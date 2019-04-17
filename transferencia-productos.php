<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
	$id_rol = datosUsuario($id_usuario)->rol;
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
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
    
	<!-- Jquery-UI -->
	<script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css" />
	<link rel="stylesheet" href="jquery-ui/jquery-ui.theme.min.css" />
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	<script src="bootstrap-table/extensions/editable/bootstrap-table-editable.js"></script>
    <script src="bootstrap-table/extensions/editable/bootstrap-editable.js"></script>
	<link rel="stylesheet" href="bootstrap-table/extensions/editable/css/bootstrap-editable.css">
	
	<!-- Typehead -->
	<script type="text/javascript" src="js/typeahead.bundle.js"></script>
	<script type="text/javascript" src="js/handlebars.js"></script>
	<link href="css/typeahead.css" rel="stylesheet">
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">

    <script type="text/javascript">
		
		$(document).ready(function () {
			
			var $table = $('#tabla');
			noSubmitForm('#form_agregar');
		
			//Funcion para que cuando se haga Enter en el input, haga click en el boton agregar_producto
			enterClick('cantidad', 'agregar_producto');

			$('#sucursal_origen').change(function () {
				
				if (!$('#sucursal_origen').val()){
					$("#mensaje").html(alertDismissJS("Favor seleccione la Sucursal de donde se comprarán los productos","error"));
					$("#buscar_producto").prop("disabled", true);
					$("#cantidad").prop("disabled", true);
					$("#buscar_codigo").prop("disabled", true);
				}else{
					$('#buscar_producto, #buscar_codigo').val("");
					$('#buscar_producto').typeahead('destroy');
					$('#buscar_producto').typeahead('val', '');
					$("#mensaje").html("");
					$table.bootstrapTable('removeAll');
					$("#buscar_producto").prop("disabled", false);
					$("#cantidad").prop("disabled", false);
					$("#buscar_codigo").prop("disabled", false);

					////// Búsqueda de productos con Typeahead
					var datosBusqueda = new Bloodhound({
					  datumTokenizer: Bloodhound.tokenizers.whitespace('producto'),
					  queryTokenizer: Bloodhound.tokenizers.whitespace,
					 remote: {
						url: 'inc/transferencia-productos-data.php?q=buscar&filtro=%QUERY&sucursal='+$('#sucursal_origen').val(),
						wildcard: '%QUERY'
					  }
					});

					$('#buscar_producto').typeahead(null, {
					  hint: true,
					  highlight: true,
					  name: 'producto',
					  limit:8,
					  name: 'descripcion',
					  source: datosBusqueda,
					  display: 'producto',
					  value: 'id_producto',
					  templates: {
						empty: [
						  '<div class="empty-message">',
							'Sin resultados en la sucursal seleccionada',
						  '</div>'
						].join('\n'),
						suggestion: Handlebars.compile("<div><img class='productoImg' src='archivos/_thumbs/{{foto}}' height=32><strong>{{producto}}</strong></div>")
					  }
					}).on('typeahead:asyncrequest', function() {
						$('#spinner').show();
					})
					.on('typeahead:asynccancel typeahead:asyncreceive', function() {
						$('#spinner').hide();
					});
				}
			});
			
			$('#sucursal_destino').change(function () {
				$("#mensaje").html("");
			});
			
			//Cuando se selecciona un item de la lista
			$('#buscar_producto').bind('typeahead:select', function(ev, datos) {
				$('#hidden_id_producto').val(datos.id_producto);
				$('#stock').val(datos.stock);
			    $('#costo').val(separadorMiles(datos.costo));
				$("#mensaje").html("");
				//Se espera 100 milisegundos para que el foco vaya a cantidad, 
				//Sin esto la funcion enterClick('cantidad') se dispara
				setTimeout(function () {
					$('#cantidad').focus();
					$('#cantidad').select();
				}, 100);
			});
			///////// FIN busqueda productos
			
			$("#agregar_producto").click(function () {
				var cantidad = parseInt(quitaSeparadorMiles($('#cantidad').val()));
				var stock =  parseInt(quitaSeparadorMiles($('#stock').val()));
				var id_producto = $('#hidden_id_producto').val();
				if (!id_producto){
					$("#mensaje").html(alertDismissJS('Favor seleccione un producto de la lista', 'error'));
				}else if (stock <= 0){
					$("#mensaje").html(alertDismissJS('Producto sin stock.', 'error'));
				}else if (cantidad <= 0){
					$("#mensaje").html(alertDismissJS('Cantidad del producto debe ser mayor a cero', 'error'));
					$('#cantidad').focus();
					$('#cantidad').select();
				}else if (cantidad > stock){
					$("#mensaje").html(alertDismissJS('Cantidad a transferir no puede ser mayor al stock disponible.', 'error'));
					$('#cantidad').focus();
					$('#cantidad').select();
				}else if (!$("#sucursal_destino").val()){
					$('#mensaje').html(alertDismissJS("Elija la Sucursal que recibirá los productos", "error"));
				}else if ($("#sucursal_origen").val()==$("#sucursal_destino").val()){
					$('#mensaje').html(alertDismissJS("Las sucursales de origen y destino no pueden ser el mismo", "error"));
				}else if ($('#costo').val()=="0"){
					$('#mensaje').html(alertDismissJS("Producto sin costo. Favor verifique.", "error"));
				}else{
					/*var check_status = 0;
					
					//VERIFICAMOS QUE EL TOTAL NO SOBREPASE EL STOCK
					var datos = $table.bootstrapTable('getData');
					var total_cantidad = 0;
					for(var k in datos) {
						 if (id_producto == datos[k].id_producto){
							total_cantidad += datos[k].cantidad;
						 }
						 
						 if (total_cantidad >= stock){
							 check_status = 1;
						 }
					}*/
					
					var check_status = 0;
					var datos = $table.bootstrapTable('getData');
					for(var k in datos) {
						if (id_producto == datos[k].id_producto){
							 var cant_actual = datos[k].cantidad;
							 var total_cant = parseInt(cant_actual)+parseInt(cantidad);
							 if (total_cant > stock){
								 $("#mensaje").html(alertDismissJS('Cantidad supera el stock disponible ('+stock+'). Favor verifique.', 'error'));
								 check_status=1;
							 }else{
								 check_status=0;
								 cantidad = total_cant;
								 $table.bootstrapTable('removeByUniqueId', datos[k].id_transferencia);
							 }
						}
					}
					
					if (check_status == 0){					
						$("#sucursal_origen option").not(":selected").attr("disabled", "disabled");
						$("#sucursal_origen").attr('readonly', 'readonly');
						$("#sucursal_destino option").not(":selected").attr("disabled", "disabled");
						$("#sucursal_destino").attr('readonly', 'readonly');
						
						//var cantProd = $table.bootstrapTable('getData').length + 1;
						$table.bootstrapTable('scrollTo', 'bottom');
						var id_timestamp = new Date().getTime();//ID PARA PODER ELIMINAR LA FILA EN CASO DE NECESIDAD
						$table.bootstrapTable('insertRow', {
							index: 0,
							row: {
								id_transferencia: id_timestamp,
								id_producto: id_producto,
								producto: $('#buscar_producto').val(),
								cantidad: cantidad,
								costo: $('#costo').val(),
								costo_total: separadorMiles(parseInt(quitaSeparadorMiles($('#costo').val())) * cantidad)
							}
						});
						 setTimeout(function () {
							$('input').val("");
							$("#mensaje").html("");
							$('#buscar_producto').typeahead('val', '');
							$('#buscar_codigo').focus();
							$('#buscar_producto').typeahead('close');
							$('#cantidad').val(1)
							$("#transferir").prop("disabled", false);
						}, 50);
					}
				}
			});
			
			function icono(value, row, index) {
				return [
					'<a class="remove" href="javascript:void(0)" title="Eliminar">',
					'<i class="glyphicon glyphicon-trash"></i>',
					'</a>'
				].join('');
			}
			
			window.borrarItem = {
				'click .remove': function (e, value, row, index) {
					var confDel = confirm("¿Borrar de la lista "+row.producto+"?");
					if (confDel){
						$table.bootstrapTable('removeByUniqueId', row.id_transferencia);
					}
				}
			};
			
			$table.bootstrapTable({
				data: [],
				uniqueId: 'id_transferencia',
				columns: [
					[
						{	field: 'id_transferencia', visible: false	}, 
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Nombre / Descripción', sortable: true	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cantidad', sortable: true, editable: false	},
						{	field: 'costo', align: 'right', valign: 'middle', title: 'Costo Unit.', sortable: true	},
						{	field: 'costo_total', align: 'right', valign: 'middle', title: 'Costo Total', sortable: true, footerFormatter: sumatoria	},
						{	field: 'borrar', align: 'center', valign: 'middle', title: 'Borrar', sortable: false, events: borrarItem,  formatter: icono	}
					]
				]
			});
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('resetView', { });
			});		
			
			function sumatoria(data) {
				field = this.field;	
				var total = 0;
					$.each(data, function (i, row) {
					total += quitaSeparadorMiles(row[field]);
				});
				return '<b style="font-size:18px">Gs. ' + separadorMiles(total)+'</b>';
			}
			
			//Acciones al editar la tabla
			/*$table.editable.defaults.onblur = 'submit';
			$table.editable.defaults.tpl = '<input type="text" onkeyup="return separadorMilesOnKey(event,this)" style="width:100%;padding:2px 5px 2px 5px">';
			
			$table.on('editable-save.bs.table', function (e, field, row, old, $el) {
				if (!row[field] && field != "cantidad"){
					row[field]=0;
				}else{
					var tmp=quitaSeparadorMiles(row[field].replace(/^0+(?=\d)/, '')); //Borramos ceros al inicio
					
					//Comprobamos si hay stock del producto
					row[field]=separadorMiles(tmp);
				} 
				if (field=="cantidad" && row[field]==0){
					row[field]=1;
				}
				var totalCosto = parseInt(quitaSeparadorMiles(row.cantidad))*parseInt(quitaSeparadorMiles(row.costo));
				var indice = $el.closest('tr').data('index');
				$table.bootstrapTable('updateCell', {index:indice, field:"costo_total", value: separadorMiles(totalCosto) });
				$table.bootstrapTable('resetView', {});
			});*/

			//Sucursales origen
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales', id: '<?php echo $id_sucursal; ?>'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					<?php
					if ($id_sucursal==1){
						echo "$.each(json, function(key, value) {
								$('#sucursal_origen').append('<option value=\"'+ value.id_sucursal + '\">' + value.sucursal +'</option>');
							});"; 
					}else{
						echo "$.each(json, function(key, value) {
								$('#sucursal_origen').html('<option></option><option value=\"'+ value.id_sucursal + '\">' + value.sucursal +'</option>');
							});";
					}
					
					?>
					
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			//Sucursales destino
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/transferencia-productos-data.php', type: 'POST', data: {q: 'sucursal_destino'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#sucursal_destino').append('<option value=\"'+ value.id_sucursal + '\">' + value.sucursal +'</option>');
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
											 
			
			$("#form_agregar").submit(function(event){
				$("#mensaje").html("");
				
				//Evita el submit default del php
				event.preventDefault();
				
				if (jQuery.isEmptyObject($table.bootstrapTable('getData'))){
					$('#mensaje').html(alertDismissJS("Debe agregar al menos un producto", "error"));
				}else{
					var r = confirm("¿Confirmar transferencia de productos de la sucursal: "+$('#sucursal_origen option:selected').text().toUpperCase()+" a la sucursal: "+$('#sucursal_destino option:selected').text().toUpperCase()+"?");
					if (r == true) {
						$.ajax({
							dataType: 'html',
							type: 'POST',
							url: 'inc/transferencia-productos-data.php',
							cache: false,
							data: {q: 'transferir', id_sucursal_ori: $('#sucursal_origen').val(), id_sucursal_des: $('#sucursal_destino').val(), descripcion: $('#descripcion').val(), datos: $table.bootstrapTable('getData') },	
							beforeSend: function(){
								$("#mensaje").html("<img src='images/progress_bar.gif'>");
							},
							success: function (datos, status, xhr) {
								$("#mensaje").html(datos);
								$table.bootstrapTable('scrollTo', 'top');
								var n = datos.toLowerCase().indexOf("error");
								if (n == -1) {
									setTimeout(function () {
										//location.reload();
										/*var param = { 'transferencia' : data, 'imprimir' : 'no'};
										OpenWindowWithPost("comprobante-transferencia.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=898,height=600", "ImprimirSolicitud", param);*/
									}, 1500);
									
									var param = { 'id_compra' : datos, 'imprimir' : 'si', 'recargar': 'si' };
									OpenWindowWithPost("imprimir-remision.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=898,height=600", "ImprimirSolicitud", param);
								}
							},
							error: function (xhr) {
								$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
							}
						});
					}
				}
			});
			
			$('#buscar_codigo').keyup(function (e) {
				if (e.keyCode === 13) {
				   $.ajax({
						dataType: 'json', async: false, cache: false, url: 'inc/transferencia-productos-data.php', type: 'POST', data: {q: 'buscar_por_codigo', codigo:$('#buscar_codigo').val() },
						beforeSend: function(){
							$("#mensaje").html("<img src='images/progress_bar.gif'>");
						},
						success: function (json){
							if (jQuery.isEmptyObject(json)){
								$("#mensaje").html(alertDismissJS('Código del producto no encontrado.', 'error'));
								$("#buscar_codigo").select();
								$("#buscar_codigo").focus();
							}else{
								$("#mensaje").html("");
								$("#buscar_producto").prop("disabled", false);
								$("#cantidad").prop("disabled", false);
								$('#hidden_id_producto').val(json.id_producto);
								$('#stock').val(json.stock);
								$('#costo').val(separadorMiles(json.costo));
								$('#buscar_producto').typeahead('val', json.producto);
								//Se espera 100 milisegundos para que el foco vaya a cantidad, 
								//Sin esto la funcion enterClick('cantidad') se dispara
								setTimeout(function () {
									$("#agregar_producto").click();
									$('#buscar_codigo').focus();
									$('#buscar_codigo').select();
								}, 100);
							}
						},
						error: function (xhr) {
							$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
						}
					});
				}
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
				<div class="col-md-12 col-sm-12 col-xs-12">
					<form class="form" id="form_agregar" method="post" enctype="multipart/form-data" action="inc/transferencia-productos-data.php?q=transferir">
						<input type="hidden" id="hidden_id_producto">
						<div class="row">
							<div class="col-md-3 col-sm-4 col-xs-6">
								<div class="form-group">
									<label for="sucursal_origen">Comprar de Sucursal (origen):</label>
									<select id="sucursal_origen" name="sucursal_origen" class="form-control input-sm" style="padding: 0">
									<option></option>
									</select>
								</div>
							</div>
							<div class="col-md-3 col-sm-4 col-xs-6">
								<div class="form-group">
									<label for="sucursal_destino">Vender a Sucursal (destino):</label>
									<select id="sucursal_destino" name="sucursal_destino" class="form-control input-sm" style="padding: 0">
									<option></option>
									</select>
								</div>
							</div>
							<div class="col-md-5 col-sm-6 col-xs-12">
								<label>Descripción / Observaciones</label>
								<input type="text" class="form-control input-sm" id="descripcion" title="Escriba una descripción a esta transferencia para organizar y localizar desde el administrador" autocomplete="off">
							</div>
						</div>
						<div class="row">
							<div class="col-md-2 col-sm-2 col-xs-6">
								<div class="form-group">
									<label for="buscar_codigo">Código</label>
									<input type="text" class="form-control input-sm" id="buscar_codigo" title="Buscar por código del producto" onkeypress="return soloNumeros(event)" autocomplete="off" style="padding:2px;text-align:center" disabled>
								</div>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-4">
								<div class="form-group">
									<label for="buscar_producto">Producto<span id="spinner" style="display:none;margin-left:10px"><img src='images/progress_bar.gif'></span></label>
									<input class="typeahead form-control input-sm" id="buscar_producto" type="text" onclick="$(this).select();" placeholder="Buscar Mercadería" autocomplete="off" disabled>
								</div>
							</div>
							<div class="col-md-1 col-sm-1 col-xs-4">
								<div class="form-group">
									<label for="stock">Stock</label>
									<input class="form-control input-sm" id="stock" name="stock" type="text" autocomplete="off" disabled>
								</div>
							</div>
							<div class="col-md-1 col-sm-2 col-xs-4">
								<div class="form-group">
									<label for="cantidad">Cantidad</label>
									<input class="form-control input-sm" id="cantidad" name="cantidad" type="text" autocomplete="off" onkeypress="return soloNumeros(event)" disabled value="1">
								</div>
							</div>
							<div class="col-md-2 col-sm-2 col-xs-4">
								<label for="costo">Costo</label>
								<input class="form-control input-sm" id="costo" name="costo" type="text" autocomplete="off" readonly>
							</div>

							<div class="col-md-2 col-sm-3 col-xs-4">
								<div class="form-group">
									<p style="line-height:12px">&nbsp;</p>
									<button type="button" class="btn btn-success btn-md" id="agregar_producto">Agregar Producto</button>
								</div>
							</div>
						</div>
						<br>
						<div id="mensaje"></div>
						<br>
						<table id="tabla" data-pagination="false" data-classes="table table-hover table-condensed" data-show-footer='true'></table>
						<br>
						<div class="col-md-2 col-md-offset-10">
							<div class="form-group" style="float:right">
								<button type="submit" class="btn btn-primary btn-md submit_btn" id="transferir" disabled>Transferir</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	

	
	
	
    <script src="js/menuHover.js"></script>
  </body>
</html>