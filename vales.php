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

   <title><?php echo nombrePagina($pag)." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>

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
		.modal-anular{z-index:999999}
    </style>
	
    <script type="text/javascript">
	
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			function icono(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)"',
					'class="btn btn-primary btn-xs anular">',
					'<span class="glyphicon glyphicon-delete aria-hidden="true"></span>&nbsp;&nbsp;Anular</button>'
				].join('');
			}
			
			window.anular = {
				'click .anular': function (e, value, row, index) {
					if (row.nombre_estado=='Anulado'){
						alert('Vale ya se encuentra anulado');
					}else{
						preguntaAnular(row.id_vale,row.nro_vale);
					}
				}
			};
			
			function colorEstado(value, row, index) {
				var color = ['#000000', '#d90000'];
				switch(value){
					case 'Anulado':
					return {
							css: {"color": color[1], "font-weight": "bold"}
						};
					break;
				}
				return {};
			}
		
			$table.bootstrapTable({
				height: $(window).height()-190,
				pageSize: Math.floor(($(window).height()-190)/28)-6,
				sortOrder: 'desc',
				columns: [
					[
						{	field: 'id_vale', align: 'left', valign: 'middle', title: 'ID Vale', sortable: true}, 
						{	field: 'fecha', align: 'left', valign: 'middle', title: 'Fecha', sortable: true}, 
						{	field: 'nro_vale', align: 'left', valign: 'middle', title: 'Nro. de Vale', sortable: true	},
						{	field: 'monto', align: 'left', valign: 'middle', title: 'Monto', sortable: true	},
						{	field: 'motivo', align: 'left', valign: 'middle', title: 'Motivo', sortable: true	},
						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario', sortable: true}, 
						{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true	}, 
						{	field: 'nombre_estado', align: 'left', valign: 'middle', title: 'Estado', sortable: true, cellStyle: colorEstado	}, 
						{	field: 'anular', align: 'center', valign: 'middle', title: 'Anular', sortable: false, events: anular,  formatter: icono	}
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
				$('input').val(""); 
				$("#msjModal").html("");
			}
			
			$('.modal-agregar').on('shown.bs.modal', function (e) {
				$("#mensaje").html("");
				$("#nro_vale").focus();
			});
						
			$("#form_agregar").submit(function(event){
				$("#msjModal").html("");
				
				//Evita el submit default del php
				event.preventDefault();
			
				var formData = $("#form_agregar").serializeArray();
				var URL = $("#form_agregar").attr("action");
					$("#msjModal").html("<img src='images/progress_bar.gif'>");
				$.post(URL, formData, function() {
				})
				.done(function(data) {
					$('#msjModal').html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						setTimeout(function () {
							location.reload();
						}, 200);
					}
				})
				.fail(function(jqXHR) {
					$('#msjModal').html(alertDismissJS(jqXHR.responseText, "error"));
				});
			});

			$('.modal-agregar').on('hide.bs.modal', function (e) {
				limpiarModal();
			});
			
			$('.modal-anular').on('hide.bs.modal', function (e) {
				$("#mensaje_anular").html("");
			});

			$(".modal").draggable({
				handle: ".modal-header"
			});

      });
		
		//ANULAR
		 function preguntaAnular(id_vale, numero){
			$('.modal-anular').modal('show');
			$("#hidden_id_vale").val(id_vale);
			$("#titulo_anular").html("¿Desea anular el vale: <strong>"+numero+"</strong>?");			
		}
		
		function confirmarAnular(){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/vales-data.php',
				cache: false,
				data: {q: 'anular', id: $("#hidden_id_vale").val() },	
				beforeSend: function(){
					$("#mensaje_anular").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					$('.modal-anular').modal('hide');
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						$('#tabla').bootstrapTable('refresh', {url: 'inc/vales-data.php?q=ver'});
					}
				},
				error: function (xhr) {
					$("#mensaje_anular").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
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
								<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">Cargar Vale</button>
							</div>
						</div>
					</div>
					<table id="tabla" data-url="inc/vales-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	<!-- MODA AGREGAR -->
	<div class="modal modal-agregar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Cargar Vale<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/vales-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="nro_vale">Nro. de Vale</label>
												<input class="form-control input-sm" type="text" name="nro_vale" id="nro_vale" autocomplete="off">
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group">
												<label for="monto">Monto</label>
												<input class="form-control input-sm" type="text" name="monto" id="monto" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<label for="motivo">Motivo</label>
												<input class="form-control input-sm" type="text" name="motivo" id="motivo" autocomplete="off">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<span id="msjModal"></span>
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
	
	<!-- MODAL ANULAR -->
	<div class="modal modal-anular" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
				<h4 id="mySmallModalLabel" class="modal-title">Anular Vale<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
			</div>
			<div class="modal-body" id="titulo_anular">
				&nbsp;
			</div>
			<div class="modal-footer">
				<div id="mensaje_anular" style="float:left"></div>
				<input type="hidden" id="hidden_id_vale">
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger btn-sm" onclick="confirmarAnular()">Anular</button>
			</div>
		</div>
	  </div>
	</div>

    <script src="js/menuHover.js"></script>
  </body>
</html>