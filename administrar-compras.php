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
	<script src="bootstrap-table/extensions/editable/bootstrap-table-editable.js"></script>
    <script src="bootstrap-table/extensions/editable/bootstrap-editable.js"></script>
	<link rel="stylesheet" href="bootstrap-table/extensions/editable/css/bootstrap-editable.css">
	
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
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-edit aria-hidden="true"></span>&nbsp;&nbsp;Editar</button>'
				].join('');
			}
			
			window.editarFila = {
				'click .editar': function (e, value, row, index) {
					mostrarModalEditar(row);
				}
			};
		
			$table.bootstrapTable({
				height: $(window).height()-210,
				pageSize: Math.floor(($(window).height()-210)/24)-6,
				columns: [
					[
					
						{	field: 'id_compra_detalle', align: 'left', valign: 'middle', title: 'ID Compra', sortable: true	}, 
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID Producto', sortable: true, visible: false	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Producto', sortable: true	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cant. Comprada', sortable: true	},
						{	field: 'cant_recibida', align: 'center', valign: 'middle', title: 'Cant. ya recibida', sortable: true },
						{	field: 'cant_pendiente', align: 'center', valign: 'middle', title: 'Cant. pendiente', sortable: true },
						{	field: 'cant_a_recibir', align: 'center', valign: 'middle', title: 'Cant. a recibir', sortable: true, editable: true	},
						{	field: 'costo', align: 'center', valign: 'middle', title: 'Costo', sortable: true	},
						{	field: 'fecha', align: 'left', valign: 'middle', title: 'Fecha Compra', sortable: true	},
						{	field: 'usuario', align: 'center', valign: 'middle', title: 'Usuario', sortable: true	}, 
						//{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: editarFila,  formatter: iconoEditar	}
					]
				]
			});
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('refreshOptions', { 
					height: $(window).height()-210,
					pageSize: Math.floor(($(window).height()-210)/24)-6
				});
			});
			
			//Acciones al editar la tabla
			$table.editable.defaults.onblur = 'submit';
			$table.editable.defaults.tpl = '<input type="text" onkeyup="return separadorMilesOnKey(event,this)" style="width:100%;padding:2px 5px 2px 5px">';
			
			$table.on('editable-save.bs.table', function (e, field, row, old, $el) {
				var indice = $el.closest('tr').data('index');
				var cant_a_recibir = quitaSeparadorMiles(row[field].replace(/^0+(?=\d)/, '')); //Borramos ceros al inicio
				if (cant_a_recibir > row.cant_pendiente){
					$table.bootstrapTable('updateCell', {index:indice, field:"cant_a_recibir", value: old});
					alert("Cantidad a recibir no puede ser mayor al pendiente.");
				}else{
					//var cant_pendiente = parseInt(quitaSeparadorMiles(row.cant_pendiente)) - cant_a_recibir;
					//$table.bootstrapTable('updateCell', {index:indice, field:"cant_pendiente", value: separadorMiles(cant_pendiente) });
				}
				$table.bootstrapTable('resetView', {});
			});

        });
		
		function guardar(){
			$.ajax({
				dataType: 'html',
				async: true,
				type: 'POST',
				url: 'inc/administrar-compras-data.php',
				cache: false,
				data: {q: 'guardar', datos: $('#tabla').bootstrapTable('getData') },	
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					var n = data.toLowerCase().indexOf("error");
						if (n == -1) {
							setTimeout(function () {
								  location.reload();
							}, 1500);
						}
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
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
					
					</div>
					<table id="tabla" data-url="inc/administrar-compras-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>
					<div class="form-inline" role="form" style="float:right">
						<div class="form-group" >
							<button type="button" class="btn btn-success form-control" id="guardar" onclick="javascript:guardar()">Guardar cambios</button>
						</div>
					</div>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>

    <script src="js/menuHover.js"></script>
  </body>
</html>