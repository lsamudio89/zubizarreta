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
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo nombrePagina(basename($_SERVER['PHP_SELF']))." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	

    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
    
	<!-- Autocomplete -->
	<link rel="stylesheet" href="css/jquery-ui.css" />
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	<script src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">

	<style type="text/css">
		.ui-corner-all{
			font-size:14px;
			font-style: italic;
			z-index:99999;
		}
    </style>
	
    <script type="text/javascript">
	
		$(document).ready(function () {
			
			function iconoEditar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>'
				].join('');
			}
			
			function iconoAsignar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-xs asignar"><span class="glyphicon glyphicon-plus aria-hidden="true"></span>&nbsp;&nbsp;Asignar Menús</button>'
				].join('');
			}
			
			window.editarFila = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				}
			};
			
			window.asignar = {
				'click .asignar': function (e, value, row, index) {
					mostrarModalAsignar(row);
				}
			};
			
			
			$('#tabla').bootstrapTable({
				columns: [
					[
						{	field: 'id_rol', title: 'ID Rol', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'rol', title: 'Nombre del Rol', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'miembros', title: 'Miembros', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'menus', title: 'Menus Asignados', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'nombre_estado', align: 'center', valign: 'middle', title: 'Estado', sortable: true	}, 
						{	field: 'estado', align: 'center', valign: 'middle', title: 'Estado', sortable: true, visible: false	}, 
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarFila,  formatter: iconoEditar	},
						{	field: 'asignar', align: 'center', valign: 'middle', title: 'Menús', sortable: false, events: asignar,  formatter: iconoAsignar	}
					]
				]
			});

			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga
			function limpiarModal(){
				$('textarea').val("");
				$('input').val(""); 
				$("#msjModalAdd").html("");
				$("#msjModalEditar").html("");
				$("#msjModalAsignar").html("");
			}
			
			$('.modal-agregar').on('shown.bs.modal', function (e) {
				$("#rol_carga").focus();
			});
						
			$("#rol_carga").focusout(function() {
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
					$('.modal-agregar').modal('hide');
					$('#mensaje').html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						setTimeout(function () {
							location.reload();
						}, 1500);
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalAdd').html(alertDismissJS(jqXHR.responseText, "error"));
				});
			});
			
			///MODAL EDITAR
			function mostrarModalEditar(row){
				$('.modal-editar').modal('show');
				$("#hidden_id_rol").val(row.id_rol);
				$("#rol_editar").val(row.rol);
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
						setTimeout(function () {
						  location.reload();
						}, 1500);
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalEditar').html(alertDismissJS(jqXHR.responseText, "error"));
				});
				
			});

			$('.modal-editar, .modal-agregar, .modal-asignar').on('hide.bs.modal', function (e) {
				limpiarModal();
			});
			
			///MODAL ASIGNAR
			function mostrarModalAsignar(row){
				$('.modal-asignar').modal('show');
				$("#hidden_id_rol_menu").val(row.id_rol);
				$("#rol_asignar").val(row.rol)
				verMenusAsignados(row.id_rol);
				//$("#estado_editar").val(row.estado);
			}
			
			$('.modal-asignar').on('hide.bs.modal', function (e) {
				$('#menus_asignados').empty();
			});
			
			
			$('.modal-eliminar').on('hide.bs.modal', function (e) {
				$("#mensaje_eliminar").html("");
			});
			
			$(".modal").draggable({
				handle: ".modal-header"
			});
			
			//CARGA MENUS DISPONIBLES
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'menus'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#menus').append('<option value="'+ value.id_menu + '">' + value.menu + '</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			$('#addMenu').on('click', function() {
				var options = $('select#menus option:selected').sort().clone();
				if ($("#menus_asignados option[value='"+options[0].value+"']").length > 0){
					$("#msjModalAsignar").html(alertDismissJS("Menú ya existe", "error"));
				}else{
					$('select#menus_asignados').append(options);
					$("#msjModalAsignar").html("");
				}
			});
			
			$('#delMenu').on('click', function() {
				$('select#menus_asignados option:selected').remove();
			});
			
			
		
        });
		
		//ELIMINAR
		 function preguntaBorrado(nombre){
			$('.modal-eliminar').modal('show');
			$("#nombre_borrar").val(nombre);
			$("#titulo_eliminar").html("¿Desea eliminar el rol: <strong>"+nombre+"</strong>?");			
		}
		
				
		
		function confirmarBorrado(){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/administrar-roles-data.php',
				cache: false,
				data: {q: 'eliminar', id: $("#hidden_id_rol").val(), nombre: $("#nombre_borrar").val() },	
				beforeSend: function(){
					$("#mensaje_eliminar").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					$('.modal-eliminar, .modal-editar').modal('hide');
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						setTimeout(function () {
						  location.reload();
						}, 1500);
					}
				},
				error: function (xhr) {
					$("#mensaje_eliminar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
        }
		
		function verMenusAsignados(id_rol){
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/administrar-roles-data.php', type: 'POST', data: {q: 'ver_menus_asignados', id_rol: id_rol},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#menus_asignados').append('<option value="'+ value.id_menu + '">' + value.menus + '</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			//Guarda menus asignados
			$("#guardarMenus").click(function () {
				
				$("#msjModalAsignar").html("");
				
				var menus = [];
				$('#menus_asignados option').each(function(i, selected){
					menus[i] = $(selected).val();
				});

				$.ajax({
					dataType: 'html', async: false, cache: false, url: 'inc/administrar-roles-data.php', type: 'POST', data: {q: 'asignar_menus', id_rol: $("#hidden_id_rol_menu").val(), menus: menus },
					beforeSend: function(){
						$("#msjModalAsignar").html("<img src='images/progress_bar.gif'>");
					},
					success: function (data){
						$('#msjModalAsignar').html(data);
							var n = data.toLowerCase().indexOf("error");
							if (n == -1) {
								setTimeout(function () {
								  location.reload();
								}, 1500);
							}
					},
					error: function (xhr) {
						$("#msjModalAsignar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
				
			});
					
			
		}
		
		
		
		
	
		// function verificaUsuario(input_usu,msj){
			// var usu = $("#"+input_usu).val();
			// if (usu){
				// $("#"+msj).html("<img src='images/progress_bar.gif'>");
				// $.ajax({
					// dataType: 'html',
					// type: 'POST',
					// url: 'inc/administrar-usuarios-data.php',
					// cache: false,
					// data: {q: 'ver_usuario', usuario: usu },	
					// beforeSend: function(){
						// $("#"+msj).html("<img src='images/progress_bar.gif'>");
					// },
					// success: function (data, status, xhr) {
						// $("#"+msj).html(data);
					// },
					// error: function (xhr) {
						// $("#mensaje_eliminar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					// }
				// });
				
				// return false;
			// }
		// }
					
    </script>
  </head>
  <body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="index.php"><img src="images/logo_48px.png">&nbsp;&nbsp;</a>
        </div>
        <div class="navbar-collapse collapse">
			<?php echo menu($_SESSION['id_usuario']); ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
	<!-- Wrap all page content here -->
    <div id="wrap">
		<div class="container-fluid">
			<div class="page-header">
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>

			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div id="mensaje"></div>
					
					<div id="toolbar">
						<div class="form-inline" role="form">
							<div class="form-group">
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Agregar Rol</button>
							</div>
						</div>
					</div>
					<table id="tabla" data-url="inc/administrar-roles-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-page-list="[10, 50, 100]" data-classes="table table-hover table-condensed" data-striped="true"></table>
						
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
					<h4 class="modal-title">Agregar Rol<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-roles-data.php?q=cargar">
						<div class="container">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label>Nombre del Rol</label>
										<input class="form-control input-sm" type="text" name="rol_carga" id="rol_carga" autocomplete="off">
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<span id="msjModalAdd"></span>
									<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar</button>
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
					<h4 class="modal-title">Editar Rol<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/administrar-roles-data.php?q=editar">
						<input type="hidden" id="hidden_id_rol" name="hidden_id_rol">
						<div class="container">
							<div class="row">
								<div class="col-md-6 col-sm-6 col-xs-6">
									<div class="form-group">
										<label>Nombre del Rol</label>
										<input class="form-control input-sm" type="text" name="rol_editar" id="rol_editar" autocomplete="off">
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-6">
									<div class="form-group">
										<label>Estado</label>
										<select id="estado_editar" name="estado_editar" class="form-control input-sm" style="padding: 0" required>
										<option value='1'>Habilitado</option>
										<option value='0'>Deshabilitado</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div id="msjModalEditar"></div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 col-sm-4 col-xs-4">
									<button onclick="preguntaBorrado($('#rol_editar').val())" type="button" class="btn btn-danger btn-md submit_btn">Eliminar Rol</button>
								</div>
								<div class="col-md-4 col-sm-4 col-xs-4">
									<span>&nbsp;</span>
								</div>
								<div class="col-md-4 col-sm-4 col-xs-4">
									<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar cambios</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- MODAL ASIGNAR/ELIMINAR MENUS -->
	<div class="modal modal-asignar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog" style="width:55%">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Asignar/Eliminar Menús al Rol<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_asignar_menus" method="post" enctype="multipart/form-data" action="inc/administrar-roles-data.php?q=asignar_menus">
						<input type="hidden" id="hidden_id_rol_menu" name="hidden_id_rol_menu">
						<div class="container-fluid">
							<div class="row">
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="form-group">
											<label>Nombre del Rol</label>
											<input class="form-control input-sm" type="text" name="rol_asignar" id="rol_asignar" autocomplete="off" readonly>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-5 col-sm-5 col-xs-5">
										<div class="form-group">
											<label>Menús disponibles</label>
											<select multiple class="form-control input-sm" id="menus" name="menus" style="padding: 2px" size="14">
											</select>
										</div>
									</div>
									<div class="col-md-2 col-sm-2 col-xs-2">
										<br><br><br><br>
										<button type="button" class="btn btn-success btn-xs" id="addMenu">Añadir -></button>
										<br><br>
										<button type="button" class="btn btn-warning btn-xs" id="delMenu"><- Borrar</button>
										
									</div>
								
									<div class="col-md-5 col-sm-5 col-xs-5">
										<div class="form-group">
											<label>Menús Asignados al Rol</label>
											<select multiple class="form-control input-sm" id="menus_asignados" name="menus_asignados" style="padding: 2px" size="14"></select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div id="msjModalAsignar"></div>
										<br>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 col-sm-4 col-xs-4 col-md-offset-8 col-sm-offset-8 col-xs-offset-8">
										<button type="button" class="btn btn-primary btn-md submit_btn" style="float:right" id="guardarMenus">Guardar cambios</button>
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
	
    <script src="js/menuHover.js"></script>
  </body>
</html>