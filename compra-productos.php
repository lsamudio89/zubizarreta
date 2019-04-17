<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
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

	<style type="text/css">
		.modal-eliminar{z-index:999999}
    </style>
	 
	 
	
    <script type="text/javascript">
		
		
		$(document).ready(function () {
		var $table = $('#tabla');	
			noSubmitForm('form');
		
			//Funcion para que cuando se haga Enter en el input, haga click en el boton agregar_producto
			enterClick('costo', 'agregar_producto');
			
			////// BÚSQUEDA DE PRODUCTOS CON TYPEAHEAD ///////
			var datosBusqueda = new Bloodhound({
			  datumTokenizer: Bloodhound.tokenizers.whitespace('producto'),
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			 remote: {
				url: 'inc/compra-productos-data.php?q=buscar&filtro=%QUERY',
				wildcard: '%QUERY'
			  }
			});
			
			$('#buscar_producto').typeahead(null, {
			  hint: true,
			  highlight: true,
			  name: 'producto',
			  limit:100,
			  source: datosBusqueda,
			  display: 'producto',
			  value: 'id_producto',
			  templates: {
				empty: [
				  '<div class="empty-message">',
					'Sin resultados',
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
			
			//Cuando se selecciona un item de la lista
			$('#buscar_producto').bind('typeahead:select', function(ev, datos) {
			    $('#hidden_id_producto').val(datos.id_producto);
				 // $('#precio_vta_min').val(datos.precio_vta_min);
				 // $('#precio_vta_may').val(datos.precio_vta_may);
				 setTimeout(function () {
				  $('#cantidad').focus();
				}, 100);
			  $("#mensaje").html("");
			});
			///// FIN BUSQUEDA PRODUCTOS ///////
			
			
			
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
						$table.bootstrapTable('removeByUniqueId', row.id_compra_producto);
					}
				}
			};
		
			$table.bootstrapTable({
				data: [],
				uniqueId: 'id_compra_producto',
				columns: [
					[
						{	field: 'id_compra_producto', visible: false	}, 
						{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Nombre / Descripción', sortable: true	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cantidad', sortable: true, editable: true	},
						{	field: 'costo', align: 'right', valign: 'middle', title: 'Costo Unit.', sortable: true, editable: true	},
						{	field: 'costo_total', align: 'right', valign: 'middle', title: 'Costo Total', sortable: true	},
						{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: borrarItem,  formatter: icono	}
					]
				]
			});
			
			
			//Acciones al editar la tabla
			$table.editable.defaults.onblur = 'submit';
			$table.editable.defaults.tpl = '<input type="text" onkeyup="return separadorMilesOnKey(event,this)" style="width:100%;padding:2px 5px 2px 5px">';
			
			$table.on('editable-save.bs.table', function (e, field, row, old, $el) {
				if (!row[field] && field != "cantidad"){
					row[field]=0;
				}else{
					var tmp=quitaSeparadorMiles(row[field].replace(/^0+(?=\d)/, '')); //Borramos ceros al inicio
					row[field]=separadorMiles(tmp);
				} 
				if (field=="cantidad" && row[field]==0){
					row[field]=1;
				}
				var totalCosto = parseInt(quitaSeparadorMiles(row.cantidad))*parseInt(quitaSeparadorMiles(row.costo));
				var indice = $el.closest('tr').data('index');
				$table.bootstrapTable('updateCell', {index:indice, field:"costo_total", value: separadorMiles(totalCosto) });
				$table.bootstrapTable('resetView', {});
			});
			
			//Altura de tabla automatica
			$(window).resize(function () {
				$table.bootstrapTable('resetView', { });
			});		
			
			function totalTextFormatter(data) {	
				return "<span style='font-size:16px'><b>TOTALES:</b></span>"; 
			}
			
			function cantidadFooter(data) {
				var total=0;
			
				field = this.field;
				$.each(data, function (i, row) {
					total += +parseInt(quitaSeparadorMiles(row[field]));
				});
				return "<span style='font-size:16px'><b>" + separadorMiles(total)+"</b></span>";
			}
			
			function guaraniesFooter(data) {
				
				var total=0, totalMax=0, totalMin=0;
			
				field = this.field;
				$.each(data, function (i, row) {
					total += +parseInt((quitaSeparadorMiles(row[field])));
				});
				$('#'+field).val(separadorMiles(total));
				return "<span style='font-size:16px'><b>Gs. " + separadorMiles(total)+"</b></span>";
			}
			
			$("#agregar_producto").click(function () {
				if (!$('#hidden_id_producto').val()){
					$("#mensaje").html(alertDismissJS('Favor seleccione un producto de la lista antes de agregar', 'error'));
				}else if ($('#cantidad').val()==0) {
					$("#mensaje").html(alertDismissJS('Cantidad debe ser mayor a cero', 'error'));
				}else{
					//VERIFICAMOS QUE NO EXISTA PRODUCTO YA CARGADO
					var check_id_merca = $('#hidden_id_producto').val();
					var check_status = 0;
					var datos = $table.bootstrapTable('getData');
					for(var k in datos) {
						if (check_id_merca == datos[k].id_producto){
							 check_status = 1;
						 }
					}
					if (check_status == 1){
						$("#mensaje").html(alertDismissJS("Producto ya agregado a la lista. Favor verifique", "error"));
					}else{
						//Recalculamos
						var costoTotal = quitaSeparadorMiles($('#cantidad').val())*quitaSeparadorMiles($('#costo').val());
						
						$table.bootstrapTable('scrollTo', 'top');
						var id_compra_producto = new Date().getTime();//ID PARA PODER ELIMINAR LA FILA EN CASO DE NECESIDAD
						$table.bootstrapTable('insertRow', {
							index: 0,
							row: {
								id_compra_producto: id_compra_producto,
								id_producto: $('#hidden_id_producto').val(),
								producto: $('#buscar_producto').val(),
								cantidad: $('#cantidad').val(),
								costo: $('#costo').val(),
								costo_total: separadorMiles(costoTotal),
							}
						});
						setTimeout(function () {
							$table.bootstrapTable('scrollTo', 'bottom');
							//Luego de insertar vaciamos los inputs
							$("#mensaje").html("");
							$('#buscar_producto').typeahead('val', '');
							$('#buscar_producto').focus();
							$('#buscar_producto').typeahead('close');
							$('#hidden_id_producto').val(""),
							$('#cantidad').val(1);							
							$('.cerar').val(0);
						}, 300);
					}
				}
			});
			
			$(".calendario").datepicker();
			
			$(function($){
				 $.datepicker.regional['es'] = {
					  closeText: 'Cerrar',
					  prevText: '<Ant',
					  nextText: 'Sig>',
					  currentText: 'Hoy',
					  monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					  monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
					  dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
					  dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
					  dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
					  weekHeader: 'Sm',
					  dateFormat: 'dd/mm/yy',
					  firstDay: 1,
					  isRTL: false,
					  showMonthAfterYear: false,
					  yearSuffix: ''
				 };
				 $.datepicker.setDefaults($.datepicker.regional['es']);
			});
			
			//Sucursales
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#sucursal').append('<option value="'+value.id_sucursal+'">'+value.sucursal+'</option>');
					});
					//$('#sucursal').val('<?php echo $id_sucursal; ?>');
					$('#sucursal').val(5);
					$('#sucursal').prop("disabled", true);
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
		});

		function calculototalCosto(){
			var totalCosto = quitaSeparadorMiles($("#cantidad").val())*quitaSeparadorMiles($("#costo").val());
			if (!isNaN(totalCosto)) $("#costo_total").val(separadorMiles(totalCosto));
		}

		/*function calculoGananciaMin(){
			var totalGananciaMin = quitaSeparadorMiles($("#precio_vta_min").val()) - quitaSeparadorMiles($("#costo_total").val());
			if (!isNaN(totalGananciaMin)) {
				$("#ganancia_min").val(separadorMiles(totalGananciaMin));
				if (totalGananciaMin > 0){
					$("#ganancia_min").css("color","green");
				}else{
					$("#ganancia_min").css("color","red");
				}
			}
		}

		function calculoGananciaMay(){
			var totalGananciaMay = quitaSeparadorMiles($("#precio_vta_may").val()) - quitaSeparadorMiles($("#costo_total").val());
			if (!isNaN(totalGananciaMay)) {
				$("#ganancia_may").val(separadorMiles(totalGananciaMay));
				if (totalGananciaMay > 0){
					$("#ganancia_may").css("color","green");
				}else{
					$("#ganancia_may").css("color","red");
				}
			}
		}*/
		
		function guardarEntrada(){
			$.ajax({
				dataType: 'html',
				async: true,
				type: 'POST',
				url: 'inc/compra-productos-data.php',
				cache: false,
				data: {q: 'guardar', fecha: $("#fecha_carga").val(), id_sucursal: $("#sucursal").val(), descripcion: $("#descripcion").val(), datos: $('#tabla').bootstrapTable('getData') },	
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (data, status, xhr) {
					$("#mensaje").html(data);
					$('#tabla').bootstrapTable('scrollTo', 'top');
					var n = data.toLowerCase().indexOf("error");
						if (n == -1) {
							setTimeout(function () {
								  location.reload();
								// var param = { 'id_entrada_mercaderia': id_entrada_mercaderia, 'imprimir' : 'no' };
								// OpenWindowWithPost("comprobante-entrada-mercaderias.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=898,height=600", "ImprimirComprobante", param);
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
				<div class="col-md-2 col-sm-4 col-xs-12">
					<label>Nº</label>
					<p style="font-size:22px" id="entrada_nro"><?php echo getAutoincrement('compra_productos'); ?></p>
				</div>
				<div class="col-md-2 col-sm-4 col-xs-12">
					<label>Fecha</label>
					<div class="input-group date">
						<input type="text" class="form-control input-sm calendario" id="fecha_carga" value="<?php echo date('d/m/Y')?>"><span class="input-group-addon input-sm"><i class="glyphicon glyphicon-th"></i></span>
					</div>
				</div>
				<div class="col-md-2 col-sm-4 col-xs-12">
					<div class="form-group">
						<label for="sucursal">Sucursal</label>
						<select id="sucursal" name="sucursal" class="form-control input-sm" style="padding: 0">
						</select>
					</div>
				</div>
				<div class="col-md-6 col-sm-12 col-xs-12">
					<label>Descripción / Observaciones</label>
					<input type="text" class="form-control input-sm" id="descripcion" title="Escriba una descripción a esta entrada para organizar y localizar desde el administrador" autocomplete="off">
				</div>
			</div>
			<br>
			<div class="row">
				<input type="hidden" id="hidden_id_producto">
				<div class="col-md-5 col-sm-5 col-xs-12">
					<div class="form-group">
						<label for="buscar_producto">Producto<span id="spinner" style="display:none;margin-left:10px"><img src='images/progress_bar.gif'></span></label>
						<input class="typeahead form-control input-sm" id="buscar_producto" type="text" onclick="$(this).select();" placeholder="Buscar Producto" title="Escriba el nombre del producto y elija de la lista" autocomplete="off">
					</div>
				</div>
				<div class="col-md-2 col-sm-2 col-xs-6">
					<div class="form-group">
						<label for="cantidad">Cantidad</label>
						<input type="text" class="form-control input-sm" id="cantidad" title="Cantidad adquirida del producto" onkeyup="separadorMilesOnKey(event,this)" autocomplete="off" value="1">
					</div>
				</div>
				<div class="col-md-2 col-sm-2 col-xs-6">
					<div class="form-group">
						<label for="costo">Costo Unit.</label>
						<input type="text" class="form-control input-sm cerar" id="costo" title="Precio de compra o costo del producto por unidad" onkeyup="separadorMilesOnKey(event,this); calculototalCosto()" autocomplete="off" value="0">
					</div>
				</div>
				<div class="col-md-2 col-sm-2 col-xs-6">
					<div class="form-group">
						<label for="costo_total">Costo Total</label>
						<input type="text" class="form-control input-sm cerar" id="costo_total" title="Total del costo (costo unitario + costo shipping) del producto por unidad" readonly disabled value="0">
					</div>
				</div>
				<div class="col-md-1 col-sm-1 col-xs-6">
					<div class="form-group">
						<p style="line-height:12px">&nbsp;</p>
						<button type="button" class="btn btn-primary btn-md" id="agregar_producto">Agregar</button>
					</div>
				</div>
			</div>	
		
			<div id="mensaje"></div>
			<div id="toolbar"></div>
			<table id="tabla" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true"></table>
		
			<div style="float:right">
				<br>
				<button type="submit" class="btn btn-success btn-md submit_btn" id="guardar" onclick="guardarEntrada()">Guardar Entrada</button>
			</div>
			
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	

	
	
	
    <script src="js/menuHover.js"></script>
  </body>
</html>