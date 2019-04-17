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
	<script src="bootstrap-table/locale/bootstrap-table-es-CL.min.js"></script>
	
	<link rel="stylesheet" href="bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.css">
	<script src="bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.js"></script>
	
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	<style type="text/css">
		
    </style>
	
    <script type="text/javascript">
	
		$(document).ready(function () {
			
			function iconoFila(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-xs editar"><span class="glyphicon glyphicon-remove aria-hidden="true"></span>&nbsp;&nbsp;Anular</button>'
				].join('');
			}
			
			window.anularFila = {
				'click .editar': function (e, value, row, index) {
					if (row.estado=="Anulado"){
						alert("Factura o Comprobante ya se encuentra Anulado");
					}else{
						var conf = confirm("¿Anular Factura N° "+row.numero+"?");
						if (conf){
							anularComprobante(row.id_factura,row.numero, row.id_sucursal);
						}
					}
				}
			};
			
			function iconoVer(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-xs editar"><span class="glyphicon glyphicon-search aria-hidden="true"></span>&nbsp;&nbsp;Ver</button>'
				].join('');
			}
			
			function iconoPagar(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-xs pagos"><span class="glyphicon glyphicon-money aria-hidden="true"></span>&nbsp;&nbsp;Pagos</button>'
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
			
			window.Pagar = {
				'click .pagos': function (e, value, row, index){
					id_factura=row.id_factura;
					saldo=row.saldo;
					$('.modal-imprimir').modal('show');
					$('#p_total_pagar').text('Gs. '+saldo);
					$('#id_factura').val(id_factura);
					$('#id_sucursal').val(row.id_sucursal);
				}
			};
			
			window.verPagos= {
				'click .pagos': function (e, value, row, index) {
				$('.modal-pagos').modal('show');
				$("#titulo_detalles").html("Detalles de venta - Comprobante Nº <label>"+row.numero+"</label>");
				$('#tabla_detalles').bootstrapTable("refresh", {url: 'inc/adm-facturas-data-cr.php?q=ver_detalles&id='+row.id_factura});
				$('#tabla_pagos').bootstrapTable("refresh", {url: 'inc/adm-facturas-data-cr.php?q=ver_pagos&id='+row.id_factura});
				}
			};
			
		function iconop(value, row, index) {
				return [
					'<button type="button" onclick="javascript:void(0)"',
					'class="btn btn-primary btn-xs pagos" title="Ver detalles de Facturación">',
					'<span class="glyphicon glyphicon-search aria-hidden="true"></span></button>'
				].join('');
			}
			
			$('#tabla').bootstrapTable({
				sortOrder: 'desc',
				columns: [
					[
						{	field: 'id_factura', title: 'ID', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'numero', title: 'N° Comp.', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'ruc', title: 'RUC/CI', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'razon_social', title: 'Razón Social', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'cantidad', title: 'Cant.', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'total_a_pagar', title: 'Total Gs.', align: 'right', valign: 'middle', sortable: true }, 
						{	field: 'saldo', title: 'Saldo', align: 'right', valign: 'middle', sortable: true }, 
						{	field: 'id_sucursal', visible: false }, 
						{	field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true }, 
						<?php if ($_SESSION['id_rol']==1){ 
							//echo "{	field: 'anular', align: 'center', valign: 'middle', title: 'Anular', sortable: false, events: anularFila,  formatter: iconoFila	},";
							} ?>
						{	field: 'ver', align: 'center', valign: 'middle', title: 'Ver', sortable: false, events: verFila,  formatter: iconoVer	},
						{	field: 'pagar', align: 'center', valign: 'middle', title: 'Pagos', sortable: false, events: Pagar,  formatter: iconoPagar	},
						{	field: 'pagos', align: 'center', valign: 'middle', title: 'Ver Pagos', sortable: false, events: verPagos,  formatter: iconop	}
					]
				]
			});
			
						
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
					total += quitaSeparadorMiles(row[field]);
				});
				if (field=="cantidad"){
					var moneda="";
				}else{
					var moneda="Gs. ";
				}
				return '<b>'+moneda+separadorMiles(total)+'</b>';
			}
			
			
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$('#tabla').bootstrapTable('refreshOptions', { 
					height: $(window).height()-180,
				});
			});
			//Funcion para limpiar el modal al cerrar o al finalizar correctamente la carga
			function limpiarModal(){
				$("#mensaje").html("");
			}
		
        });
        
        	function calculoCambio(){
			var monto_final_tmp = $('.p_total_pagar').html().split("Gs. ");
			var total_pagar= quitaSeparadorMiles(monto_final_tmp[1]);
			var totalCambio = quitaSeparadorMiles($("#monto_entregado").val()) - total_pagar;
			if (!isNaN(totalCambio)) {
				$("#cambio").val(separadorMiles(totalCambio));
				if (totalCambio >= 0){
					$("#cambio").css("color","green");
				}else{
					$("#cambio").css("color","red");
				}
			}
		
			
		}
		

		function anularComprobante(id,nro,id_suc){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/adm-facturas-data-cr.php',
				cache: false,
				data: {q: 'anular', id: id, nro: nro, id_suc:id_suc },
				async: true,
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						$('#tabla').bootstrapTable('refresh', {url: 'inc/adm-facturas-data-cr.php?q=ver'});
					}
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
        }
        
        
        function Imprimir_pago(){
				$.ajax({
					dataType: 'json',
					async: true,
					type: 'POST',
					url: 'inc/adm-facturas-data-cr.php',
					cache: false,
					data: {q:'agregar_pago', metodo_pago:$("#metodo_pago").val(), id_factura:$("#id_factura").val(), monto:$("#monto_entregado").val(), id_sucursal:$("#id_sucursal").val() },	
					beforeSend: function(){
						$("#mensaje_pagar").html("<img src='images/progress_bar.gif'>");
					},
					success: function (data, status, xhr) {
						$('#tabla').bootstrapTable('refresh', {url: 'inc/adm-facturas-data-cr.php?q=ver'});
						id_pago=data.id_pago;
						$("#mensaje_pagar").html(data.mensaje);
						var param = { 'id': id_pago, 'imprimir' : 'si', 'recargar':'si' };
						OpenWindowWithPost("imprimir-recibo.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "imprimirFacturaMayorista", param);
						deshabilitar();
					},
					error: function (xhr) {
						$("#mensaje_pagar").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
		}
		
		function deshabilitar(){
			$("#btn_imprimir_ticket").attr("disabled",true);
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
		<div class="container-fluid">
			<div class="page-header">
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div id="mensaje"></div>
					
					<div id="toolbar">
					</div>
					<table id="tabla" data-url="inc/adm-facturas-data-cr.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true"  data-classes="table table-hover table-condensed" data-striped="true" data-side-pagination="server"></table>
				</div>
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
				
	<!-- MODAL IMPRIMIR  -->
	<div class="modal modal-imprimir" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog modal modal-sm2">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
				<h4 id="mySmallModalLabel" class="modal-title">Imprimir Comprobante<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
			</div>
			<div class="modal-body">
				
					<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12">
						<div class="form-group" id="mensaje_pagar">
						</div>
							<div class="form-group">
								<label for="tipo_editar">Total a Pagar</label>
								<p style="font-size:32px;line-height:40px" class="p_total_pagar" id="p_total_pagar">Gs. 0</p>
								<input type="hidden" name="id_factura" id="id_factura" value="">
								<input type="hidden" name="id_sucursal" id="id_sucursal" value="">
							</div>
							
							<div class="form-group">
								<label>Método de Pago:</label>
								<select id="metodo_pago" class="form-control input-md">
								  <optgroup label="Monedas en Efectivo">
									  <option value="Efectivo">Efectivo Gs.</option>
								  </optgroup>
								  
								  <optgroup label="Otros metodos de Pago">
									  <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
									  <option value="Tarjeta de Débito">Tarjeta de Débito</option>
									  <option value="Cheque">Cheque</option>
									  <option value="Giro">Giro</option>
								  </optgroup>
							  </select>
							</div>
							
							<div class="form-group">
								<label for="tipo_editar">Monto a Pagar</label>
								<input class="form-control input-md" type="text" id="monto_entregado" onkeyup="separadorMilesOnKey(event,this);calculoCambio()" autocomplete="off" style="font-weight:bold;font-size:20px">
							</div>
							<div class="form-group">
								<label for="tipo_editar">Saldo</label>
								<input class="form-control input-sm" type="text" id="cambio" readonly style="font-weight:bold;font-size:16px">
							</div>
						</div>
					</div>
				
				<div id="mensaje_imprimir"></div>
			</div>

			<div class="modal-footer">
				
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12">
						<button type="button" class="btn btn-primary btn-sm" id="btn_imprimir_ticket" onclick="Imprimir_pago();">Imprimir Recibo</button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
	
	<div class="modal modal-pagos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
	
	<?php echo piePagina(); ?>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>
