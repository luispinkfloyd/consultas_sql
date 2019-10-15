<form class="form-general" action="{{ route('consulta') }}" method="get">
    <input type="hidden" name="database" value="{{$database}}">
    <div class="row form-select-row">
        <div class="col-sm-6 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="database_span">Data Base</span>
                </div>
                <select class="custom-select" disabled>
                    <option selected>{{$database}}</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="schemas_span">Schemas</span>
                </div>
                <select class="custom-select" name="schema" onChange="this.form.submit();" required>
                    <option disabled selected value>--Seleccione--</option>
                    @foreach($schemas as $schema)
                        <option>{{$schema->schema_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</form>