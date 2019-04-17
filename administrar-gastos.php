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
	

	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	
    <script type="text/javascript">
    
    		function Abrir_ventana(pagina){
			var opciones="menubar=no, scrollbars=no, resizable=yes, width=760, height=530";
			ventana_secundaria=window.open(pagina,"",opciones);
			}
			
		
			function RecargarGastos(desde,hasta){
		var datos;
		//recarga la tabla
		$.ajax({
		dataType: 'json', async: false, cache: false, url: 'inc/administrar-gastos-data.php', type: 'POST', data: {q: 'ver', desde: desde, hasta:hasta},
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
					//RecargarGastos(fechaIni,fechaFin);
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalAdd').html(alertDismissJS(jqXHR.responseText, "error"));
				});
				
			});
			
			var datos;
				
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/administrar-gastos-data.php', type: 'POST', data: {q: 'ver'},
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
						{	field: 'id_gasto', title: 'ID', align: 'center', valign: 'middle', sortable: true, }, 
						{	field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario', sortable: true,visible:false	},
						{	field: 'fecha', align: 'left', valign: 'middle', title: 'Fecha', sortable: true	}, 
						{	field: 'hora', align: 'left', valign: 'middle', title: 'Hora', sortable: true	},
						{	field: 'descripcion', align: 'left', valign: 'middle', title: 'Descripcion', sortable: true	},
						{	field: 'monto', align: 'right', valign: 'middle', title: 'Monto', sortable: true, footerFormatter: sumatoria, formatter: moneda	},
						{	field: 'id_gasto', align: 'center', valign: 'center', title: 'Anular', sortable: true, formatter: anular	}
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
			
			function anular(value){
			return '<a href=javascript:Anular_gasto("'+value+'")><span style="color:red" class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>'; 
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
		
		//ANULAR
			function Anular_gasto(gasto){
			var statusConfirm = confirm('Estas seguro que desea anular el gasto  '+gasto+' ?'); 

			if (statusConfirm == true) 
			{
			$('#mensaje').html('<img src=images/progress_bar.gif>');
			$('#mensaje').load('inc/administrar-gastos-data.php?q=anular&id_gasto='+gasto);			
			location.reload();			
			//setTimeout(function(){RecargarGastos();}, 1000);	
			}
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
			
			<div id="mensaje" style="margin-bottom: 10px;"></div>
				<div id="toolbar">
					<div class="form-inline" role="form">
		                <div class="form-group">
							<button type="button" class="btn btn-primary form-control" id="agregar" data-toggle="modal" data-target=".modal-agregar">
							<i class="glyphicon glyphicon-plus"></i>
							Agregar Gasto</button>   
		                </div>
		                
		                <div class="form-group">
									<div id="reportrange" class="btn btn-default form-control" style="background: #fff; cursor: pointer; padding: 10px 10px; border: 1px solid #ccc; width: 100%">
									<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
									<span></span> <b class="caret"></b>
								</div>
			            </div>    
			        </div>
			     </div>		
					
					<table id="tabla" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left"
					data-pagination="true" data-page-list="[15, 50, 100]" data-classes="table table-hover table-condensed" data-show-footer='true'></table>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
	
	<!-- MODA AGREGAR -->
	<div class="modal modal-agregar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Agregar Gasto<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
				</div>
				<div class="modal-body">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/administrar-gastos-data.php?q=cargar">
						<div class="container-fluid">
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="form-group">
												<label for="codigo_carga">Fecha</label>
												<input type="date" class="form-control input-sm" name="fecha_carga" id="fecha_carga" required placeholder="" autocomplete="off" value="<?php echo date('Y-m-d');?>">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="form-group">
												<label for="codigo_carga">Sucursal #<?php echo $id_sucursal=$_SESSION['id_sucursal'];?></label>
										<?php
									$db = DataBase::conectar();
									$q="select disponibilidades from sucursales where id_sucursal=$id_sucursal ";
									$r=mysqli_query($db,$q);
									$row=mysqli_fetch_array($r);
									$disponibilidades=$row['disponibilidades'];
									
									if (empty($disponibilidades)){
									$disponibilidades=0;	
									}
									
									$disponibilidades_punto=poner_puntos($disponibilidades);
									
									if ($disponibilidades>0){
									$sty="style='color:green;'";
									$disabled="";	
									}else{
									$sty="style='color:red;'";
									$disabled="disabled";
									}
									
									echo "<br><div id='div_disponible'><b $sty>Gs. $disponibilidades_punto</b></div>";
									?>
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="form-group">
												<label for="codigo_carga">Monto</label>
												<input class="form-control input-sm" type="text" name="monto_carga" id="monto_carga" autocomplete="off" onkeyup="poner_puntos_input(this);">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<label for="rubro_carga">Descripcion</label>
												<input class="form-control input-sm" type="text" name="descripcion_carga" id="descripcion_carga" autocomplete="off">
											</div>
										</div>
									</div>
									
									<?php
									if ($_SESSION['id_rol']==1){
									?>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="form-group">
												<label for="rubro_carga">Sucursal</label>
												<?php
												SelectSucursales(1);
												?>
											</div>
										</div>
									</div>
									<?php
									}else{
										echo $_SESSION['id_rol'];
									}
									?>
									
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<span id="msjModalAdd"></span>
											<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right" <?php echo $disabled;?>>Guardar</button>
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
	
	<script>
	function poner_puntos_input(input)
	{
	var num = input.value.replace(/\./g,'');
	if(!isNaN(num)){
	num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
	num = num.split('').reverse().join('').replace(/^[\.]/,'');
	input.value = num;
	}
	else{ alert('Solo se permiten numeros');
	input.value = input.value.replace(/[^\d\.]*/g,'');
	}
	}
	
	function Dispinibilidad_sucursal(){
	id_sucursal=$("#id_sucursal").val();
	$("#div_disponible").html('<center><img src="images/progress_bar.gif></center>"');
	$("#div_disponible").load('inc/administrar-gastos-data.php?q=traer_disponible&id_sucursal='+id_sucursal);
	}
	</script>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>