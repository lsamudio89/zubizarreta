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
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-search aria-hidden="true"></span>&nbsp;&nbsp;Ver Sucursales</button>'
					/*'<a class="editar" href="javascript:void(0)" title="Editar">',
					'<i class="glyphicon glyphicon-record"></i>',
					'</a>'*/
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
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Nombre / Descripción', sortable: true	},
						{	field: 'precio_vta_min', align: 'left', valign: 'middle', title: 'Precio Minorista', sortable: true	},
						{	field: 'precio_vta_may', align: 'left', valign: 'middle', title: 'Precio Mayorista', sortable: true	},
						{	field: 'ver-sucursales', align: 'center', valign: 'middle', title: 'Ver Sucursales', sortable: false, events: editarFila,  formatter: iconoEditar	}
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

			
		
			///MODAL EDITAR
			function mostrarModalEditar(row){
			id_producto=row.id_producto;	
			$('#h4_modal').text('Producto: ' +id_producto);	
			$("#contenido_modal").html("<img src='images/progress_bar.gif'>");
			$("#contenido_modal").load("inc/reporte-productos-por-sucursal-data.php?q=productos_por_sucursal&id_producto="+id_producto);
			$('.modal-editar').modal('show');
			}
			


			$(".modal").draggable({
				handle: ".modal-header"
			});

        });
		

			
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
					<table id="tabla" data-url="inc/reporte-productos-por-sucursal-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	
	

	<!-- MODAL EDITAR -->
	<div class="modal modal-editar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title" id="h4_modal">Producto por Sucursal<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body" id="contenido_modal">

				</div>
			</div>
		</div>
	</div>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>