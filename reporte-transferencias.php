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
			
			function icono(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)"',
					'class="btn btn-primary btn-xs ver">',
					'<span class="glyphicon glyphicon-search aria-hidden="true"></span>&nbsp;&nbsp;Ver Detalles</button>'
				].join('');
			}
			
			window.verFila = {
				'click .ver': function (e, value, row, index) {
					mostrarModal(row);
				}
			};

			$table.bootstrapTable({
				height: $(window).height()-170,
				pageSize: Math.floor(($(window).height()-170)/28)-6,
				sortName: 'fecha',
				sortOrder: 'desc',
				columns: [
					[
						{	field: 'id_compra_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: true }, 
						{	field: 'fecha', align: 'left', valign: 'middle', title: 'Fecha', sortable: true	},
						{	field: 'sucursal_origen', align: 'left', valign: 'middle', title: 'Sucursal Origen', sortable: true	},
						{	field: 'sucursal_destino', align: 'left', valign: 'middle', title: 'Sucursal Destino', sortable: true	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Total de Productos', sortable: true	},
						{	field: 'total_costo', align: 'right', valign: 'middle', title: 'Total Costo', sortable: true	},
						{	field: 'descripcion', align: 'left', valign: 'middle', title: 'Observaciones', sortable: true	},
						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario', sortable: true, visible: false	}, 
						{	field: 'ver', align: 'center', valign: 'middle', title: 'Ver Detalles', sortable: false, events: verFila,  formatter: icono	}
					]
				]
			});
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('refreshOptions', { 
					height: $(window).height()-170,
					pageSize: Math.floor(($(window).height()-170)/28)-6
				});
			});
			
			
			
			$('#tabla_detalles').bootstrapTable({
				height: $(window).height()-210,
				pageSize: Math.floor(($(window).height()-210)/26)-5,
				columns: [
					[
						{	field: 'id_compra_detalle', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: false	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Producto', sortable: true, footerFormatter: totales	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cantidad', sortable: true, footerFormatter: sumaCantidad	},
						{	field: 'costo', align: 'right', valign: 'middle', title: 'Costo', sortable: true, footerFormatter: sumaTotal	},
						{	field: 'fecha', align: 'center', valign: 'middle', title: 'Fecha', sortable: true	},
						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario', sortable: true	}
					]
				]
			});
			
			function totales() {
				return '<b style="font-size:16px">Totales:</b>';
			}
			
			function sumaCantidad(data) {
				field = this.field;	
				var cantidad=0;
					$.each(data, function (i, row) {
					cantidad += quitaSeparadorMiles(row[field]);
				});
				return '<b style="font-size:16px">'+separadorMiles(cantidad)+'</b>';
			}
			
			function sumaTotal(data) {
				field = this.field;	
				var costo=0, cantidad=0, total=0;
					$.each(data, function (i, row) {
					costo = quitaSeparadorMiles(row['costo']);
					cantidad = quitaSeparadorMiles(row['cantidad']);
					total += costo*cantidad;					
				});
				return '<b style="font-size:16px">Gs. '+separadorMiles(total)+'</b>';
			}

			function mostrarModal(row){
				$('.modal-ver').modal('show');
				$("#mensaje").html("");
				$("#toolbar_det").html("De: <label>"+row.sucursal_origen.toUpperCase()+"</label>&nbsp;&nbsp;a: <label>"+row.sucursal_destino.toUpperCase()+"</label>");
				$('#tabla_detalles').bootstrapTable("refresh", {url: 'inc/reporte-transferencias-data.php?q=ver_detalles&id='+row.id_compra_producto, pageSize: Math.floor(($(window).height()-210)/26)-5});
				$('#tabla_detalles').bootstrapTable('resetSearch', '');
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
				
			</div>
			<div class="row">
				<div class="col-md-12">
					<div id="toolbar">
						<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
						<span id="mensaje"></span>
					</div>
					<table id="tabla" data-url="inc/reporte-transferencias-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	<!-- MODAL EDITAR -->
	<div class="modal modal-ver" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Detalles de Transferencia<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<span id="toolbar_det" style="font-size:18px"></span>
					<table id="tabla_detalles" data-toolbar="#toolbar_det" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="false" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true" data-show-footer="true"></table>
				</div>
			</div>
		</div>
	</div>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>