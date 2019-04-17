<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

   <title><?php echo nombrePagina(basename($_SERVER['PHP_SELF']))." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-theme.min.css" rel="stylesheet">

    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
    
	<!-- Autocomplete -->
	<link rel="stylesheet" href="css/jquery-ui.css" />
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	
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
			
			window.editarRegistro = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				}
			};
			
			/*var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/administrar-sucursales-data.php', type: 'POST', data: {q: 'ver'},
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
			});*/
			
			$table.bootstrapTable({
				data: [],
				columns: [
					[
						{	field: 'id_sucursal', title: 'ID sucrusal', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true	}, 
						{	field: 'direccion', align: 'left', valign: 'middle', title: 'Dirección', sortable: true	},
						{	field: 'estado', align: 'left', valign: 'middle', title: 'Estado', sortable: true, visible: false	},
						{	field: 'nombre_estado', align: 'left', valign: 'middle', title: 'Estado', sortable: true	},
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarRegistro,  formatter: iconoEditar	}
					]
				]
			});
			
			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga
			function limpiarModal(){
				$('textarea').val("");
				$('input').val(""); 
				$("#msjModalAdd").html("");
				$("#msjModalEditar").html("");
			}
			
			$('.modal-agregar').on('shown.bs.modal', function (e) {
				$("#sucursal_carga").focus();
			});
						
			$("#sucursal_carga").focusout(function() {
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
				$("#hidden_id_sucursal").val(row.id_sucursal);
				$("#sucursal_editar").val(row.sucursal);
				$("#direccion_editar").val(row.direccion);
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

        });
		
		//ELIMINAR
		 function preguntaBorrado(nombre){
			$('.modal-eliminar').modal('show');
			$("#nombre_borrar").val(nombre);
			$("#titulo_eliminar").html("¿Desea eliminar el sucursal: <strong>"+nombre+"</strong>?");			
		}
		
		function confirmarBorrado(){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/administrar-sucursales-data.php',
				cache: false,
				data: {q: 'eliminar', id: $("#hidden_id_sucursal").val(), nombre: $("#nombre_borrar").val() },	
				beforeSend: function(){
					$("#mensaje_eliminar").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$('.modal-eliminar, .modal-editar').modal('hide');
					var n = data.indexOf("Cannot delete or update a parent row");
					if (n == -1) {
						$("#mensaje").html(data);
						setTimeout(function () {
							location.reload();
						}, 1500);
					}else{
						$("#mensaje").html(alertDismissJS("Error al eliminar. El sucursal contiene mercaderías.", "error"));
					}
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
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Agregar sucursal</button>
							</div>
						</div>
					</div>
					
					
					<table id="tabla" data-url="inc/administrar-sucursales-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="false" data-page-list="[10, 50, 100]" data-classes="table table-hover table-condensed"></table>
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
					<h4 class="modal-title">Agregar sucursal<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-sucursales-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="sucursal_carga">Nombre del sucursal</label>
												<input type="text" class="form-control" name="sucursal_carga" id="sucursal_carga" required placeholder="" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<label for="direccion_carga">Dirección</label>
												<input class="form-control" type="text" name="direccion_carga" id="direccion_carga" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6  col-xs-6">
											<div class="form-group">
												<label for="estado_carga">Estado</label>
												<select id="estado_carga" name="estado_carga" class="form-control" style="padding: 0" disabled>
												<option value="1">Habilitado</option>
												<option value="0">Deshabilitado</option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<span id="msjModalAdd"></span>
											<br>
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
	<!-- MODAL EDITAR -->
	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Editar sucursal<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
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
								<br>
								<input type="hidden" id="nombre_borrar">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
								<button type="button" class="btn btn-danger btn-sm" onclick="confirmarBorrado()">Eliminar</button>
							</div>
						</div>
					  </div>
					</div>
					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/administrar-sucursales-data.php?q=editar">
						<input type="hidden" id="hidden_id_sucursal" name="hidden_id_sucursal">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="sucursal_editar">Nombre del sucursal</label>
												<input type="text" class="form-control" name="sucursal_editar" id="sucursal_editar" required placeholder="" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<label for="direccion_editar">Dirección</label>
												<input class="form-control" type="text" name="direccion_editar" id="direccion_editar" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6  col-xs-6">
											<div class="form-group">
												<label for="estado_editar">Estado</label>
												<select id="estado_editar" name="estado_editar" class="form-control" style="padding: 0" value="1">
												<option value='1'>Habilitado</option>
												<option value='0'>Deshabilitado</option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div id="msjModalEditar"></div>
											<br>
											<button onclick="preguntaBorrado($('#sucursal_editar').val())" type="button" class="btn btn-danger btn-md submit_btn" style="float:left">Eliminar sucursal</button>
											<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar cambios</button>
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
	
    <script src="js/menuHover.js"></script>
  </body>
</html>