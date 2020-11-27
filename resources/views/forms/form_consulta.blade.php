<div class="container consulta-style">
	<form action="{{ route('consulta') }}" method="get" name="form1">
		<input type="hidden" name="database" value="{{$database}}">
		<input type="hidden" name="schema" value="{{$schema}}">
		<div class="container">
            <div class="row">
                <label>Pegar consulta:</label>
            </div>
            <div class="row form-group borde redondeado">
                <textarea id="textarea" name="consulta">@if(isset($consulta)){{$consulta}}@endif</textarea>
            </div>
			<div class="row form-group">
				<div class="col-sm" align="center">
					<input type="submit" value="Consultar" class="btn btn-outline-success mr-1">
                </div>
                <div class="col-sm" align="center">
                    <button type="button" class="btn btn-outline-info ml-1" onclick="exportar_sql()" id="guardar_sql">Guardar consulta</button>
                </div>
                <div class="col-sm" align="center">
					<a href="{{ route('consulta') }}" class="btn btn-outline-danger mr-1" id="limpiar_consulta">Limpiar consulta</a>
                </div>
			</div>
		</div>
	</form>
</div>
@section('script')
<script type="text/javascript">

var mime = 'text/x-pgsql';

window.onload = function(){

    var editor = CodeMirror.fromTextArea(document.getElementById('textarea'), {
        mode: mime,
        lineNumbers: true,
        styleActiveLine: true
    });

    if(editor.getValue() == ''){
        document.getElementById('guardar_sql').disabled = true;
        document.getElementById('limpiar_consulta').classList.add('disabled');
    }else{
        document.getElementById('guardar_sql').disabled = false;
        document.getElementById('limpiar_consulta').classList.remove('disabled');
    }

    editor.on('change', editor => {
        var value_textarea = editor.getValue();
        //var value_textarea = value_textarea.replace(/\n/g, "\r\n");
        document.getElementById('textarea').value = value_textarea;
        if(editor.getValue() == ''){
            document.getElementById('guardar_sql').disabled = true;
            document.getElementById('limpiar_consulta').classList.add('disabled');
        }else{
            document.getElementById('guardar_sql').disabled = false;
            document.getElementById('limpiar_consulta').classList.remove('disabled');
        }
    });

}

function exportar_sql(){

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

</script>


@endsection
