<form id="database" action="{{ route('database') }}" method="post"> @csrf </form>
<form id="schema" action="{{ route('schema') }}" method="get"> <input type="hidden" name="database" value="{{$database}}"> </form>
<div class="row form-select-row form-general" style="margin-top: 0px">
	<div class="col-sm-6 form-group" style="margin-top: 13px">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" id="database_span">Data Base<button class="btn btn-sm btn-link ml-2" style="padding: 0px" type="submit" form="database"><img src="{{ asset('img/recargar.png')}}" height="15"></button></span>
			</div>
			<select class="custom-select" disabled>
				<option selected>{{$database}}</option>
			</select>
		</div>
	</div>
	<div class="col-sm-6 form-group" style="margin-top: 13px">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" id="schemas_span">Schemas<button class="btn btn-sm btn-link ml-2" style="padding: 0px" type="submit" form="schema"><img src="{{ asset('img/recargar.png')}}" height="15"></button></span>
			</div>
			<select class="custom-select" disabled>
				<option selected>{{$schema}}</option>
			</select>
		</div>
	</div>
</div>
