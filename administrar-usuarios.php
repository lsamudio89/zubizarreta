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
	<script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">

    <script type="text/javascript">
	
		$(document).ready(function () {
			
			function iconoEditar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>'
				].join('');
			}
			
			window.editarFila = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				}
			};
			
			
			$('#tabla').bootstrapTable({
				columns: [
					[
						{	field: 'rol', title: 'Rol', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'id_rol', title: 'Rol', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'id_usuario', title: 'ID Usuario', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'nombre_usuario', align: 'left', valign: 'middle', title: 'Nombre de Usuario', sortable: true	}, 
						{	field: 'nombre', align: 'left', valign: 'middle', title: 'Nombre', sortable: true	},
						{	field: 'apellido', align: 'left', valign: 'middle', title: 'Apellido', sortable: true	},
						{	field: 'cargo', align: 'left', valign: 'middle', title: 'Cargo', sortable: true	},
						{	field: 'departamento', align: 'left', valign: 'middle', title: 'Departamento', sortable: true	},
						{	field: 'ci', align: 'left', valign: 'middle', title: 'C.I.', sortable: true	},
						{	field: 'email', align: 'left', valign: 'middle', title: 'E-mail', sortable: true	},
						{	field: 'telefono', align: 'left', valign: 'middle', title: 'Teléfono', sortable: true	},
						{	field: 'celular', align: 'center', valign: 'middle', title: 'Celular', sortable: true	},
						{	field: 'direccion', align: 'center', valign: 'middle', title: 'Dirección', sortable: true	},
						{	field: 'fecha_registro', align: 'left', valign: 'middle', title: 'Fecha Alta', sortable: true	},
						{	field: 'id_sucursal', visible: false	},
						{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true	},
						{	field: 'estado', align: 'center', valign: 'middle', title: 'Estado', sortable: true	}, 
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarFila,  formatter: iconoEditar	}
					]
				]
			});

			//oculta columnas
			$('#tabla').bootstrapTable('hideColumn', 'id_usuario');
			$('#tabla').bootstrapTable('hideColumn', 'id_rol');
			
			//Roles
			$.ajax({
				dataType: 'json', async: true, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'roles'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#rol_carga').append('<option value="'+ value.id_rol + '">' + value.rol + '</option>');
						$('#rol_editar').append('<option value="'+ value.id_rol + '">' + value.rol + '</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			//Departamentos
			$.ajax({
				dataType: 'json', async: true, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'departamentos'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#departamento_carga').append('<option value="'+ value.departamento + '">' + value.departamento + '</option>');
						$('#departamento_editar').append('<option value="'+ value.departamento + '">' + value.departamento + '</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			//Sucursales
			$.ajax({
				dataType: 'json', async: true, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#sucursal_carga').append('<option value="'+ value.id_sucursal + '">' + value.sucursal + '</option>');
						$('#sucursal_editar').append('<option value="'+ value.id_sucursal + '">' + value.sucursal + '</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga
			function limpiarModal(){
				$('textarea').val("");
				$('input').val(""); 
				$("#msjModalAdd").html("");
				$("#msjModalEditar").html("");
			}
			
			$('.modal-agregar').on('shown.bs.modal', function (e) {
				$("#usuario_carga").focus();
			});
						
			$("#usuario_carga").focusout(function() {
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
				$("#hidden_id_usuario").val(row.id_usuario);
				$("#usuario_editar").val(row.nombre_usuario);
				$("#nombre_editar").val(row.nombre);
				$("#apellido_editar").val(row.apellido);
				$("#ci_editar").val(row.ci);
				$("#cargo_editar").val(row.cargo);
				$("#departamento_editar").val(row.departamento);
				$("#sucursal_editar").val(row.id_sucursal);
				$("#telefono_editar").val(row.telefono);
				$("#celular_editar").val(row.celular);
				$("#direccion_editar").val(row.direccion);
				$("#email_editar").val(row.email);
				$("#rol_editar").val(row.id_rol);
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
			$("#titulo_eliminar").html("¿Desea eliminar el usuario: <strong>"+nombre+"</strong>?");			
		}
		
		//Cambiar contraseña
		 function cambiarPassword(nombre){
			  var txt;
				var r = confirm("Esto restablecerá la contraseña del usuario '"+nombre+"' por: '"+nombre+"' para que pueda elegir una nueva. ¿Desea continuar?");
				if (r == true) {
					confirmaReseteo(nombre)
				}
		}
		
		function confirmaReseteo(nombre){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/administrar-usuarios-data.php',
				cache: false,
				data: {q: 'restablecer_password', id: $("#hidden_id_usuario").val(), nombre: nombre },	
				beforeSend: function(){
					$("#msjModalEditar").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					$('.modal-editar').modal('hide');
					setTimeout(function () {
						  location.reload();
					}, 1500);
				},
				error: function (xhr) {
					$("#msjModalEditar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
        }
		
		
		function confirmarBorrado(){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/administrar-usuarios-data.php',
				cache: false,
				data: {q: 'eliminar', id: $("#hidden_id_usuario").val(), nombre: $("#nombre_borrar").val() },	
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
		
		function verificaUsuario(input_usu,msj){
			var usu = $("#"+input_usu).val();
			if (usu){
				$("#"+msj).html("<img src='images/progress_bar.gif'>");
				$.ajax({
					dataType: 'html',
					type: 'POST',
					url: 'inc/administrar-usuarios-data.php',
					cache: false,
					data: {q: 'ver_usuario', usuario: usu },	
					beforeSend: function(){
						$("#"+msj).html("<img src='images/progress_bar.gif'>");
					},
					success: function (data, status, xhr) {
						$("#"+msj).html(data);
					},
					error: function (xhr) {
						$("#mensaje_eliminar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
				
				return false;
			}
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
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Agregar Usuario</button>
							</div>
						</div>
					</div>

					<table id="tabla" data-url="inc/administrar-usuarios-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-page-list="[10, 50, 100]" data-classes="table table-hover table-condensed" data-striped="true"></table>
						
				</div>
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	<!-- MODA AGREGAR -->
	<div class="modal modal-agregar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Agregar Usuario<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-usuarios-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Usuario (Login)</label>
												<input class="form-control input-sm" type="text" name="usuario_carga" id="usuario_carga" autocomplete="off" onblur="verificaUsuario(this.id,'msj_usuario_carga');"><span id="msj_usuario_carga"></span>
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Nombre</label>
												<input class="form-control input-sm" type="text" name="nombre_carga" id="nombre_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Apellido</label>
												<input class="form-control input-sm" type="text" name="apellido_carga" id="apellido_carga" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>C.I.</label>
												<input class="form-control input-sm" type="text" name="ci_carga" id="ci_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Teléfono</label>
												<input class="form-control input-sm" type="text" name="telefono_carga" id="telefono_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Celular</label>
												<input class="form-control input-sm" type="text" name="celular_carga" id="celular_carga" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Dirección particular</label>
												<input class="form-control input-sm" type="text" name="direccion_carga" id="direccion_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>E-mail</label>
												<input class="form-control input-sm" type="email" name="email_carga" id="email_carga" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Cargo</label>
												<input class="form-control input-sm" type="text" name="cargo_carga" id="cargo_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Departamento</label>
												<select id="departamento_carga" name="departamento_carga" class="form-control input-sm" style="padding: 0">
												<option value=''></option>
												</select>
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Sucursal</label>
												<select id="sucursal_carga" name="sucursal_carga" class="form-control input-sm" style="padding: 0">
												<option value=''></option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Rol en el Sistema</label>
												<select id="rol_carga" name="rol_carga" class="form-control input-sm" style="padding: 0" required>
												<option value=''></option>
												</select>
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Contraseña expirada</label>
												<input class="form-control input-sm" type="text" name="password_carga" id="password_carga" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="checkbox">
									<label>
									  <input type="checkbox" disabled checked>El Usuario deberá cambiar la contraseña al iniciar sesión en el Sistema
									</label>
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
	<!-- MODAL EDITAR -->
	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Editar Usuario<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<!-- MODAL ELIMINAR -->
					<div class="modal modal-eliminar" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
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
					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/administrar-usuarios-data.php?q=editar">
						<input type="hidden" id="hidden_id_usuario" name="hidden_id_usuario">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Usuario (Login)</label>
												<input class="form-control input-sm" type="text" name="usuario_editar" id="usuario_editar" disabled readonly>
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Nombre</label>
												<input class="form-control input-sm" type="text" name="nombre_editar" id="nombre_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Apellido</label>
												<input class="form-control input-sm" type="text" name="apellido_editar" id="apellido_editar" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>C.I.</label>
												<input class="form-control input-sm" type="text" name="ci_editar" id="ci_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Teléfono</label>
												<input class="form-control input-sm" type="text" name="telefono_editar" id="telefono_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<div class="form-group">
												<label>Celular</label>
												<input class="form-control input-sm" type="text" name="celular_editar" id="celular_editar" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Dirección particular</label>
												<input class="form-control input-sm" type="text" name="direccion_editar" id="direccion_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>E-mail</label>
												<input class="form-control input-sm" type="email" name="email_editar" id="email_editar" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Cargo</label>
												<input class="form-control input-sm" type="text" name="cargo_editar" id="cargo_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Departamento</label>
												<select id="departamento_editar" name="departamento_editar" class="form-control input-sm" style="padding: 0">
												<option value=''></option>
												</select>
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Sucursal</label>
												<select id="sucursal_editar" name="sucursal_editar" class="form-control input-sm" style="padding: 0">
												<option value=''></option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Rol en el Sistema</label>
												<select id="rol_editar" name="rol_editar" class="form-control input-sm" style="padding: 0" required>
												<option value=''></option>
												</select>
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-6">
											<div class="form-group">
												<label>Estado</label>
												<select id="estado_editar" name="estado_editar" class="form-control input-sm" style="padding: 0" required>
												<option value='1'>Habilitado</option>
												<option value='0'>Deshabilitado</option>
												<option value='2'>Contraseña expirada</option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div id="msjModalEditar"></div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<button onclick="preguntaBorrado($('#usuario_editar').val())" type="button" class="btn btn-danger btn-md submit_btn">Eliminar Usuario</button>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
											<button onclick="cambiarPassword($('#usuario_editar').val())" type="button" class="btn btn-warning btn-md submit_btn">Cambiar Contraseña</button>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-4">
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