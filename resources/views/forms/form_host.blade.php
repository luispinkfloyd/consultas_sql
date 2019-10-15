<form class="form-general" action="{{ route('database') }}" method="post">
@csrf
    <div class="row form-host-row">
        <div class="col-sm form-group">
            <label for="db_host">Host</label>
            <input type="text" class="form-control" value="{{old('db_host')}}" id="db_host" name="db_host" placeholder="Ingresar host..." required>
        </div>
        <div class="col-sm form-group">
            <label for="db_usuario">Usuario</label>
            <input type="text" class="form-control" value="{{old('db_usuario')}}" id="db_usuario" name="db_usuario" placeholder="Ingresar usuario..." required>
        </div>
        <div class="col-sm form-group">
            <label for="db_contrasenia">Contraseña</label>
            <input type="password" class="form-control" value="{{old('db_contrasenia')}}" id="db_contrasenia" name="db_contrasenia" placeholder="Contraseña" required>
        </div>
        <div class="col-sm-2 form-group form-host-col-submit">
            <button type="submit" class="btn btn-primary">Seleccionar</button>
        </div>
    </div>
</form>