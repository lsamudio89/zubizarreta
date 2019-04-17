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

		.no-margin{ margin-bottom:0; }

		.modal-eliminar{z-index:999999}

    </style>

	

    <script type="text/javascript">

	

		$(document).ready(function () {

			var $table = $('#tabla');	

			

			function iconoEditar(value, row, index) {

				return [

					'<button type="button" onclick="javascript:void(0)"',

					'class="btn btn-primary btn-xs editar">',

					'<span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>'

				].join('');

			}

			

			window.editarFila = {

				'click .editar': function (e, value, row, index) {

					mostrarModalEditar(row);

				}

			};

		

			$table.bootstrapTable({

				height: $(window).height()-190,

				pageSize: Math.floor(($(window).height()-190)/28)-6,

				columns: [

					[

						{	field: 'id_cliente', align: 'left', valign: 'middle', title: 'ID Cliente', sortable: true, visible: true }, 

						{	field: 'razon_social', align: 'left', valign: 'middle', title: 'Nombre / Razón Social', sortable: true	},

						{	field: 'ruc', align: 'left', valign: 'middle', title: 'RUC', sortable: true	},

						{	field: 'telefono', align: 'left', valign: 'middle', title: 'Teléfono', sortable: true	},

						{	field: 'direccion', align: 'left', valign: 'middle', title: 'Dirección', sortable: true	},

						{	field: 'email', align: 'left', valign: 'middle', title: 'E-mail', sortable: true	},

						//{	field: 'tipo', align: 'left', valign: 'middle', title: 'Tipo', sortable: true	}, 

						{	field: 'estado', align: 'left', valign: 'middle', title: 'ID Estado', sortable: true, visible: false	}, 

						{	field: 'nombre_estado', align: 'left', valign: 'middle', title: 'Estado', sortable: true}, 

						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario Carga', sortable: true, visible: false	}, 

						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarFila,  formatter: iconoEditar	}

					]

				]

			});

			

			//Altura de tabla automatica

			$(window).resize(function () {

				$table.bootstrapTable('refreshOptions', { 

					height: $(window).height()-190,

					pageSize: Math.floor(($(window).height()-190)/28)-6

				});

			});



			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga

			function limpiarModal(){

				$('textarea').val("");

				$('input').val(""); 

				$("#msjModalAdd").html("");

				$("#msjModalEditar").html("");

			}

			

			$('.modal-agregar').on('shown.bs.modal', function (e) {

				$("#mensaje").html("");

				$("#ruc_carga").focus();

			});

						

			$("#ruc_carga").focusout(function() {

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

				$("#mensaje").html("");

				$("#hidden_id_cliente").val(row.id_cliente);

				$("#razon_social_editar").val(row.razon_social);

				$("#ruc_editar").val(row.ruc);

				$("#telefono_editar").val(row.telefono);

				$("#direccion_editar").val(row.direccion);

				$("#email_editar").val(row.email);

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

					$('.modal-editar').modal('hide');

					$('#mensaje').html(data);

					var n = data.toLowerCase().indexOf("error");

					if (n == -1) {

						$('#tabla').bootstrapTable('refresh', {url: 'inc/administrar-clientes-data.php?q=ver'});

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

				url: 'inc/administrar-clientes-data.php',

				cache: false,

				data: {q: 'eliminar', id_cliente: $("#hidden_id_cliente").val(), nombre: $("#nombre_borrar").val() },	

				beforeSend: function(){

					$("#mensaje_eliminar").html("<img src='images/progress_bar.gif'>");

				},

				success: function (data, status, xhr) {

					$("#mensaje").html(data);

					$('.modal-eliminar, .modal-editar').modal('hide');

					var n = data.toLowerCase().indexOf("error");

					if (n == -1) {

						$('#tabla').bootstrapTable('refresh', {url: 'inc/administrar-clientes-data.php?q=ver'});

					}

				},

				error: function (xhr) {

					$("#mensaje_eliminar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));

				}

			});

        }

		

		function verificaRUC(input_ruc,input_razon,msj){

			var ruc = $("#"+input_ruc).val();

			$("#"+msj).html("Buscando RUC <img src='images/progress_bar.gif'>");

			$.getJSON("http://www.freelancer.com.py/ruc.php?ruc="+ruc+"&jsoncallback=?")

				.done(function(resp) {

					$("#"+msj).html("");

					if(resp.ruc){

						$("#"+input_razon).val(resp.razon_social);

						$("#"+input_ruc).val(resp.ruc);

					}

				})

				.fail(function(resp) {

					$('#'+msj).html("Error al consultar RUC. "+resp.responseText);

				})

			return false;

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

								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Registrar Cliente</button>

							</div>

						</div>

					</div>

					<table id="tabla" data-url="inc/administrar-clientes-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"

					data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>

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

					<h4 class="modal-title">Registrar Cliente<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>

				</div>

				<div class="modal-body">

					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-clientes-data.php?q=cargar">

						<div class="container-fluid">

							<div class="row">

								<div class="col-md-12 col-sm-12 col-xs-12">

									<div class="row">

										<div class="col-md-3 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="ruc_carga">RUC / CI</label>

												<input class="form-control input-sm" type="text" name="ruc_carga" id="ruc_carga" autocomplete="off" onblur="verificaRUC(this.id,'razon_social_carga','mensaje_ruc_carga');">

												<span id="mensaje_ruc_carga"></span>

											</div>

										</div>

										

										<div class="col-md-9 col-sm-12 col-xs-12">

											<div class="form-group">

												<label for="razon_social_carga">Nombre / Razón Social</label>

												<input class="form-control input-sm" type="text" name="razon_social_carga" id="razon_social_carga" autocomplete="off">

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-md-3 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="telefono_carga">Teléfono</label>

												<input class="form-control input-sm" id="telefono_carga" name="telefono_carga" type="text" autocomplete="off">

											</div>

										</div>

										<div class="col-md-9 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="direccion_carga">Dirección</label>

												<input class="form-control input-sm" id="direccion_carga" name="direccion_carga" type="text" autocomplete="off">

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-md-12 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="email_carga">E-mail</label>

												<input class="form-control input-sm" id="email_carga" name="email_carga" type="email" autocomplete="off">

											</div>

										</div>

<!-- 										<div class="col-md-4 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="tipo_carga">Tipo</label>

												<select id="tipo_carga" name="tipo_carga" class="form-control input-sm" style="padding: 0">

												<option value='Minorista'>Minorista</option>

												<option value='Mayorista'>Mayorista</option>

												</select>

											</div>

										</div> -->

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

	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">

		<div class="modal-dialog modal modal-md">

			<div class="modal-content">

				<div class="modal-header">

					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>

					<h4 class="modal-title">Editar Cliente<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>

				</div>

				<div class="modal-body">

					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/administrar-clientes-data.php?q=editar">

						<input type="hidden" id="hidden_id_cliente" name="hidden_id_cliente">

						<div class="container-fluid">

							<div class="row">

								<div class="col-md-12 col-sm-12 col-xs-12">

									<div class="row">

										<div class="col-md-3 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="ruc_editar">RUC / CI</label>

												<input class="form-control input-sm" type="text" name="ruc_editar" id="ruc_editar" autocomplete="off" onblur="verificaRUC(this.id,'razon_social_editar','mensaje_ruc_editar');">

												<span id="mensaje_ruc_editar"></span>

											</div>

										</div>

										

										<div class="col-md-9 col-sm-12 col-xs-12">

											<div class="form-group">

												<label for="razon_social_editar">Nombre / Razón Social</label>

												<input class="form-control input-sm" type="text" name="razon_social_editar" id="razon_social_editar" autocomplete="off">

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-md-3 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="telefono_editar">Teléfono</label>

												<input class="form-control input-sm" id="telefono_editar" name="telefono_editar" type="text" autocomplete="off">

											</div>

										</div>

										<div class="col-md-9 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="direccion_editar">Dirección</label>

												<input class="form-control input-sm" id="direccion_editar" name="direccion_editar" type="text" autocomplete="off">

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-md-8 col-sm-6 col-xs-12">

											<div class="form-group">

												<label for="email_editar">E-mail</label>

												<input class="form-control input-sm" id="email_editar" name="email_editar" type="email" autocomplete="off">

											</div>

										</div>

										<!-- <div class="col-md-3 col-sm-3 col-xs-12">

											<div class="form-group">

												<label for="tipo_editar">Tipo</label>

												<select id="tipo_editar" name="tipo_editar" class="form-control input-sm" style="padding: 0">

												<option value='Minorista'>Minorista</option>

												<option value='Mayorista'>Mayorista</option>

												</select>

											</div>

										</div>-->

										<div class="col-md-4 col-sm-3 col-xs-12">

											<div class="form-group">

												<label for="estado_editar">Estado</label>

												<select id="estado_editar" name="estado_editar" class="form-control input-sm" style="padding: 0">

												<option value='1'>Habilitado</option>

												<option value='0'>Deshabilitado</option>

												<option value='2'>Moroso</option>

												</select>

											</div>

										</div>

									</div>

									<div class="row">

										<div class="col-md-12 col-sm-12 col-xs-12">

											<div id="msjModalEditar"></div>

											<button onclick="preguntaBorrado($('#razon_social_editar').val())" type="button" class="btn btn-danger btn-md submit_btn" style="float:left">Eliminar</button>

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