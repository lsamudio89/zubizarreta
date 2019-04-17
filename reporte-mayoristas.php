<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_rol = datosUsuario($id_usuario)->rol;
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
	

	
	<!-- Custom style -->
   <link href="css/theme.css" rel="stylesheet">
	
   <script type="text/javascript">
	$(document).ready(function () {
		
			var $table = $('#tabla');	
			
			//Sucursales
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					<?php if ($id_rol==1){ ?>
						$.each(json, function(key, value) {
							$('#sucursal').append('<option value="'+value.id_sucursal+'">'+value.sucursal+'</option>');
						});
						$('#sucursal').append('<option value="todas">TODAS</option>');
					<?php } else { ?>
						$('#sucursal').append('<option value="<?php echo $id_sucursal; ?>">Actual</option>');
					<?php } ?>
					$('#sucursal').val('<?php echo $id_sucursal; ?>');
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			
			var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/reporte-mayorista-data.php', type: 'POST', data: {id_sucursal: <?php echo $id_sucursal; ?>},
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

		
			$table.bootstrapTable({
				data: datos,
				height: $(window).height()-172,
				pageSize: Math.floor(($(window).height()-172)/28)-5,
				columns: [
					[
						{	field: 'ruc', align: 'left', valign: 'middle', title: 'RUC', sortable: true, width:'10%'	},
						{	field: 'razon_social', align: 'left', valign: 'middle', title: 'Razón Social', sortable: true	}, 
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Productos Vendidos', sortable: true, footerFormatter: sumatoria, width:'10%'	},
						{	field: 'costo', align: 'right', valign: 'middle', title: 'Costo', sortable: true, footerFormatter: sumatoria, width:'15%'	},
						{	field: 'total_a_pagar', align: 'right', valign: 'middle', title: 'Total a Pagar', sortable: true, footerFormatter: sumatoria, width:'15%'	},
						{	field: 'ganancia', align: 'right', valign: 'middle', title: 'Ganancia', sortable: true, footerFormatter: sumatoria, width:'15%'	}
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
			
			function moneda(valor){
				return valor.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
			}

			function sumatoria(data) {
				field = this.field;	
				var total = 0;
					$.each(data, function (i, row) {
					total += quitaSeparadorMiles(row[field]);
				});
				if (field=="cantidad"){
					var moneda="";
				}else{
					var moneda="Gs. ";
				}
				return '<b style="font-size:18px">'+moneda+separadorMiles(total)+'</b>';
			}
			
			
		//CALENDARIO
		function cb(start, end) {
			desde = start.format('DD/MM/YYYY');
			hasta = end.format('DD/MM/YYYY');
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
			desde = picker.startDate.format('DD/MM/YYYY');
			hasta = picker.endDate.format('DD/MM/YYYY');
			$("#desde").val(picker.startDate.format('YYYY-MM-DD'));	
			$("#hasta").val(picker.endDate.format('YYYY-MM-DD'));
			recargarDatos(desde,hasta);
		});
		//FIN CALENDARIO
		
		$('#sucursal').change(function(){
			recargarDatos(desde,hasta);
		});

			
   });
	
	function recargarDatos(desde,hasta){
			desde=$("#desde").val();	
			hasta=$("#hasta").val();
			id_sucursal=$("#sucursal").val();
			
			var datos;
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/reporte-mayorista-data.php', type: 'POST', data: {id_sucursal: id_sucursal, desde: desde, hasta: hasta},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
				if (json) datos = json;
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error_span'));
				}
			});
			$('#tabla').bootstrapTable("load", datos);
		}
				
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
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div id="toolbar">
						<div class="form-inline" role="form">
							<input type="hidden" value="" id="desde">
							<input type="hidden" value="" id="hasta">

							<div class="form-group">
								<div id="reportrange" class="btn btn-default form-control" style="background: #fff; cursor: pointer; padding: 10px 10px; border: 1px solid #ccc; width: 100%">
									<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
									<span></span> <b class="caret"></b>
								</div>
							</div>
							<?php if ($id_rol==1){ $display="visible"; }else{ $display="hidden"; } ?>
							<div class="form-group" style="visibility:<?php echo $display; ?>">
							&nbsp;&nbsp;&nbsp;&nbsp;
							<label>Sucursal:</label>&nbsp;&nbsp;
								<select id="sucursal" name="sucursal" class="form-control input-sm">
							</select>
							</div>

							&nbsp;&nbsp;&nbsp;&nbsp;
							<span id="mensaje"></span>	
						</div>
					</div>

					<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="false" data-classes="table table-hover table-condensed" data-show-footer='true'></table>
				</div>
			</div>		
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>