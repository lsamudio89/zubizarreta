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

    <script type="text/javascript">
	
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			function color(value, row, index) {
				 if (parseInt(row.total) <= parseInt(row.stock_minimo)){
					return { css: {"color": "#b30000", "font-weight": "bold"}};
				}else{
					return { css: {"color": "inherit", "font-weight": "normal"} };
				}
				 
			}
			
			
			$table.bootstrapTable({
				height: $(window).height()-190,
				pageSize: Math.floor(($(window).height()-190)/28)-3,
				sortName: 'total',
				sortOrder: 'asc',
				columns: [
					[
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Nombre / Descripción', sortable: true, cellStyle: color},
						{	field: 'luque', align: 'center', valign: 'middle', title: 'Luque', sortable: true, cellStyle: color	},
						{	field: 'casamatriz', align: 'center', valign: 'middle', title: 'Casa Matriz', sortable: true, cellStyle: color	},
						{	field: 'sanlorenzo', align: 'center', valign: 'middle', title: 'San Lorenzo', sortable: true, cellStyle: color	},
						{	field: 'alberdi', align: 'center', valign: 'middle', title: 'Alberdi', sortable: true, cellStyle: color	},
						{	field: 'deposito', align: 'center', valign: 'middle', title: 'Deposito', sortable: true, cellStyle: color	},
						{	field: 'total', align: 'center', valign: 'middle', title: 'Total', sortable: true, cellStyle: color	},
						{	field: 'stock_minimo', align: 'left', valign: 'middle', title: 'Stock Mín.', sortable: true, cellStyle: color}
					]
				]
			});
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('refreshOptions', { 
					height: $(window).height()-190,
					pageSize: Math.floor(($(window).height()-190)/28)-3
				});
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
					<table id="tabla" data-url="inc/reporte-stock-minimo-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	
	
    <script src="js/menuHover.js"></script>
  </body>
</html>