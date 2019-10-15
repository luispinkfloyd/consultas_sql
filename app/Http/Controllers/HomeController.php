<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
//use App\Exports\ExcelExport;
//use Maatwebsite\Excel\Facades\Excel;
use Session;
use ReflectionClass;
use Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;

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
    public function index()
    {
		
		//Primero retorna la vista con el formulario para conectarse al host
		
		Cache::flush();
		
		return view('home');
		
    }
	
	public function paginacion($array, $request)
	{
		$page = Input::get('page', 1);
		
		$perPage = 5;
		
		$offset = ($page * $perPage) - $perPage;

		return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,['path' => $request->url(), 'query' => $request->query()]);
		
	}
	
	public function get_databases(Request $request){
		
		
		//Intenta hacer la conexión (en caso de fallar, retorna al home y muestra el mensaje de error)
		try
		{
			
			
			//Guardo el host, usuario y contraseña definidos en el form_host para hacer la conexión, en variables de sesión; mientras dure la sesión y no se modifiquen, la conexión siempre se va a realizar con estos valores
			$request->session()->put('db_host',$request->db_host);
			
			$request->session()->put('db_usuario',$request->db_usuario);
			
			$request->session()->put('db_contrasenia',$request->db_contrasenia);
			
			//Traigo los valores de la conexión para manejarlos como variantes directamente (menos la contraseña)
			$db_usuario = $request->session()->get('db_usuario');
		
			$db_host = $request->session()->get('db_host');
			
			//Genero el modelo de la conexión pgsql_variable con los valores definidos, y realizo la conexión
			Config::set('database.connections.pgsql_variable', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => 'postgres',
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia'),
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => 'public',
			));
			
			$conexion = DB::connection('pgsql_variable');
			
			//Hago la consulta para traer las bases de datos que haya en el host
			$sql="select pg_database.datname
						  from pg_database
						 where pg_database.datname not in ('template0','template1')
					  order by pg_database.datname;";
			
			$bases = $conexion->select($sql);
			
			//Retorno al home con los datos de la consulta
			return view('home',['bases' => $bases,'db_usuario' => $db_usuario,'db_host' => $db_host]);
			
		}
		catch (\Exception $e) {
			
			//En caso de error retorno al home con el mensaje del error
			$mensaje_error = $e->getMessage();
			
			return redirect('home')->withInput()->with('mensaje_error',$mensaje_error);
			
		}
		
	}
	
	public function get_schemas(Request $request)
    {
        
		//Verifico que los input session hechos en el método anterior sigan seteados
		if($request->session()->get('db_usuario') !== NULL && $request->session()->get('db_host') !== NULL){
		
			//Traigo los inputs session y la base de datos seleccionada en el form_database (todos los datos para armar la conexión, a partir de acá, se manejan por get)
			$database = $request->database;
			
			$db_usuario = $request->session()->get('db_usuario');
			
			$db_host = $request->session()->get('db_host');
			
			Config::set('database.connections.pgsql_variable', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => $database,
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia'),
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => 'public',
			));
			
			//Realizo la conexión
			$conexion = DB::connection('pgsql_variable');
			
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
			
			$request->session()->put('charset_def',$charset);
			
			if(count($schemas) == 1 && $schemas[0]->schema_name == 'public'){
				
				$request->session()->put('schema','public');
				
				$request->session()->put('database',$database);
				
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
		if($request->session()->get('db_usuario') !== NULL && $request->session()->get('db_host') !== NULL){
				
			//Traigo los inputs session y la base de datos seleccionada más el schema seleccionado en el form_schema
			$database = $request->database;
			
			if(isset($request->database) && isset($request->schema)){
				
				$database = $request->database;
				
				$schema = $request->schema;
				
			}else{
				
				$schema = $request->session()->get('schema');
				
				$database = $request->session()->get('database');
				
			}
			
			//echo $schema; exit;
			
			$db_usuario = $request->session()->get('db_usuario');
			
			$db_host = $request->session()->get('db_host');
			
			$charset_def = $request->session()->get('charset_def');
			
			Config::set('database.connections.pgsql_variable', array(
				'driver'    => 'pgsql',
				'host'      => $db_host,
				'database'  => $database,
				'username'  => $db_usuario,
				'password'  => $request->session()->get('db_contrasenia'),
				'charset'   => $charset_def,
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'schema'    => $schema,
			));
			
			//Realizo la conexión	
			$conexion = DB::connection('pgsql_variable');
			
			//Retorno al home con los datos de las consultas
			return view('home',['database' => $database,'schema' => $schema,'db_usuario' => $db_usuario,'db_host' => $db_host]);
			
		}else{
			
			//En caso que los input session no sigan seteados, redirecciono al home inicial
			return redirect('home');
			
		}
		
    }
	
}
