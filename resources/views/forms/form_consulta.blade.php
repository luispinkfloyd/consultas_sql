<div class="container consulta-style">
	<form action="{{ route('consulta') }}" method="get">
		<input type="hidden" name="database" value="{{$database}}">
		<input type="hidden" name="schema" value="{{$schema}}">
		<div class="container">
			<div class="row form-group">
				<label for="textarea">Pege la consulta:</label>
				<textarea class="form-control" id="textarea" rows="10" name="consulta" required>@if(isset($consulta)){{$consulta}}@endif
				</textarea>
			</div>
			<div class="row form-group">
				<div class="col-md-12" align="center">
					<input type="submit" value="Consultar" class="btn btn-outline-success">
				</div>
			</div>
		</div>
	</form>
</div>