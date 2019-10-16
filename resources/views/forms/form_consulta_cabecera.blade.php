<div class="row form-select-row form-general" style="margin-top: 0px">
	<div class="col-sm-6 form-group" style="margin-top: 13px">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" id="database_span">Data Base</span>
			</div>
			<select class="custom-select" disabled>
				<option selected>{{$database}}</option>
			</select>
		</div>
	</div>
	<div class="col-sm-6 form-group" style="margin-top: 13px">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" id="schemas_span">Schemas</span>
			</div>
			<select class="custom-select" disabled>
				<option selected>{{$schema}}</option>
			</select>
		</div>
	</div>
</div>