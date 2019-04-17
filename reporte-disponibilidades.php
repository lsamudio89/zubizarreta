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
	
    <script type="text/javascript">
    

			
	
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			
			var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/reporte-disponibilidades-data.php', type: 'POST', data: {q: 'ver'},
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
				pageSize:30,
				columns: [
					[
						{	field: 'id_sucursal', title: 'ID', align: 'center', valign: 'middle', sortable: true, }, 
						{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true},
						{	field: 'disponibilidades', align: 'right', valign: 'middle', title: 'Disponibilidades', sortable: true, footerFormatter: sumatoria, formatter: moneda, editable:false	}
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
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			
			<!--
			<div id="mensaje" style="margin-bottom: 10px;"></div>
				<div id="toolbar">
					<div class="form-inline" role="form">
		                <div class="form-group">
									<div id="reportrange" class="btn btn-default form-control" style="background: #fff; cursor: pointer; padding: 10px 10px; border: 1px solid #ccc; width: 100%">
									<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
									<span></span> <b class="caret"></b>
								</div>
			            </div>    
			        </div>
			     </div>		 NO NECESITA RANGO DE FECHAS-->
					
					<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-page-list="[15, 50, 100]" data-classes="table table-hover table-condensed" data-show-footer='true'></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>