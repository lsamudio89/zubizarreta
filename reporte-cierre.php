<?php
	include 'inc/reporte-cierre-data.php';
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
    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
    
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
		<link rel="stylesheet" href="css/bootstrap-editable.css">
	 <script src="js/bootstrap-table-editable.js"></script>
	    <script src="js/bootstrap-editable.js"></script>
	
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
    
        <?php
		if (empty($_POST['fecha'])){
			$hoy=date('Y-m-d');	
		}else{
			$hoy=$_POST['fecha'];	
		}
		
							
		if ($_POST['id_sucursal']){
			$id_sucursal=$_POST['id_sucursal'];	
		}else{
			$id_sucursal=$_SESSION['id_sucursal'];	
		}
		
		?>

    <script type="text/javascript">

		$(document).ready(function () {
			var $table = $('#tabla');	
			
			
			var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/reporte-cierre-data.php', type: 'POST', data: {q: 'ver', fecha: '<?php echo $hoy; ?>', id_sucursal: '<?php echo $id_sucursal; ?>'},
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
			});
			
			function iconoVer(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-search aria-hidden="true"></span>&nbsp;&nbsp;Ver</button>'
				].join('');
			}
			
			window.verFila = {
				'click .editar': function (e, value, row, index) {
					 if (row.tipo=="Factura"){
					 var param = { 'id':row.id_factura, 'imprimir':'no', 'recargar':'no' };
					 OpenWindowWithPost("imprimir-factura.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=860,height=600", "ImprimirFactura", param);
					 }else{
						 var param = { 'id':row.id_factura, 'imprimir':'no', 'recargar':'no' };
						OpenWindowWithPost("imprimir-comprobante.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=860,height=600", "ImprimirComprobante", param);
					 }
				}
			};
			
			
			$table.bootstrapTable({
				data: datos,
				pageSize:30,
				columns: [
					[
						{	field: 'id_factura', title: 'ID Factura', align: 'center', valign: 'middle', sortable: true, }, 
						{	field: 'numero', title: 'Nº Factura', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'total_a_pagar', align: 'right', valign: 'middle', title: 'Total Venta', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'total_efectivo', align: 'right', valign: 'middle', title: 'Total Efectivo', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'total_tarjeta', align: 'right', valign: 'middle', title: 'Total Tarjeta', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'total_cheque', align: 'right', valign: 'middle', title: 'Total Cheque', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'total_giro', align: 'right', valign: 'middle', title: 'Total Giro', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'metodo_pago', align: 'center', valign: 'middle', title: 'Método Pago', sortable: true, editable:false	},
						{	field: 'total_descuento', align: 'right', valign: 'middle', title: 'Total Descuento', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'total_nota', align: 'right', valign: 'middle', title: 'Total Nota CR', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	},
						{	field: 'tipo_venta', align: 'center', valign: 'middle', title: 'Tipo Venta', sortable: true},
						{	field: 'usuario', align: 'center', valign: 'middle', title: 'Usuario', sortable: true},
						{	field: 'ver', align: 'center', valign: 'middle', title: 'Ver', sortable: false, events: verFila,  formatter: iconoVer	}
					]
				]
			});
	
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('refreshOptions', { 
					height: $(window).height()-172,
					pageSize: Math.floor(($(window).height()-172)/28)-5 
				});
			});
			
			function moneda(value){
			return '<div>' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")+
			' Gs.</div>'; 
			}
			
			
			function sumatoria(data) {
			field = this.field;	
			var total = 0;
			$.each(data, function (i, row) {
			total += parseInt(row[field]);
			});
			return '<b>Gs. ' + separadorMiles(total)+'</b>';
			}
						
			
		//CALENDARIO
		var fechaIni;
		var fechaFin;
		
		function cb(start, end) {
			fechaIni = start.format('DD/MM/YYYY');
			fechaFin = end.format('DD/MM/YYYY');
			$('#reportrange span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));
		}
		cb(moment(), moment());
		$('#reportrange').daterangepicker({
			timePicker: false,
			opens: "right",
			format: 'DD/MM/YYYY',
			locale: {
				applyLabel: 'Aplicar',
				cancelLabel: 'Borrar',
				fromLabel: 'Desde',
				toLabel: 'Hasta',
				customRangeLabel: 'Personalizado',
				daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
				monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"],
				firstDay: 1
			},
			ranges: {
			   'Hoy': [moment(), moment()],
			   'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			   'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
			   'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
			   'Este Mes': [moment().startOf('month'), moment().endOf('month')],
			   'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			}
		}, cb);
		
		$('#reportrange').on('apply.daterangepicker', function(ev, picker) { 
			fechaIni = picker.startDate.format('DD/MM/YYYY');
			fechaFin = picker.endDate.format('DD/MM/YYYY');
			RecargarGastos(fechaIni,fechaFin);
		});
		//FIN CALENDARIO
			
        });
		
				
    </script>
    
    	<link rel="stylesheet" type="text/css" media="all" href="css/daterangepicker-bs3.css" />
    <script type="text/javascript" src="js/moment.js"></script>
  	<script type="text/javascript" src="js/daterangepicker.js"></script>
    
    	<style type="text/css">
		panel-body-ventas {
			padding: 5px
		}
		
		#reportrange {
		background: #ffffff;
		-webkit-box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1);
		-moz-box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1);
		box-shadow: 0 1px 3px rgba(0,0,0,.25), inset 0 -1px 0 rgba(0,0,0,.1);
		color: #333333;
		padding: 8px;
		line-height: 18px;
		cursor: pointer;
		}
		#reportrange .caret {
			margin-top: 1px;
			margin-left: 2px;
		}
		#reportrange span {
			padding-left: 3px;
		}
	
		#ocultar{
			float:right;
		}
	</style>
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
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?> - <?php echo fechaLatina($hoy);?></h2>
			</div>
			<div id="mensaje" style="margin-bottom: 10px;"></div>
			<div id="toolbar">
				<div class="form-inline" role="form">
					<form method="POST" action="">
						<?php
						//solo el superadmin puede ver los cierres de todas las sucursales, despliega select
						if ($_SESSION['id_rol']==1){
							echo "
							<div class='form-group'>";
								SelectSucursales(1,$id_sucursal);
							echo "</div>";
						}
						?>
						<div class="form-group">
							<input type="date"  class="form-control input-sm" name="fecha" value="<?php echo $hoy; ?>">
							<button type="submit" class="btn btn-primary">Ver</button>
							<button type="button" class="btn btn-primary" onclick="Imprimir();">Imprimir</button>
						</div>
					</form>
				</div>   
			</div>
			
			<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
			data-pagination="false" data-classes="table table-hover table-condensed" data-show-footer='true'></table>
			<!--<h4>Productos Vendidos</h4>
			ProductosVendidos($hoy,$id_sucursal);
			-->
			<br>
			<h4>Resumen Totales</h4>
			<?php
			ResumenTotales($hoy,$id_sucursal,$usuario);
			?>
		</div> <!-- /container -->
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
    <script src="js/menuHover.js"></script>
    
    <script>
    function Imprimir(){
    window.print();
    }
    </script>
  </body>
</html>