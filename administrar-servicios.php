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
		.del{ z-index:5000; color:red; left:-17px; top:-9px; width:6px; height:6px; cursor:pointer; }
		.no-margin{ margin-bottom:0; }
		.modal-dialog-full { width: 100%; height: 99%; padding: 0; margin:0 }
		.modal-content-full { height: 99%; border-radius: 0; }
		.img_producto{ border:2px solid #2c3e50; cursor:pointer; margin:3px 3px 0 0; height:45px; width:80px; background-color:white; }
		.modal-eliminar{z-index:999999}
    </style>
	
    <script type="text/javascript">
	
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			function iconoEditar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>&nbsp;&nbsp;&nbsp;'
					/*'<a class="editar" href="javascript:void(0)" title="Editar">',
					'<i class="glyphicon glyphicon-edit"></i>',
					'</a>'*/
				].join('');
			}
			
			window.editarFila = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				},
				'click .imprimir': function (e, value, row, index) {
					var param = { 'id_producto': row.id_producto, 'imprimir' : 'no', 'recargar':'no' };
					OpenWindowWithPost("imprimir-etiqueta.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=200", "imprimirEtiqueta", param);
				}
			};
		
			$table.bootstrapTable({
				height: $(window).height()-190,
				pageSize: Math.floor(($(window).height()-190)/28)-6,
				columns: [
					[
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID Servicio', sortable: true	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Descripción / Servicio', sortable: true	},
						{	field: 'usuario', align: 'center', valign: 'middle', title: 'Usuario', sortable: true	}, 
						{	field: 'iva', align: 'center', valign: 'middle', title: 'I.V.A.', sortable: true	},
						{	field: 'tipo', align: 'center', valign: 'middle', title: 'Tipo', sortable: true	},
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Acciones', sortable: false, events: editarFila,  formatter: iconoEditar	}
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
				$.ajax({
					dataType: 'text', async: true, cache: false, url: 'inc/administrar-productos-data.php', type: 'POST', data: {q: 'sgte_id'},
					beforeSend: function(){
						$("#mensaje").html("<img src='images/progress_bar.gif'>");
					},
					success: function (datos){
						$("#mensaje").html("");
						$("#id_carga").val(datos);
					},
					error: function (xhr) {
						$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
				$("#producto_carga").focus();
			});
						
			$("#producto_carga").focusout(function() {
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
				$("#id_editar").val(row.id_producto);
				$("#producto_editar").val(row.producto);
				$("#stock").html(row.stock);
				$("#stock_minimo_editar").val(row.stock_minimo);
				$("#costo_editar").val(row.costo);
				$("#precio_vta_min_editar").val(row.precio_vta_min);
				$("#precio_distribuidor_editar").val(row.precio_distribuidor);
				$("#precio_vta_may_editar").val(row.precio_vta_may);
				$("#ganancia_min").html(row.ganancia_min);
				$("#ganancia_may").html(row.ganancia_may);
				$("#estado_editar").val(row.estado);
				$("#iva_editar").val(row.iva);
				$("#tipo_editar").val(row.tipo);
				//calculoGananciaMin();
				//calculoGananciaMay()
				//Mostramos las Fotos
				$.ajax({
					dataType: 'json', async: false, cache: false, url: 'inc/administrar-productos-data.php', type: 'POST', data: {q: 'ver_fotos', id_producto:row.id_producto },
					beforeSend: function(){
						$("#msjModalEditar").html("<img src='images/progress_bar.gif'>");
					},
					success: function (json){
						$("#div_fotos_editar").html("");
						var sum=1;
						$.each(json, function(key, v) {
							sum++;
							$("#div_fotos_editar").append('<span id="span_editar'+v.id_producto+sum+'"><img id="foto_editar'+v.id_producto+sum+'" class="img_producto" title="Click para elegir una foto desde el Administrador de Archivos" src="archivos/_thumbs/'+v.foto+'" onclick="BrowseServer(this.id)" alt="#"><span class="glyphicon glyphicon-remove del" aria-hidden="true" onclick="borrarImagen(\'span_editar'+v.id_producto+sum+'\')"></span><input type="hidden" id="foto_editar'+v.id_producto+sum+'_input" name="foto_editar[]" value="archivos/'+v.foto+'"></span>');
						});
						$("#msjModalEditar").html("");
					},
					error: function (xhr) {
						$("#msjModalEditar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});

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
						$table.bootstrapTable('refresh', {url: 'inc/administrar-productos-data.php?q=ver'});
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
		
		var urlobj;

		 function BrowseServer(obj)
		 {
			  urlobj = obj;
			  OpenServerBrowser(
			  'fileman/index.php',
			  screen.width * 0.8,
			  screen.height * 0.8 ) ;
		 }

		 function OpenServerBrowser( url, width, height )
		 {
			  var iLeft = (screen.width - width) / 3 ;
			  var iTop = (screen.height - height) / 3 ;
			  var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes" ;
			  sOptions += ",width=" + width ;
			  sOptions += ",height=" + height ;
			  sOptions += ",left=" + iLeft ;
			  sOptions += ",top=" + iTop ;
			  var oWindow = window.open( url, "BrowseWindow", sOptions ) ;
		 }
		 
		  function SetUrl(url, width, height, alt)
		 {
			var url_clean_tmp = url.split("?time");
			var url_clean = url_clean_tmp[0].replace("archivos/productos","archivos/_thumbs/productos");
			document.getElementById(urlobj+'_input').value = url_clean_tmp[0]; //Guardamos la ubicacion de la imagen
			$("#"+urlobj).attr("src", url_clean);  //Mostramos el thumbnail
			oWindow = null;
		 }
		
		function agregarDOMImg(tipo){
		  var cantidadFotos=parseInt($('#div_fotos_'+tipo).find("img").length);
		  cantidadFotos++;
		  $("#div_fotos_"+tipo).append('<span id="span_'+tipo+cantidadFotos+'"><img id="foto_'+tipo+cantidadFotos+'" class="img_producto" title="Click para elegir una foto desde el Administrador de Archivos" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" onclick="BrowseServer(this.id)" alt="#"><span class="glyphicon glyphicon-remove del" aria-hidden="true" onclick="borrarImagen(\'span_'+tipo+cantidadFotos+'\')"></span><input type="hidden" id="foto_'+tipo+cantidadFotos+'_input" name="foto_'+tipo+'[]" value=""></span>');
		  BrowseServer('foto_'+tipo+cantidadFotos);
		}
		  
		function borrarImagen(span){
		  var r = confirm("¿Eliminar foto seleccionada?");
			if (r == true) {
				$("#"+span).remove();
			}
		}
		
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
				url: 'inc/administrar-productos-data.php',
				cache: false,
				data: {q: 'eliminar', id_producto: $("#id_editar").val(), nombre: $("#nombre_borrar").val() },	
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
		  
			function calculoGananciaMin(){
				var totalGananciaMin = quitaSeparadorMiles($("#precio_vta_min_editar").val()) - quitaSeparadorMiles($("#costo_editar").val());
				if (!isNaN(totalGananciaMin)) {
					$("#ganancia_min").html(separadorMiles(totalGananciaMin));
					if (totalGananciaMin > 0){
						$("#ganancia_min").css("color","green");
					}else{
						$("#ganancia_min").css("color","red");
					}
				}
			}

			function calculoGananciaMay(){
				var totalGananciaMay = quitaSeparadorMiles($("#precio_vta_may_editar").val()) - quitaSeparadorMiles($("#costo_editar").val());
				if (!isNaN(totalGananciaMay)) {
					$("#ganancia_may").html(separadorMiles(totalGananciaMay));
					if (totalGananciaMay > 0){
						$("#ganancia_may").css("color","green");
					}else{
						$("#ganancia_may").css("color","red");
					}
				}
			}
			
			function imprimirCodigo(){
				var param = { 'id': data.id_factura, 'imprimir' : 'si', 'recargar':'si' };
				OpenWindowWithPost("imprimir-factura.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "imprimirFacturaMinorista", param);
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
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Agregar Servicios</button>
							</div>
						</div>
					</div>
					<table id="tabla" data-url="inc/administrar-productos-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
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
					<h4 class="modal-title">Agregar Servicio<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-productos-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-3 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="id_carga">Código</label>
												<input class="form-control input-sm" type="text" name="id_carga" id="id_carga" autocomplete="off" readonly style="text-align:center">
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_carga">Nombre / Descripción del Servicio</label>
												<input class="form-control input-sm" type="text" name="producto_carga" id="producto_carga" autocomplete="off">
											</div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_editar">I.V.A.</label>
												<select class="form-control input-sm" name="iva_carga" id="iva_carga" required>
												<option value="5">IVA 5%</option>	
												<option value="10">IVA 10%</option>
												<option value="0">EXENTA</option>
												</select>
											</div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_editar">Tipo</label>
												<select class="form-control input-sm" name="tipo_carga" id="tipo_carga" required>
												<option value="TASAS">TASAS</option>	
												<option value="GASTOS">GASTOS</option>
												<option value="HONORARIOS PROFESIONALES">HONORARIOS PROFESIONALES</option>
												<option value="OTROS">OTROS</option>
												</select>
											</div>
										</div>
									<div class="row">
										<div class="col-md-3 col-sm-3 col-xs-6">
											<div class="form-group">
												<label for="estado_carga">Estado</label>
												<select id="estado_carga" name="estado_carga" class="form-control input-sm" style="padding: 0">
												<option value='1'>Habilitado</option>
												<option value='0'>Deshabilitado</option>
												</select>
											</div>
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
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
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
	
	<!-- MODAL EDITAR -->
	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Editar Servicio<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_editar" method="post" enctype="multipart/form-data" action="inc/administrar-productos-data.php?q=editar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-3 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="id_editar">Código</label>
												<input class="form-control input-sm" type="text" name="id_editar" id="id_editar" autocomplete="off" readonly>
											</div>
										</div>
										<div class="col-md-4 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_editar">Nombre / Descripción del Servicio</label>
												<input class="form-control input-sm" type="text" name="producto_editar" id="producto_editar" autocomplete="off">
											</div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_editar">I.V.A.</label>
												<select class="form-control input-sm" name="iva_editar" id="iva_editar" required>
												<option value="5">IVA 5%</option>	
												<option value="10">IVA 10%</option>
												<option value="0">EXENTA</option>
												</select>
											</div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="producto_editar">TIPO</label>
												<select class="form-control input-sm" name="tipo_editar" id="tipo_editar" required>
												<option value="TASAS">TASAS</option>	
												<option value="GASTOS">GASTOS</option>
												<option value="HONORARIOS PROFESIONALES">HONORARIOS PROFESIONALES</option>
												<option value="OTROS">OTROS</option>
												</select>
											</div>
										</div>
										<div class="col-md-3 col-sm-3 col-xs-12">
											<div class="form-group">
												<label for="estado_editar">Estado</label>
												<select id="estado_editar" name="estado_editar" class="form-control input-sm" style="padding: 0">
												<option value='1'>Habilitado</option>
												<option value='0'>Deshabilitado</option>
												</select>
											</div>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div id="msjModalEditar"></div>
											<button onclick="preguntaBorrado($('#producto_editar').val())" type="button" class="btn btn-danger btn-md submit_btn" style="float:left">Eliminar Servicio</button>
											<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar Cambios</button>
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