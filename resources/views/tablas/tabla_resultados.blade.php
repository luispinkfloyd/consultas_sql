<div class="tabla-resultados borde-bottom">
    <div class="row">
		<div class="col form-group" align="center">
            <form method="get" action="{{ route('export_excel') }}">
                <button type="submit" class="btn btn-success mt-2 mr-2" style="margin:2px;"><img src="{{asset('img/excel.png')}}" height="20" style="float:left;padding-right:5px"><span>Exportar a  Excel</span></button>
            </form>
        </div>
		<div class="col form-group" align="center">
        	<h5 class="mt-3">Total de registros = <b>{{$count_datos}}</b></h5>
        </div>
    </div>
</div>
<div class="table-responsive tabla-resultados borde-top">
	<table class="table table-sm table-bordered table-striped table-hover">
		<thead>
			<tr>
				@foreach($datos[0] as $key => $value)
				<th scope="col">{{$key}}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach($datos as $dato)
			<tr>
				@foreach($dato as $columna)
					@if($charset_def !== 'UTF8')

						<td>{{utf8_encode($columna)}}</td>

					@else

						<td>{{$columna}}</td>

					@endif
				@endforeach
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
