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
	
	<script src="jquery-ui/jquery-ui.min.js"></script>
    
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	 
	 <style>
		#tabla {
			letter-spacing: 0.2px;
		}
	 </style>
	
    <script type="text/javascript">
    
	 
		datos="";
		
		function RecargarVentas(desde,hasta){
				
			desde=$("#desde2").val();	
			hasta=$("#hasta2").val();
			id_sucursal=$("#id_sucursal").val();
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/reporte-ventas-data.php', type: 'POST', data: {q: 'ver', id_sucursal: id_sucursal,desde: desde, hasta: hasta},
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
			$('#tabla').bootstrapTable("load", datos);
			//fin recarga la tabla	
		}
		
		
		$(document).ready(function () {
			var $table = $('#tabla');	
			
			//TOOLTIP EN COLUMNAS TRUNCADAS
			$('#tabla').on('mouseenter', ".verTooltip", function () {
				var $this = $(this);
				$this.attr('title', $this.text());
			});
			
			//CSS PARA TRUNCAR COLUMNAS MUY LARGAS
			function truncarColumna(value,row,index, field){
			  return {
				classes: 'verTooltip',
				css: {"max-width": "110px" , "white-space": "pre", "overflow": "hidden", "text-overflow": "ellipsis"}
			  };
			}  
				
			function icono(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)"',
					'class="btn btn-primary btn-xs ver" title="Ver detalles de Facturación">',
					'<span class="glyphicon glyphicon-search aria-hidden="true"></span></button>'
				].join('');
			}
			
			window.verFila = {
				'click .ver': function (e, value, row, index) {
					mostrarModal(row);
				}
			};
			
			$table.bootstrapTable({
				data: datos,
				height: $(window).height()-172,
				pageSize: Math.floor(($(window).height()-172)/28)-5,
				columns: [
					[
						{	field: 'state', align: 'left', valign: 'middle', title: 'X', checkbox: true, class: 'hidden' },
						{	field: 'id_factura', title: 'ID', align: 'center', valign: 'middle', sortable: true, visible: false}, 
						{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true, width:'7%' },
						{	field: 'numero', align: 'left', valign: 'middle', title: 'Nº Factura', sortable: true, width:'7%'},
						{	field: 'fecha', align: 'center', valign: 'middle', title: 'Fecha', sortable: true, width:'13%'},
						{	field: 'ruc', align: 'left', valign: 'middle', title: 'RUC', sortable: true, width:'7%'},
						{	field: 'razon_social', align: 'left', valign: 'middle', title: 'Razón Social', sortable: true, width:'18%', cellStyle: truncarColumna},
						//{	field: 'condicion_venta', align: 'center', valign: 'middle', title: 'Condición', sortable: true},
						{	field: 'tipo_venta', align: 'right', valign: 'middle', title: 'Tipo', sortable: true, width:'7%' },
						{	field: 'total_ventas', align: 'right', valign: 'middle', title: 'Precio Venta', sortable: true, footerFormatter: sumatoria, formatter: moneda, width:'7%'},
						{	field: 'total_costo', align: 'right', valign: 'middle', title: 'Costo', sortable: true, footerFormatter: sumatoria, formatter: moneda, width:'7%'	},
						{	field: 'descuento', align: 'right', valign: 'middle', title: 'Des/NC', sortable: true, footerFormatter: sumatoria, formatter: moneda, width:'7%'	},
						{	field: 'comision_tarj', align: 'right', valign: 'middle', title: 'Comisión Tarj.', sortable: true, footerFormatter: sumatoria, formatter: moneda, width:'7%'	},
						{	field: 'ganancia', align: 'right', valign: 'middle', title: 'Ganancias', sortable: true, footerFormatter: sumatoria, formatter: moneda, width:'7%'	},
						{	field: 'usuario', align: 'center', valign: 'middle', title: 'Usuario', sortable: true, width:'5%'},
						{	field: 'ver', align: 'center', valign: 'middle', title: 'Ver', sortable: false, events: verFila,  formatter: icono, width:'1%'	}
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
				//return '<div> Gs. ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")+'</div>'; 
				return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
			}
			
			$('#tabla_detalles').bootstrapTable({
				columns: [
					[
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: false	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Producto', sortable: true, footerFormatter: totales	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cant.', sortable: true, footerFormatter: sumatoria, width:'2%'	},
						{	field: 'costo', align: 'right', valign: 'middle', title: 'Costo', sortable: true, footerFormatter: sumatoria, width:'6%'	},
						{	field: 'precio_venta', align: 'right', valign: 'middle', title: 'Precio Vta.', sortable: true, footerFormatter: sumatoria, width:'2%'	},
						{	field: 'total_costo', align: 'right', valign: 'middle', title: 'Tot. Costo', sortable: true, footerFormatter: sumatoria, width:'3%'	},
						{	field: 'total_venta', align: 'right', valign: 'middle', title: 'T. Precio Vta.', sortable: true, footerFormatter: sumatoria, width:'3%'	},
					]
				]
			});
			
			$('#tabla_pagos').bootstrapTable({
				columns: [
					[
						{	field: 'id_pago', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: false	}, 
						{	field: 'metodo_pago', align: 'left', valign: 'middle', title: 'Método', sortable: true	},
						{	field: 'monto', align: 'right', valign: 'middle', title: 'Monto', sortable: true, footerFormatter: sumatoria, width:'10%'	},
						{	field: 'comision_tarj', align: 'right', valign: 'middle', title: 'Comisión TJ', sortable: true, footerFormatter: sumatoria, width:'10%' },
						{	field: 'fecha', align: 'center', valign: 'middle', title: 'Fecha', sortable: true, width:'35%'	}
					]
				]
			});
			
			function totales() {
				return '<b style="font-size:16px">Totales:</b>';
			}
			
			function sumatoria(data) {
				field = this.field;	
				var total = 0;
					$.each(data, function (i, row) {
						if (row['metodo_pago'] != 'Descuento'){
							total += quitaSeparadorMiles(row[field]);
						}
				});
				if (field=="cantidad"){
					var moneda="";
				}else{
					var moneda="Gs. ";
				}
				return '<b>'+moneda+separadorMiles(total)+'</b>';
			}
			
			

			function mostrarModal(row){
				$('.modal-ver').modal('show');
				$("#mensaje").html("");
				$("#titulo_detalles").html("Detalles de venta - Comprobante Nº <label>"+row.numero+"</label>");
				$('#tabla_detalles').bootstrapTable("refresh", {url: 'inc/reporte-ventas-data.php?q=ver_detalles&id='+row.id_factura});
				$('#tabla_pagos').bootstrapTable("refresh", {url: 'inc/reporte-ventas-data.php?q=ver_pagos&id='+row.id_factura});
			}

			$(".modal").draggable({
				handle: ".modal-header"
			});
						




			
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
			$("#desde2").val(picker.startDate.format('YYYY-MM-DD'));	
			$("#hasta2").val(picker.endDate.format('YYYY-MM-DD'));
			
			RecargarVentas(fechaIni,fechaFin);
		});
		//FIN CALENDARIO
		
		RecargarVentas(fechaIni,fechaFin);
			
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
			
			<div id="mensaje" style="margin-bottom: 10px;"></div>
				<div id="toolbar">
					<div class="form-inline" role="form">
						<input type="hidden" value="" id="desde2">
						<input type="hidden" value="" id="hasta2">
					
		                <div class="form-group">
								<div id="reportrange" class="btn btn-default form-control" style="background: #fff; cursor: pointer; padding: 10px 10px; border: 1px solid #ccc; width: 100%">
									<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
									<span></span> <b class="caret"></b>
								</div>
							</div>
							
							<?php
							//solo el superadmin puede ver los cierres de todas las sucursales, despliega select
							if ($id_rol==1){
							?>
							<div class="form-group">
								<?php
								$q="select * from sucursales where estado='1' order by id_sucursal ASC";
								$db = DataBase::conectar();
								$r=mysqli_query($db,$q);
								echo "&nbsp;&nbsp;&nbsp;&nbsp;<select class='form-control input-sm' name='id_sucursal' id='id_sucursal' onchange=RecargarVentas();>
								";
								while ($row=mysqli_fetch_array($r)){
								$id_sucursal=$row['id_sucursal'];
								$sucursal=$row['sucursal'];
								
								if ($id_sucursal_post==$id_sucursal){
								$selcted="selected";	
								}else{
								$selcted="";	
								}
								
								echo "
									<option value='$id_sucursal' $selcted>$sucursal</option>
								";	
								}
								echo "
									<option value='todas' $selcted>TODAS</option>";
								echo "</select>";
								?>
							</div>
							<?php
							}
							?>
							
			      </div>    
			    </div>
				<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
				data-pagination="false" data-page-list="[15, 50, 100]" data-classes="table table-hover table-condensed" data-show-footer='true' data-single-select="true" data-click-to-select="true"></table>
				
				
				<div class="modal modal-ver" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
					<div class="modal-dialog modal modal-lg-2">
						<div class="modal-content">
							<div class="modal-header">
								<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
								<h4 class="modal-title" id="titulo_detalles"><a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
							</div>
							<div class="modal-body">
								<div class="row">
									<div class="col-md-7">
										<h4>Productos</h4>
										<table id="tabla_detalles" data-show-export="false" data-search="false" data-show-refresh="false" data-show-toggle="false" data-show-columns="false" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true" data-show-footer="true"></table>
									</div>
								
									<div class="col-md-5">
										<h4>Pagos</h4>
										<table id="tabla_pagos" data-show-export="false" data-search="false" data-show-refresh="false" data-show-toggle="false" data-show-columns="false" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true" data-show-footer="true"></table>
									</div>
								</div>
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