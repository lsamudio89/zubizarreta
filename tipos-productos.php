<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
	
?>
<!DOCTYPE html>
<html lang="en">
  <head lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png">

   <title><?php echo nombrePagina(basename($_SERVER['PHP_SELF']))." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	

    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
    
	<!-- Autocomplete -->
	<link rel="stylesheet" href="css/jquery-ui.css" />
	<script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	<script src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- Bootstrap Multiselect -->
	<script type="text/javascript" src="js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>
	
	<!--<script src="js/fSelect.js"></script>
	<link href="css/fSelect.css" rel="stylesheet">-->
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	<style type="text/css">
		.modal-eliminar{z-index:999999}
    </style>
    <script type="text/javascript">
	
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			function iconoEditar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>'
					/*'<a class="editar" href="javascript:void(0)" title="Editar">',
					'<i class="glyphicon glyphicon-edit"></i>',
					'</a>'*/
				].join('');
			}
			
			window.editarFila = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				}
			};
			
			var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/tipos-productos-data.php', type: 'POST', data: {q: 'ver'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					if (json) datos = json;
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			$table.bootstrapTable({
				data: datos,
				columns: [
					[
						{	field: 'id_tipo', visible: false }, 
						{	field: 'tipo', align: 'left', valign: 'middle', title: 'Tipo de producto', sortable: true	}, 
						{	field: 'estado', align: 'left', valign: 'middle', title: 'Estado', sortable: true	},
						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario Carga', sortable: true	},
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarFila,  formatter: iconoEditar	}
					]
				]
			});

			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga
			function limpiarModal(){
				$('input').val(""); 
				$("#msjModalAdd").html("");
				$("#msjModalEditar").html("");
			}
			
			$('.modal-agregar').on('shown.bs.modal', function (e) {
				$("#tipo_carga").focus();
			});
						
			$("#tipo_carga").focusout(function() {
				$("#msjModalAdd").html("");	
			});
						
			$("#form_agregar").submit(function(event){
				$("#msjModalAdd").html("");
				
				//Evita el submit default del php
				event.preventDefault();
			
				var formData = $("#form_agregar").serializeArray();
				var URL = $("#form_agregar").attr("action");
					$("#msjModalAdd").html("<img src='images/progress_bar.gif'>");
				$.post(URL, formData, function() {
				})
				.done(function(data) {
					$('#msjModalAdd').html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						location.reload();
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalAdd').html(alertDismissJS(jqXHR.responseText, "error"));
				});
			});
			
			///MODAL EDITAR
			function mostrarModalEditar(row){
				$('.modal-editar').modal('show');

				$("#hidden_id_tipo").val(row.id_tipo);
				$("#tipo_editar").val(row.tipo);
				$("#estado_editar").val(row.estado);
				
			}
			
			//Guarda cambios editados
			$("#form_editar").submit(function(event) {
				
				$("#msjModalEditar").html("");
				
				//Evita el submit default del php
				event.preventDefault();
				var formData = $("#form_editar").serializeArray();
				var URL = $("#form_editar").attr("action");
					$("#msjModalEditar").html("<img src='images/progress_bar.gif'>");
				$.post(URL, formData, function() {
				})
				.done(function(data) {
					$('#msjModalEditar').html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						location.reload();
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalEditar').html(alertDismissJS(jqXHR.responseText, "error"));
				});
				
			});

			$('.modal-editar, .modal-agregar').on('hide.bs.modal', function (e) {
				limpiarModal();
			});
			
			$('.modal-eliminar').on('hide.bs.modal', function (e) {
				$("#mensaje_eliminar").html("");
			});

			$(".modal").draggable({
				handle: ".modal-header"
			});

        });

		//ELIMINAR
		 function preguntaBorrado(nombre){
			$('.modal-eliminar').modal('show');
			$("#nombre_borrar").val(nombre);
			$("#titulo_eliminar").html("¿Desea eliminar: <strong>"+nombre+"</strong>?");			
		}
		
		function confirmarBorrado(){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/tipos-productos-data.php',
				cache: false,
				data: {q: 'eliminar', id: $("#hidden_id_tipo").val(), nombre: $("#nombre_borrar").val() },	
				beforeSend: function(){
					$("#mensaje_eliminar").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					$('.modal-eliminar, .modal-editar').modal('hide');
					setTimeout(function () {
						  location.reload();
					}, 1500);
				},
				error: function (xhr) {
					$("#mensaje_eliminar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
        }
			
    </script>
  </head>
  <body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
		 <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php"><img src="images/logo_48px.png">&nbsp;&nbsp;</a>
        </div>
        <div class="navbar-collapse collapse">
			<?php echo menu($_SESSION['id_usuario']); ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
	<!-- Wrap all page content here -->
    <div id="wrap">
		<div class="container">
			<div class="page-header">
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			
			
			<div class="row">
				<div class="col-md-12">
					<div id="mensaje"></div>
					<div id="toolbar">
					
						<div class="form-inline" role="form">
							<div class="form-group">
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Agregar Tipo de Producto</button>
							</div>
						</div>
					</div>
					
					
					<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="false" data-page-list="[10, 50, 100]" data-classes="table table-hover table-condensed"></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	<!-- MODA AGREGAR -->
	<div class="modal modal-agregar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Agregar Tipo de Producto<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/tipos-productos-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label for="tipo_carga">Tipo de producto</label>
										<input type="text" class="form-control input-sm" name="tipo_carga" id="tipo_carga" required placeholder="" autocomplete="off">
									</div>
										
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<span id="msjModalAdd"></span>
											<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<!-- MODAL ELIMINAR -->
	<div class="modal modal-eliminar" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
				<h4 id="mySmallModalLabel" class="modal-title">Eliminar<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
			</div>
			<div class="modal-body" id="titulo_eliminar">
				&nbsp;
			</div>
			<div class="modal-footer">
				<div id="mensaje_eliminar" style="float:left"></div>
				<input type="hidden" id="nombre_borrar">
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger btn-sm" onclick="confirmarBorrado()">Eliminar</button>
			</div>
		</div>
	  </div>
	</div>
	
	<!-- MODAL EDITAR -->
	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Editar Producto<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/tipos-productos-data.php?q=editar">
						<input type="hidden" id="hidden_id_tipo" name="hidden_id_tipo">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label for="tipo_editar">Tipo de producto</label>
										<input type="text" class="form-control input-sm" name="tipo_editar" id="tipo_editar" required placeholder="" autocomplete="off">
									</div>
								</div>
								<div class="col-md-12 col-sm-12  col-xs-12">
									<div class="form-group">
										<label for="estado_editar">Estado</label>
										<select id="estado_editar" name="estado_editar" class="form-control input-sm" style="padding: 0">
										<option value='1'>Activo</option>
										<option value='0'>Inactivo</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div id="msjModalEditar"></div>
									<button onclick="preguntaBorrado($('#tipo_editar').val())" type="button" class="btn btn-danger btn-md submit_btn" style="float:left">Eliminar</button>
									<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar cambios</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>