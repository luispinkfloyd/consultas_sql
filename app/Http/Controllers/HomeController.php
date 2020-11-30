<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
use Rap2hpoutre\FastExcel\FastExcel;
use Session;
use ReflectionClass;
use Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Consulta;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

		//Primero retorna la vista con el formulario para conectarse al host

        Cache::flush();

        $request->session()->forget('db_usuario_sql');
        $request->session()->forget('db_host_sql');
        $request->session()->forget('db_contrasenia_sql');

		return view('home');

    }

	public function paginacion($array, $request)
	{
		$page = \Request::input('page', 1);

		$perPage = 20;

		$offset = ($page * $perPage) - $perPage;

		return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,['path' => $request->url(), 'query' => $request->query()]);

	}

	public function get_databases(Request $request){


		//Intenta hacer la conexión (en caso de fallar, retorna al home y muestra el mensaje de error)
		try
		{

            //echo $request->db_host.'<br>'.$request->db_usuario.'<br>'.$request->db_contrasenia; exit;

            if($request->session()->get('db_usuario_sql') === NULL && $request->session()->get('db_host_sql') === NULL){

				$request->session()->put('db_host_sql',$request->db_host);

				$request->session()->put('db_usuario_sql',$request->db_usuario);

				$request->session()->put('db_contrasenia_sql',$request->db_contrasenia);

			}

			//Traigo los valores de la conexión para manejarlos como variantes directamente (menos la contraseña)
			$db_usuario = $request->session()->get('db_usuario_sql');

            $db_host = $request->session()->get('db_host_sql');

            //echo $db_host; echo $db_usuario; echo $request->session()->get('db_contrasenia_sql'); exit;

			//Genero el modelo de la conexión pgsql_variable con los valores definidos, y realizo la conexión
			Config::set('database.connections.pgsql_variable_sql', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => 'postgres',
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia_sql'),
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => 'public',
			));

			$conexion = DB::connection('pgsql_variable_sql');

			//Hago la consulta para traer las bases de datos que haya en el host
			$sql="select pg_database.datname
						  from pg_database
						 where pg_database.datname not in ('template0','template1')
					  order by pg_database.datname;";

			$bases = $conexion->select($sql);

			//Retorno al home con los datos de la consulta
			return view('home',['bases' => $bases,'db_usuario' => $db_usuario,'db_host' => $db_host]);

		}catch (\Exception $e) {

			//En caso de error retorno al home con el mensaje del error
			$mensaje_error = $e->getMessage();

			return redirect('home')->withInput()->with('mensaje_error',$mensaje_error);

		}

	}

	public function get_schemas(Request $request)
    {

		//Verifico que los input session hechos en el método anterior sigan seteados
		if($request->session()->get('db_usuario_sql') !== NULL && $request->session()->get('db_host_sql') !== NULL){

			//Traigo los inputs session y la base de datos seleccionada en el form_database (todos los datos para armar la conexión, a partir de acá, se manejan por get)
			$database = $request->database;

			$db_usuario = $request->session()->get('db_usuario_sql');

			$db_host = $request->session()->get('db_host_sql');

			Config::set('database.connections.pgsql_variable_sql', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => $database,
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia_sql'),
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => 'public',
			));

			//Realizo la conexión
			$conexion = DB::connection('pgsql_variable_sql');

			//Consulto los schemas disponibles de la base de datos seleccionada
			$sql="select schema_name
						from information_schema.schemata
					   where not schema_name ilike 'pg%'
						 and schema_name <> 'information_schema'
						 and catalog_name = '".$database."'
					order by schema_name;";

			$schemas = $conexion->select($sql);

			//Consulto la codificación de la base y la almaceno en un input session para usarla en futuras consultas (hasta acá, siempre se usa la codificación UTF8)
			$sql_charset = 'SHOW SERVER_ENCODING';

			$charset_registro = $conexion->select($sql_charset);

			$charset = $charset_registro[0]->server_encoding;

			$request->session()->forget('charset_def_sql');

			$request->session()->put('charset_def_sql',$charset);

			$request->session()->forget(['schema_sql', 'database_sql']);

			//Si sólo existe un schema, me salteo la vista para seleccionar el schema y voy directo a la consulta con los valores del schema mismo y la base de datos.
			if(count($schemas) == 1){

				$request->session()->put('schema_sql',$schemas[0]->schema_name);

				$request->session()->put('database_sql',$database);

				return redirect()->route('consulta');

			}

			//Retorno al home con los datos de las consultas
			return view('home',['database' => $database,'schemas' => $schemas,'db_usuario' => $db_usuario,'db_host' => $db_host]);

		}else{

			//En caso que los input session no sigan seteados, redirecciono al home inicial
			return redirect('home');

		}

    }

	public function consulta(Request $request)
    {

		//Verifico que los input session hechos en el primer método sigan seteados
		if($request->session()->get('db_usuario_sql') !== NULL && $request->session()->get('db_host_sql') !== NULL){

			//Traigo los inputs session y la base de datos seleccionada más el schema seleccionado en el form_schema
            //$database = $request->database;

            if(isset($request->database)){

                $database = $request->database;
                $request->session()->forget('database_sql');
                $request->session()->put('database_sql',$database);

			}else{

                $database = $request->session()->get('database_sql');


            }

            if(isset($request->schema)){

                $schema = $request->schema;
                $request->session()->forget('schema_sql');
                $request->session()->put('schema_sql',$schema);


			}else{

                $schema = $request->session()->get('schema_sql');

            }

			$db_usuario = $request->session()->get('db_usuario_sql');

			$db_host = $request->session()->get('db_host_sql');

			$charset_def = $request->session()->get('charset_def_sql');

			Config::set('database.connections.pgsql_variable_sql', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => $database,
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia_sql'),
				'charset'   => $charset_def,
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => $schema,
			));

			//Realizo la conexión
			$conexion = DB::connection('pgsql_variable_sql');

			$datos = NULL;

			$count_datos = 0;

			$consulta = NULL;

            $mensaje_error = NULL;

            $consulta_input_selected = NULL;

            $consulta_select_selected = NULL;

            if(isset($request->consulta_input)){

                $consulta_input_selected = $request->consulta_input;

            }

            if(isset($request->consulta_select)){

                $consulta_select_selected = $request->consulta_select;

            }

			if(isset($request->consulta)){

				ini_set('memory_limit', -1);

                $consulta = $request->consulta;

                //echo $consulta; exit;

				if(Cache::get('consulta') == $request->consulta){

					$datos = Cache::get('datos_sql');

					$count_datos = Cache::get('count_datos');

					//$datos = $this->paginacion($datos,$request);


				}else{

					try
					{

						Cache::put('consulta',$consulta,3600);

						Cache::forget('datos_sql');

						Cache::forget('count_datos');

                        $datos = $conexion->select($consulta);

                        //print_r($datos); exit;

						$count_datos = count($datos);

						Cache::put('datos_sql',$datos,3600);

						Cache::put('count_datos',$count_datos,3600);

						//$datos = $this->paginacion($datos,$request);

					}
					catch (\Exception $e)
					{

						//En caso de error retorno al home con el mensaje del error
						$mensaje_error = $e->getMessage();


					}

				}

            }

            $mensaje_success = NULL;

            if(isset($request->consulta_hidden)){

				ini_set('memory_limit', -1);

                $consulta = $request->consulta_hidden;

                //echo $consulta; exit;

				try
				{

                    $consulta_save = new Consulta();

                    $consulta_save->sector = $request->sector;
                    $consulta_save->nombre = $request->nombre;
                    $consulta_save->consulta = $consulta;

                    $consulta_save->save();

                    $mensaje_success = 'Consulta guardada correctamente.';


				}
				catch (\Exception $e)
				{

					//En caso de error retorno al home con el mensaje del error
					$mensaje_error = $e->getMessage();


				}

			}

            $sectores = Consulta::distinct()->get(['sector']);

			//Retorno al home con los datos de las consultas
			return view('home',['database' => $database,
								'schema' => $schema,
								'db_usuario' => $db_usuario,
								'db_host' => $db_host,
							    'datos' => $datos,
								'count_datos' => $count_datos,
								'consulta' => $consulta,
                                'mensaje_error' => $mensaje_error,
                                'mensaje_success' => $mensaje_success,
                                'charset_def' => $charset_def,
                                'sectores' => $sectores,
                                'consulta_input_selected' => $consulta_input_selected,
                                'consulta_select_selected' => $consulta_select_selected,
							   ]);

		}else{

			//En caso que los input session no sigan seteados, redirecciono al home inicial
			return redirect('home');

		}

    }

    public function export_sql(Request $request){

        //return $request->consulta;

        /*
        $date = date('dmYGis');
        $file = 'script_'.$date.'.sql';
        $handle = fopen($file, "w");
        $text1 = $request->consulta_copia;
        fwrite($handle, $text1);
        fclose($handle);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($file));
        readfile($file);
        unlink($file);
        ignore_user_abort(true);
        if (connection_aborted()) {
            unlink($file);
        }
        */
        //Reemplazado por función en javascript
    }

	public function export_excel(Request $request){

		ini_set('memory_limit', -1);

		$date = date('dmYGis');

		$datos = Cache::get('datos_sql');

		$charset_def = $request->session()->get('charset_def_sql');

		if($charset_def !== 'UTF8'){


			foreach($datos as $dato){

				foreach($dato as $key => $value){

					$array_dato_encode[$key] = utf8_encode($value);

				}

				$array_datos[] = $array_dato_encode;

			}


		}else{

			$array_datos = $datos;

		}



		//print_r($array_datos); exit;

		$list = collect($array_datos);

		return (new FastExcel($list))->download('resultados_consulta_'.$date.'.xlsx');

    }

    public function ajax_get_consulta(REQUEST $request){

        $consulta_array = array();

        $sector = $request->consulta_select_value;

        $consultas_nombres = Consulta::where('sector', $sector)->get();

        foreach($consultas_nombres as $consulta_nombre) {
            $consulta_array[$consulta_nombre->id] = (array) $consulta_nombre->nombre;
        }

		return response()->json($consulta_array);


    }

    public function ajax_set_consulta(REQUEST $request){

        $consulta_text = '';

        $nombre = $request->consulta_input_value;

        $consultas_text = Consulta::where('nombre', $nombre)->get();

        foreach($consultas_text as $consulta_text_item) {
            $consulta_text = $consulta_text_item->consulta;
        }

		return $consulta_text;


    }

}
