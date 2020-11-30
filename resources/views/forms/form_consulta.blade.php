<div class="container consulta-style">
	<form action="{{ route('consulta') }}" method="get" name="form1">
		<input type="hidden" name="database" value="{{$database}}">
		<input type="hidden" name="schema" value="{{$schema}}">
		<div class="container">
            <div class="row">
                <div class="col-3">
                    <label>Pegar consulta:</label>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-4">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Sector</label>
                                </div>
                                <select class="custom-select" id="consulta_select" name="consulta_select" onchange="ajax_get_consulta()">
                                    <option value disabled selected>--Seleccionar--</option>
                                    @foreach($sectores as $sector)
                                        <option @if(isset($consulta_select_selected)) @if($consulta_select_selected == $sector->sector) {{'selected'}} @endif @endif>{{$sector->sector}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Nombre de la Consulta</label>
                                </div>
                                <input class="form-control" type="text" name="consulta_input" id="consulta_input" @if(isset($consulta_input_selected)) value="{{$consulta_input_selected}}" @endif list="consulta_input_datalist" autocomplete="off" disabled>
                                <datalist id="consulta_input_datalist"></datalist>
                            </div>
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-primary" onclick="ajax_set_consulta()">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group borde redondeado">
                <textarea id="textarea" name="consulta">@if(isset($consulta)){{$consulta}}@endif</textarea>
            </div>
			<div class="row form-group">
				<div class="col-sm" align="center">
					<input type="submit" value="Consultar" class="btn btn-outline-success mr-1">
                </div>
                <div class="col-sm" align="center">
                    <button type="button" class="btn btn-outline-info ml-1" onclick="function_exportar_sql()" id="exportar_sql">Exportar consulta</button>
                </div>
                <div class="col-sm" align="center">
                    <button type="button" class="btn btn-outline-secondary ml-1" data-toggle="modal" data-target="#guardarsqlModal" id="guardar_sql">Guardar consulta</button>
                </div>
                <div class="col-sm" align="center">
					<a href="{{ route('consulta') }}" class="btn btn-outline-danger mr-1" id="limpiar_consulta">Limpiar consulta</a>
                </div>
			</div>
		</div>
	</form>
</div>

<!-- Modal -->
<div class="modal fade" id="guardarsqlModal" tabindex="-1" role="dialog" aria-labelledby="guardarsqlModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="{{route('consulta')}}" method="get">
        <div class="modal-header">
          <h5 class="modal-title" id="guardarsqlModalLabel">Guardar Consulta</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text">Sector</label>
                </div>
                <select class="custom-select" name="sector" required>
                    <option value disabled selected>--Seleccionar--</option>
                    <option>Grado</option>
                    <option>Posgrado</option>
                    <option>Wichí</option>
                    <option>Sanavirón Quilmes</option>
                    <option>Servidor 55</option>
                    <option>Local</option>
                </select>
            </div>
            <input type="hidden" name="consulta_hidden" id="consulta_hidden" @if(isset($consulta)) value="{{$consulta}}" @endif>
            <input type="hidden" name="database" value="{{$database}}">
            <input type="hidden" name="schema" value="{{$schema}}">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text">Nombre de la consulta</label>
                </div>
                <input type="text" name="nombre" class="form_control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
        </form>
      </div>
    </div>
</div>


@section('script')
<script type="text/javascript">

var mime = 'text/x-pgsql';
var editor = {};

window.onload = function(){

    editor = CodeMirror.fromTextArea(document.getElementById('textarea'), {
        mode: mime,
        lineNumbers: true,
        styleActiveLine: true
    });

    if(editor.getValue() == ''){
        document.getElementById('exportar_sql').disabled = true;
        document.getElementById('guardar_sql').disabled = true;
        document.getElementById('limpiar_consulta').classList.add('disabled');
    }else{
        document.getElementById('exportar_sql').disabled = false;
        document.getElementById('guardar_sql').disabled = false;
        document.getElementById('limpiar_consulta').classList.remove('disabled');
    }

    editor.on('change', editor => {
        var value_textarea = editor.getValue();
        document.getElementById('textarea').value = value_textarea;
        document.getElementById('consulta_hidden').value = value_textarea;
        if(editor.getValue() == ''){
            document.getElementById('exportar_sql').disabled = true;
            document.getElementById('guardar_sql').disabled = true;
            document.getElementById('limpiar_consulta').classList.add('disabled');
        }else{
            document.getElementById('exportar_sql').disabled = false;
            document.getElementById('guardar_sql').disabled = false;
            document.getElementById('limpiar_consulta').classList.remove('disabled');
        }
    });

}

function function_exportar_sql(){

    /*var base = '{!! route('export_sql') !!}';
    var url = base + '?consulta_copia=' + document.getElementById("textarea").value ;
    window.location.href = url;*/ //Quitado por solución javascript

    // your CodeMirror textarea ID
    var textToWrite = document.getElementById("textarea").value;

    // preserving line breaks
    var textToWrite = textToWrite.replace(/\n/g, "\r\n");

    var textFileAsBlob = new Blob([textToWrite], {type:'application/x-sql'});

    // filename to save as

    var d = new Date();

    var fileNameToSaveAs = "script_"+d.getTime()+".sql";

    var downloadLink = document.createElement("a");
    downloadLink.download = fileNameToSaveAs;

    // hidden link title name
    downloadLink.innerHTML = "LINKTITLE";

    window.URL = window.URL || window.webkitURL;

    downloadLink.href = window.URL.createObjectURL(textFileAsBlob);

    downloadLink.onclick = destroyClickedElement;
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();

}

function destroyClickedElement(event) {
    document.body.removeChild(event.target);
}

function ajax_get_consulta(){

    $('#consulta_input_datalist').empty();
    var consulta_select_value;
    //hacemos focus al campo de búsqueda
    $("#consulta_select").focus();
    //obtenemos el texto introducido en el campo de búsqueda
    consulta_select_value = $("#consulta_select").val();
    //alert(consulta)
    //hace la búsqueda
    $.ajax({
        type: "GET",
        url: "{{ route('ajax_get_consulta')}}",
        data: "consulta_select_value="+consulta_select_value,
        dataType: "json",
        error: function(){
                alert("Error petición ajax");
        },
        success: function(result){
            $.each( result, function(k,v) {
                    $('#consulta_input_datalist').append($('<option>', {text:v}));
            });
            document.getElementById('consulta_input').disabled = false;
        }
    });

}

function ajax_set_consulta(){

    //$('#textarea').empty();
    var consulta_input_value;
    //hacemos focus al campo de búsqueda
    $("#consulta_input").focus();
    //obtenemos el texto introducido en el campo de búsqueda
    consulta_input_value = $("#consulta_input").val();
    //alert(consulta_input_value);
    //hace la búsqueda
    $.ajax({
        type: "GET",
        url: "{{ route('ajax_set_consulta')}}",
        data: "consulta_input_value="+consulta_input_value,
        dataType: "text",
        error: function(){
                alert("Error petición ajax");
        },
        success: function(result){
            editor.setValue(result);
        }
    });

}

</script>


@endsection
