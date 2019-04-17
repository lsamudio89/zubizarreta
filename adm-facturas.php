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
			
			$('#tabla').bootstrapTable({
				sortOrder: 'desc',
				columns: [
					[
						{	field: 'id_factura', title: 'ID', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'numero', title: 'N° Comp.', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'fecha_anulada', title: 'Fecha Anulada', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'usuario_anulo', title: 'Usuario Anulo', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'ruc', title: 'RUC/CI', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'razon_social', title: 'Razón Social', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'tipo_venta', title: 'Tipo Cliente', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'cantidad', title: 'Cant.', align: 'center', valign: 'middle', sortable: true }, 
						{	field: 'total_a_pagar', title: 'Total Gs.', align: 'right', valign: 'middle', sortable: true }, 
						{	field: 'id_sucursal', visible: false }, 
						{	field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true }, 
						{	field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true }, 
						<?php //if ($_SESSION['id_rol']==1){ 
						//echo "{	field: 'anular', align: 'center', valign: 'middle', title: 'Anular', sortable: false, events: anularFila,  formatter: iconoFila	},";
						 //} ?>
						{	field: 'ver', align: 'center', valign: 'middle', title: 'Ver', sortable: false, events: verFila,  formatter: iconoVer	}
					]
				]
			});
			
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
		

		function anularComprobante(id,nro,id_suc){
			$.ajax({
				dataType: 'html',
				type: 'POST',
				url: 'inc/adm-facturas-data.php',
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
						$('#tabla').bootstrapTable('refresh', {url: 'inc/adm-facturas-data.php?q=ver'});
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
		<div class="container-fluid">
			<div class="page-header">
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div id="mensaje"></div>
					
					<div id="toolbar">
					</div>
					<table id="tabla" data-url="inc/adm-facturas-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true"  data-classes="table table-hover table-condensed" data-striped="true" data-side-pagination="server"></table>
				</div>
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	
    <script src="js/menuHover.js"></script>
  </body>
</html>
