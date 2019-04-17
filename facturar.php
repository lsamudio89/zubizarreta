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
	<link href="https://fonts.googleapis.com/css?family=Exo+2" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	

    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
	<script src="js/date.js"></script>
    
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
		#p_total { display:inline-block; vertical-align:middle; line-height:40px }
		#tabla_clientes.table-hover tbody tr:hover > td {
			 cursor: pointer;
		}
    </style>
	 
	 
	
    <script type="text/javascript">
		$(document).ready(function () {
		var $tabla_productos = $('#tabla_productos');	
		var $tabla_pagos = $('#tabla_pagos');	
			
			//noSubmitForm('form');
		
			//Funcion para que cuando se haga Enter en el input, hace click en el boton
			enterClick('exenta', 'agregar_producto');
			enterClick('venta_5', 'agregar_producto');
			enterClick('venta_10', 'agregar_producto');
			enterClick('monto', 'agregar_pago');
			
			
			////// BÚSQUEDA DE PRODUCTOS CON TYPEAHEAD ///////
			var datosBusqueda = new Bloodhound({
			  datumTokenizer: Bloodhound.tokenizers.whitespace('producto'),
			  queryTokenizer: Bloodhound.tokenizers.whitespace,
			  remote: {
				url: 'inc/facturar-data.php?q=buscar&filtro=%QUERY',
				wildcard: '%QUERY'
			  }
			});
			//console.log(datos_busqueda);
			$('#buscar_producto').typeahead(null, {
				hint: true,
				highlight: true,
				name: 'producto',
				limit: 20,
				source: datosBusqueda,
				display: 'producto',
				value: 'id_producto',
				templates: {
					empty: [
					  '<div class="empty-message">',
						'Sin resultados',
					  '</div>'
					].join('\n'),
					//<img class='productoImg' src='archivos/_thumbs/{{foto}}' height=32>
					suggestion: Handlebars.compile("<div><strong>{{producto}}</strong></div>")
				}
				}).on('typeahead:asyncrequest', function() {
					$('#spinner').show();
				})
				.on('typeahead:asynccancel typeahead:asyncreceive', function() {
					$('#spinner').hide();
				}).blur(function(){
					//SI EL VALOR DEL INPUT NO COINCIDE CON UN ELEMENTO DE LA LISTA, ENTONCES SE BORRA (ESTO OBLIGA A QUE SE SELECCIONE UN ELEMENTO DE LA LISTA)
					//Convertimos el objeto a array
					var check_value = $.map(datosBusqueda.remote.transport._cache.list.head.val, function(value, index) {
						return [value.producto];
					});
					if (check_value.indexOf($('#buscar_producto').val()) === -1){
						$('#buscar_producto').val('');
						public_id_producto = "";
						public_stock = "";
						$('#precio').val(0);
					}
				});
			
//                        console.log(datos_busqueda);
			//Cuando se selecciona un item de la lista
			$('#buscar_producto').bind('typeahead:select', function(ev, datos) {
				public_id_producto = datos.id_producto;
                                $("#iva").val(datos.iva);
				$("#mensaje").html("");
				$("#mensaje_pagos").html("");
                               
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
						$("#mensaje").html("");
						$tabla_productos.bootstrapTable('removeByUniqueId', row.id_tabla_productos);
						$('#hidden_descuento').val(0);
						$tabla_pagos.bootstrapTable('removeAll');
					}
				}
			};

			$tabla_productos.bootstrapTable({
				height: $(window).height()-400,
				data: [],
				uniqueId: 'id_tabla_productos',
				showFooter: true,
				columns: [
					[
						{	field: 'id_tabla_productos', visible: false	}, 
						{	field: 'id_producto', align: 'center', valign: 'middle', title: 'Cód.', sortable: true, visible: false	}, 
						{	field: 'producto', align: 'left', valign: 'middle', title: 'Servicios', sortable: true , footerFormatter: totalTextFormatter	},
						{	field: 'cantidad', align: 'center', valign: 'middle', title: 'Cant.', sortable: true, footerFormatter: sumarTotales	},
						{	field: 'exenta', align: 'center', valign: 'center', title: 'Exentas', sortable: true, footerFormatter: totalFooter	},
						{	field: 'venta_5', align: 'center', valign: 'center', title: '5%', sortable: true, footerFormatter: totalFooter	},
						{	field: 'venta_10', align: 'center', valign: 'center', title: '10%', sortable: true, visible: true, footerFormatter: totalFooter},
						{	field: 'borrar', align: 'center', valign: 'middle', title: 'Borrar', sortable: false, events: borrarItem,  formatter: icono	}
					]
				]
			});
			
				//Altura de tabla automatica
			$(window).resize(function () {
				$tabla_productos.bootstrapTable('refreshOptions', { 
					height: $(window).height()-400,
				});
				$tabla_productos.bootstrapTable('resetView', { });
			});

			
			function totalTextFormatter(data) {	
				return "<span style='font-size:16px'><b>TOTALES:</b></span>"; 
			}
			
			function cantidadFooter(data) {
				var total=0;
			
				var field = this.field;
				$.each(data, function (i, row) {
					total += +parseInt(quitaSeparadorMiles(row[field]));
				});
				return "<span style='font-size:16px'><b>" + separadorMiles(total)+"</b></span>";
			}

			function totalFooter(data) {
				var total=0;
				var field = this.field;
				$.each(data, function (i, row) {
					total += +parseInt(quitaSeparadorMiles(row[field]));
				});
				//console.log(total);
				return "<span style='font-size:16px'><b> Gs. " + separadorMiles(total)+"</b></span>";
			}
			
			function sumarTotales(data) {
				var exenta=0;
				var total_5=0;
				var total_10=0;

				var field = this.field;
				if (field){
					$.each(data, function (i, row) {
						exenta += +parseInt(quitaSeparadorMiles(row['exenta']));
						total_5 += +parseInt(quitaSeparadorMiles(row['venta_5']));
						total_10 += +parseInt(quitaSeparadorMiles(row['venta_10']));
					});
				}
				var totales=exenta+total_5+total_10;
				$('.p_total_pagar').html("Gs. "+separadorMiles(totales));
				//pone el monto a pagar
                $("#monto").val(separadorMiles(totales));
			}
			
			
			$("#agregar_producto").click(function () {
				if (!public_id_producto){
					$("#mensaje").html(alertDismissJS('Favor seleccione un Servicio de la lista antes de agregar', 'error'));
				}
				else if ($('#exenta').val()==0 && $('#venta_5').val()==0 && $('#venta_10').val()==0 ){
					$("#mensaje").html(alertDismissJS('Favor ingrese precio de venta', 'error'));
				}else{
					
					//VERIFICAMOS QUE NO EXISTA PRODUCTO YA CARGADO
					var check_status = 0;
					var datos = $tabla_productos.bootstrapTable('getData');
					for(var k in datos){
						if (public_id_producto == datos[k].id_producto){
							 check_status=1;
						}
					}
					
					//Recalculamos
					if (check_status==1){
						$("#mensaje").html(alertDismissJS('Servicio ya cargado', 'error'));
					}else{
						
						$tabla_productos.bootstrapTable('scrollTo', 'top');
						var id_tabla_productos = new Date().getTime();//ID PARA PODER ELIMINAR LA FILA EN CASO DE NECESIDAD
                                                
                                              
                                                var iva = parseInt($('#iva').val());
                                                var monto_servicio =$('#monto_servicio').val();
                                                if(iva ==0){
                                                    var exenta = monto_servicio;
                                                }else if(iva ==5){
                                                    var iva5 = monto_servicio;
                                                } else if(iva ==10){
                                                    var iva10 = monto_servicio;
                                                }
                                                
                                               
						$tabla_productos.bootstrapTable('insertRow', {
							index: 0,
							row: {
								id_tabla_productos: id_tabla_productos,
								id_producto: public_id_producto,
								producto: $('#buscar_producto').val(),
								cantidad: 1,
								exenta: exenta,
								venta_5: iva5,
								venta_10: iva10
							}
						});
						setTimeout(function () {
							$tabla_pagos.bootstrapTable('removeAll');
							$('#saldo').val(0);
							//$tabla_productos.bootstrapTable('scrollTo', 'top');
							//Luego de insertar vaciamos los inputs
							$("#mensaje").html("");
							$("#mensaje_pagos").html("");
                                                        $('#monto_servicio').html("");
							$('#buscar_producto').typeahead('val', '');
							$('#buscar_producto').typeahead('close');
							public_id_producto="",
                                                        
							
							$('#buscar_producto').focus();
						}, 50);
					}
				}
			});
			
			function iconoPago(value, row, index) {
				return [
					'<a class="remove_pago" href="javascript:void(0)" title="Eliminar">',
					'<i class="glyphicon glyphicon-trash"></i>',
					'</a>'
				].join('');
			}
			
			window.borrarPago = {
				'click .remove_pago': function (e, value, row, index) {
					var confDel = confirm("¿Borrar método de pago "+row.metodo_pago.toUpperCase()+"?");
					if (confDel){
						$("#mensaje_pagos").html("");
						$("#mensaje").html("");
						$tabla_pagos.bootstrapTable('removeByUniqueId', row.id_pago);
						setTimeout(function () {
							if (row.metodo_pago=="Descuento") $('#hidden_descuento').val(0);
							$('.p_total_pagar').html("Gs. "+separadorMiles(totalPagar()));
							$('#saldo').val(separadorMiles(totalPagar()-totalPagos()));
						}, 200);
					}
				}
			};
			
			$tabla_pagos.bootstrapTable({
				data: [],
				uniqueId: 'id_pago',
				columns: [
					[
						{	field: 'id_pago', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: false	}, 
						{	field: 'metodo_pago', align: 'left', valign: 'middle', title: 'Método de Pago', sortable: true },
						{	field: 'cod_aut', align: 'left', valign: 'middle', title: 'Cód. Aut.', sortable: true },
						{	field: 'monto', align: 'right', valign: 'middle', title: 'Monto', sortable: true	},
						{	field: 'borrar', align: 'center', valign: 'middle', title: 'Borrar', sortable: false, events: borrarPago,  formatter: iconoPago	}
					]
				]
			});

			$("#agregar_pago").click(function () {
                        
				if ($('#tipo_giro').val() && $('#tipo_giro').val() != ""){
					var metodoPago = $('#metodo_pago').val()+" ("+$('#tipo_giro').val()+")";
				}else{
					var metodoPago = $('#metodo_pago').val();
				}
				var monto = quitaSeparadorMiles($('#monto').val());
				var cod_aut = $('#cod_aut').val();
				
			
				
				if ($('.p_total_pagar').html() == "Gs. 0"){
					$("#mensaje_pagos").html(alertDismissJS("Ningún producto agregado. Favor verifique.", "error"));
				}else if (!monto || monto==0){
					if(metodoPago=="Descuento"){
						$("#mensaje_pagos").html(alertDismissJS('Favor escriba un monto mayor a cero que será descontado del total a pagar', 'error'));
						$('#monto').focus();
						$('#monto').select();
					}else{
						$("#mensaje_pagos").html(alertDismissJS('Favor escriba un monto mayor a cero a ser pagado con '+metodoPago.toUpperCase(), 'error'));
						$('#monto').focus();
						$('#monto').select();
					}
				}else{
					if ((metodoPago=="Tarjeta de Crédito" || metodoPago=="Tarjeta de Débito") && !cod_aut){
						$("#mensaje_pagos").html(alertDismissJS("Favor ingrese Código de Autorización del ticket impreso en el POS.", "error"));
					}else{

						//VERIFICAMOS QUE NO EXISTA EL METODO DE PAGO YA CARGADO
						var check_status=0; var hayTarjeta=0;
						var datos = $tabla_pagos.bootstrapTable('getData');
						for(var k in datos) {
							if (metodoPago == "Efectivo" && metodoPago == datos[k].metodo_pago){
								 check_status=1;
							}
							//COMPROBAMOS SI UNO DE LOS METODOS DE PAGO YA CARGADOS ES TARJETA PARA PODER DESHABILITAR IMPRESION TICKET
							if (datos[k].metodo_pago == "Tarjeta de Crédito" || datos[k].metodo_pago == "Tarjeta de Débito"){
								hayTarjeta=1;
							}
						}
						if (hayTarjeta==1){
							$("#btn_imprimir_ticket").css("visibility", "hidden");
						}else{
							$("#btn_imprimir_ticket").css("visibility", "visible");
						}
								
						if (check_status==1){
							$("#mensaje_pagos").html(alertDismissJS("Método de pago ya agregado a la lista. Favor verifique", "error"));
						}else{
							if (metodoPago=="Descuento"){
								if (monto > totalPagar()){
										$("#mensaje_pagos").html(alertDismissJS("Monto a descontar supera el total a pagar. Favor verifique", "error"));
										$('#monto').focus();
										$('#monto').select();
								}else{
									if (datos.length==0){
										monto = -Math.abs(monto); //CONVERTIMOS A NEGATIVO
										insertaPagos("Descuento",monto);
										$('#hidden_descuento').val(monto);
										$('.p_total_pagar').html("Gs. "+separadorMiles(totalPagar()));
										$('#saldo').val(separadorMiles(totalPagar()));
									}else{
										var preg = confirm("El descuento se debe cargar primero. ¿Desea borrar los otros métodos de pagos?");
										if (preg){
											$tabla_pagos.bootstrapTable('removeAll');
											monto = -Math.abs(monto); //CONVERTIMOS A NEGATIVO
											insertaPagos("Descuento",monto);
											$('#hidden_descuento').val(monto);
											$('.p_total_pagar').html("Gs. "+separadorMiles(totalPagar()));
											$('#saldo').val(separadorMiles(totalPagar()));
										}
									}
								}
							}else{
								if (totalPagos()+monto> totalPagar()){
									$("#mensaje_pagos").html(alertDismissJS("Monto a pagar supera saldo o el total a pagar "+totalPagar(), "error"));
									$('#monto').focus();
									$('#monto').select();
								}else{
									insertaPagos(metodoPago,monto,cod_aut);
									var saldo = parseInt(totalPagar())-parseInt(totalPagos());
									$('#saldo').val(separadorMiles(saldo));
								}
							}
						}
					}
				}
			});

			function totalPagar(){
				total_pagar=0;
				var datosProductos = $tabla_productos.bootstrapTable('getData');
				for(var m in datosProductos) {
					
					venta_10=parseInt(quitaSeparadorMiles(datosProductos[m].venta_10));
					venta_5=parseInt(quitaSeparadorMiles(datosProductos[m].venta_5));
					exenta=parseInt(quitaSeparadorMiles(datosProductos[m].exenta));
					total_pagar +=venta_10+venta_5+exenta;
				}
				total_pagar += parseInt($('#hidden_descuento').val());
				return total_pagar;
			}
			
			function totalPagos(){
				var total_pagos=0;
				var datosPagos = $tabla_pagos.bootstrapTable('getData');
				for(var j in datosPagos) {
					total_pagos += quitaSeparadorMiles(datosPagos[j].monto);
					if(datosPagos[j].metodo_pago=="Descuento") total_pagos += Math.abs(quitaSeparadorMiles(datosPagos[j].monto));
				}
				return total_pagos;
			}
			
			function insertaPagos(metodoPago,monto,cod_aut){
				$tabla_pagos.bootstrapTable('scrollTo', 'top');
				var id_pago = new Date().getTime();//ID PARA PODER ELIMINAR LA FILA EN CASO DE NECESIDAD
				$tabla_pagos.bootstrapTable('insertRow', {
					index: 0,
					row: {
						id_pago: id_pago,
						metodo_pago: metodoPago,
						cod_aut: cod_aut,
						monto: separadorMiles(monto),
					}
				});
				setTimeout(function () {
					$tabla_pagos.bootstrapTable('scrollTo', 'bottom');
					$("#mensaje").html("");
					$("#mensaje_pagos").html("");
					$('#monto').val("");
					$('#cod_aut').val("");
					$('#tipo_giro').val("");
				}, 200);
			}
			
			$('#metodo_pago').change(function () {
				var saldo = parseInt(totalPagar())-parseInt(totalPagos());
				$('#monto').val(separadorMiles(saldo));
				
				switch($(this).val()){
					case 'Giro':
						restoreDivPagos();
						$('#div_metodo_pago').removeClass('col-md-4').addClass('col-md-3');
						$('#div_monto').removeClass('col-md-3').addClass('col-md-2');
						$('#div_saldo').removeClass('col-md-3').addClass('col-md-2');
						$('#div_tipo_giro').css("display","inline");
						$('#tipo_giro').select();
						$('#tipo_giro').focus();
					break;
					
					case 'Tarjeta de Crédito':
					case 'Tarjeta de Débito':
						restoreDivPagos();
						$('#div_metodo_pago').removeClass('col-md-4').addClass('col-md-3');
						$('#div_monto').removeClass('col-md-3').addClass('col-md-2');
						$('#div_saldo').removeClass('col-md-3').addClass('col-md-2');
						$('#div_cod_aut').css("display","inline");
						$('#cod_aut').select();
						$('#cod_aut').focus();
					break;
					
					default:
						restoreDivPagos();
						$('#tipo_giro').val("");
						$('#monto').select();
						$('#monto').focus();
					break;
				}
			});
			
			function restoreDivPagos(){
				$('#div_metodo_pago').removeClass('col-md-3').addClass('col-md-4');
				$('#div_monto').removeClass('col-md-2').addClass('col-md-3');
				$('#div_saldo').removeClass('col-md-2').addClass('col-md-3');
				$('#div_tipo_giro').css("display","none");
				$('#div_cod_aut').css("display","none");
			}
			
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
				dataType: 'json', async: true, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						if (value.id_sucursal == '<?php echo $id_sucursal; ?>'){
							$('#sucursal').html(value.sucursal);
						}
					});
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
			$(".modal").draggable({
				handle: ".modal-header"
			});
			
			$("#form_cliente").submit(function(event){
				$("#msjModalAdd").html("");
				
				//Evita el submit default del php
				event.preventDefault();
			
				var formData = $("#form_cliente").serializeArray();
				var URL = $("#form_cliente").attr("action");
					$("#msjModalAdd").html("<img src='images/progress_bar.gif'>");
				$.post(URL, formData, function() {
				})
				.done(function(data) {
					//$('#mensaje_ruc').html(data);
					$("#msjModalAdd").html(data);
					var n = data.toLowerCase().indexOf("error");
					if (n == -1) {
						$('.modal-cliente').modal('hide');
					}
				})
				.fail(function(jqXHR) {
					$('#msjModalAdd').html(alertDismissJS(jqXHR.responseText, "error"));
				});
			});
			
			$('#tabla_clientes').bootstrapTable({
				height: $(window).height()-210,
				pageSize: Math.floor(($(window).height()-210)/26)-5,
				columns: [
					[
						//{	field: 'state', align: 'left', valign: 'middle', title: 'Sel.', checkbox: true },
						{	field: 'id_cliente', align: 'left', valign: 'middle', title: 'ID cliente', sortable: true, visible: false	}, 
						{	field: 'ruc', align: 'left', valign: 'middle', title: 'RUC / CI', sortable: true	},
						{	field: 'razon_social', align: 'left', valign: 'middle', title: 'Razón Social', sortable: true	},
						{	field: 'tipo', align: 'left', valign: 'middle', title: 'Tipo', sortable: true	}
					]
				]
			});
			
			$('.modal-buscar-cliente').on('shown.bs.modal', function (e) {
				$('#tabla_clientes').bootstrapTable("refresh", {url: 'inc/facturar-data.php?q=buscar_razon_social', pageSize: Math.floor(($(window).height()-210)/26)-5});
				$('#tabla_clientes').bootstrapTable('resetSearch', '');
				
				$('.bootstrap-table').find("input[type=text]").each(function(ev)
				  {
					if($(this).attr("placeholder") == "Buscar") { 
						$(this).focus();
						$(this).select();
					}
				 });
			});
			
			$('#tabla_clientes').on('dbl-click-row.bs.table', function (row, $element, field) {
				$('#ruc').val($element.ruc);
				$('#ruc').blur();
				 $(".modal-buscar-cliente").modal("hide");
				 $('#buscar_codigo').focus();
			});
				
			
		
			/*$('#tabla_clientes').on('page-change.bs.table', function (e) {
				$('#tabla_clientes').bootstrapTable('resetView', {
					height: 365
				});
			});*/
			
			
			$('.modal-cliente').on('hide.bs.modal', function (e) {
				$("#ruc").val($("#ruc_carga").val());
				$("#razon_social").val($("#razon_social_carga").val());
				//$("#ruc").blur();
			});
			
			
		});
		
		function buscarCliente(){
			var ruc = $("#ruc").val();
			if (!ruc){
				ruc = '44444401-7';
			}
			$.ajax({
				dataType: 'json', async: true, cache: false, url: 'inc/facturar-data.php', type: 'POST', data: {q:'buscar_cliente', ruc:ruc},
				beforeSend: function(){
					$("#mensaje_ruc").html("Buscando Cliente <img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$("#mensaje_ruc").html("");
					if(json){
						if (json.razon_social=="no registrado"){
						public_tipo_cliente = "Minorista";
							$("#tipo_cliente").html("Cliente Minorista");
							//buscarRUC($("#ruc").val());
							modalRegistrarCliente();
						}else{
							public_tipo_cliente = json.tipo;
							public_id_cliente = json.id_cliente;
							$("#tipo_cliente").html("Cliente "+json.tipo);
							$("#razon_social").val(json.razon_social);
							$("#ruc").val(json.ruc);
							$("#buscar_codigo").focus();
						}
					}else{
						public_id_cliente="";
						public_tipo_cliente="";
						$("#tipo_cliente").html("");
					}
				},
				error: function (xhr) {
					console.log(xhr);
					$("#mensaje_ruc").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
		}
		
		function buscarRUC(input_ruc,input_razon,msj){
			var ruc = $("#"+input_ruc).val();
			if (ruc){
				$("#"+msj).html("Buscando RUC <img src='images/progress_bar.gif'>");
				$.getJSON("http://www.freelancer.com.py/ruc.php?ruc="+ruc+"&jsoncallback=?")
					.done(function(resp) {
						$("#"+msj).html("");
						if(resp.ruc){
							$("#"+input_razon).val(resp.razon_social);
							$("#"+input_ruc).val(resp.ruc);
						}
					})
					.fail(function(resp) {
						$('#'+msj).html("Error al consultar RUC. "+resp.responseText);
					})
				return false;
			}
		}
		
		function modalRegistrarCliente(x){
			$('.modal-cliente').modal('show');
			$("#msjModalAdd").html("");
			$("#mensaje_cliente").html("");
			if (x !='btn') $("#mensaje_cliente").html(alertDismissJS("Cliente no registrado. Favor complete con sus datos.","info"));
			$("#razon_social_carga").val("");
			$("#direccion_carga").val("");
			$("#telefono_carga").val("");
			$("#email_carga").val("");
			$("#ruc_carga").val($("#ruc").val());
			$("#ruc_carga").focus();
		}
		
		
		function modalImprimir_zubizarreta(){
			if(!$("#ruc").val()){
				$("#mensaje_ruc").html(alertDismissJS("Favor elija un cliente o utilice el RUC 44444401-7 para comprobante SIN NOMBRE", "error"));
				$("#ruc").focus();
			}else if (jQuery.isEmptyObject($('#tabla_productos').bootstrapTable('getData'))){ 
				$("#mensaje").html(alertDismissJS("Ningún producto agregado. Favor verifique.", "error"));
				$("#buscar_producto").focus();
			}else if (jQuery.isEmptyObject($('#tabla_pagos').bootstrapTable('getData'))){
				$("#mensaje_pagos").html(alertDismissJS("Favor agregue al menos un método de pago para finalizar la venta.", "error"));
				$("#monto").focus();
			}else if ($("#saldo").val()>0) {
				$("#mensaje_pagos").html(alertDismissJS("Hay saldo pendiente. Agregue un método de pago y monto hasta completar el total a pagar.", "error"));
				$("#monto").focus();
			}else{
				$("#mensaje_imprimir").html("");
				$('.modal-imprimir').modal('show');
				$("#monto_entregado").val(separadorMiles(total_pagar)); //ACCEDEMOS A LA VARIABLE PUBLICA DE LA FUNCION totalPagar()
				calculoCambio();
				$("#monto_entregado").select();
				$("#monto_entregado").focus();
			}
		}
                
                function modalImprimir(){
			
			var total_pagos=0;
			var $tabla_pagos = $('#tabla_pagos');	
			var datosPagos = $tabla_pagos.bootstrapTable('getData');
			for(var j in datosPagos) {
				total_pagos += quitaSeparadorMiles(datosPagos[j].monto);
				if(datosPagos[j].metodo_pago=="Descuento" || datosPagos[j].metodo_pago=="Nota") total_pagos += Math.abs(quitaSeparadorMiles(datosPagos[j].monto));
			}
			
			condicion=$("#condicion").val();
			nota_cr=$("#nota_cr").val();
			if(condicion=='contado'){
				if(!$("#ruc").val()){
					$("#mensaje_ruc").html(alertDismissJS("Favor elija un cliente o utilice el RUC 44444401-7 para comprobante SIN NOMBRE", "error"));
					$("#ruc").focus();
				}else if (jQuery.isEmptyObject($('#tabla_productos').bootstrapTable('getData'))){ 
					$("#mensaje").html(alertDismissJS("Ningún producto agregado. Favor verifique.", "error"));
					$("#buscar_producto").focus();
				}
				else if (jQuery.isEmptyObject($('#tabla_pagos').bootstrapTable('getData'))){
					$("#mensaje_pagos").html(alertDismissJS("Favor agregue al menos un método de pago para finalizar la venta.", "error"));
					$("#monto").focus();
				}
				else if(total_pagos<200000 && public_tipo_cliente=="Mayorista" && nota_cr==false){
				  $("#mensaje_pagos").html(alertDismissJS("Compra minima para mayorista 200.000 Gs.", "error"));
				  $("#buscar_producto").focus();	
				}
				else if ($("#saldo").val()>0){
					$("#mensaje_pagos").html(alertDismissJS("Hay saldo pendiente. Agregue un método de pago y monto hasta completar el total a pagar.", "error"));
					$("#monto").focus();
				}
				else{
					$("#mensaje_imprimir").html("");
					$('.modal-imprimir').modal('show');
					$("#monto_entregado").val(separadorMiles(total_pagar)); //ACCEDEMOS A LA VARIABLE PUBLICA DE LA FUNCION totalPagar()
					calculoCambio();
					$("#monto_entregado").select();
					$("#monto_entregado").focus();
				}
			}else if(condicion=='credito'){
					$("#mensaje_imprimir").html("");
					$('.modal-imprimir').modal('show');
					$("#monto_entregado").val(0)
			}
		}
		
		function calculoCambio(){
			/*var monto_final_tmp = $('.p_total_pagar').html().split("Gs. ");
			var monto_final = monto_final_tmp[1];*/
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
		
		function imprimir(tipo){
			if (quitaSeparadorMiles($("#cambio").val()) < 0){
				$("#mensaje_imprimir").html(alertDismissJS("Monto entregado no válido. Verifique", "error"));
			}else{	
				$.ajax({
					dataType: 'json',
					async: true,
					type: 'POST',
					url: 'inc/facturar-data.php',
					cache: false,
					data: {q:'facturar', tipo:tipo, ruc:$("#ruc").val(), razon_social:$("#razon_social").val(), id_cliente: public_id_cliente, condicion:$("#condicion").val(), fecha_vencimiento:$("#fecha_vencimiento").val(), productos:$('#tabla_productos').bootstrapTable('getData'), pagos:$('#tabla_pagos').bootstrapTable('getData'), descuento:$("#hidden_descuento").val(),nota_cr:$("#nota_cr").val(), tipo_venta:public_tipo_cliente },	
					beforeSend: function(){
						$("#mensaje_imprimir").html("<img src='images/progress_bar.gif'>");
					},
					success: function (data, status, xhr) {
						$("#mensaje_imprimir").html(data.mensaje);
						$('#tabla_productos').bootstrapTable('scrollTo', 'top');
						var n = data.mensaje.toLowerCase().indexOf("error");
							if (n == -1) {
								//Si es ticket (comprobante de venta)
								if (tipo=="t"){
									 var param = { 'id': data.id_factura, 'imprimir' : 'si', 'recargar':'si' };
									 OpenWindowWithPost("imprimir-comprobante.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "ImprimirComprobante", param);
								//Si es factura
								}else{
									//Si es mayorista y si pidió factura se imprime factura y planilla mayorista
									if (public_tipo_cliente=="Mayorista"){
										var param2 = { 'id': data.id_factura, 'imprimir' : 'no', 'recargar':'si' };
										OpenWindowWithPost("imprimir-comprobante.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "ImprimirComprobanteMayorista", param2);
										var param = { 'id': data.id_factura, 'imprimir' : 'si', 'recargar':'no' };
										OpenWindowWithPost("imprimir-factura.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "imprimirFacturaMayorista", param);
									//Si es minorista se imprime factura
									}else{
										var param = { 'id': data.id_factura, 'imprimir' : 'si', 'recargar':'si' };
										OpenWindowWithPost("imprimir-factura.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=825,height=650", "imprimirFacturaMinorista", param);
									}
								}
								
								 /*var param = { 'id': data.id_factura, 'imprimir' : 'no' };
								 OpenWindowWithPost("imprimir-planilla.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=600", "ImprimirComprobante", param);
								 location.reload();*/
							}
					},
					error: function (xhr) {
						$("#mensaje_imprimir").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
			}
		}
		
		function deshabilitar(){
			$("#btn_imprimir_fact").attr("disabled",true);
			$("#btn_imprimir_ticket").attr("disabled",true);
		}
		
		function validar(){
		ruc.value = ruc.value.replace(/[^0-9]/g,'');
		}
	
        $(document).ready(function () {
            var condicion = $("#condicion").val();
            if(condicion == 'contado'){
                $("#mostrar_vencimiento").hide();
            }else{
                $("#mostrar_vencimiento").show();
            }
            
        })
        
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
				<h2 class="titulo_pestanas"><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?> <span style="margin-left:20px;font-size:13px"> <?php echo date('d/m/Y H:i')?> hs.</span></h2>
			</div>
			<div class="row">
				<div class="col-md-2 col-sm-4 col-xs-12">
					<label>RUC</label>
					<div class="input-group">
						<input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" placeholder="Buscar RUC o CI..." onblur="buscarCliente();" onkeyup="validar();">
							<span class="input-group-btn">
							   <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal-buscar-cliente" title="Buscar Cliente"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
								<button type="button" class="btn btn-success btn-sm" onclick="modalRegistrarCliente('btn')" title="Registrar Cliente"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></button>
							</span>
					</div>
				</div>
				
				<div class="col-md-4 col-sm-4 col-xs-12">
					<label for="sucursal">Razón Social</label><span style="float:right" id="tipo_cliente" class="label label-danger"></span>
					<input class="form-control input-sm" type="text" name="razon_social" id="razon_social" autocomplete="off">
				</div>
				<div class="col-md-2 col-sm-6 col-xs-12">
					<label>Condición:</label>
					<select id="condicion" class="form-control input-sm" onchange="Condicion_factura()">
                                            <option value="contado">Factura Contado</option>
                                            <option value="credito">Factura Crédito</option>
                                        </select>
				</div>
                             
				<div class="col-md-4 col-sm-6 col-xs-12" style="text-align:center">
					<label>Total a Pagar</label>
					<p style="font-size:40px;line-height:40px" class="p_total_pagar">Gs. 0</p>
				</div>
			</div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12 col-xs-12"></div>
                        <div class="col-md-2 col-sm-12 col-xs-12" id="mostrar_vencimiento">
                                <label>Vencimiento:</label>
                                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control input-sm" required="required" onchange="Condicion_factura()">                                            
                        </div>
                    </div>
			<div class="row">
				<div class="col-md-6 col-sm-12 col-xs-12">
				<span id="mensaje_ruc"></span>
					<h3 class="titulo_pestanas" style="line-height:40px">Servicios</h3>
					<div class="row">
						<div class="col-md-4 col-sm-5 col-xs-12">
							<div class="form-group">
								<label for="buscar_producto">Servicios<span id="spinner" style="display:none;margin-left:10px"><img src='images/progress_bar.gif'></span></label>
								<input class="typeahead form-control input-sm" id="buscar_producto" type="text" onclick="$(this).select();" placeholder="Buscar Servicios" title="Escriba el nombre del producto y elija de la lista" autocomplete="off">
							</div>
						</div>
                                            
                                                <div class="col-md-4 col-sm-5 col-xs-12">
							<div class="form-group">
								<label for="buscar_monto">Monto<span id="spinner" style="display:none;margin-left:10px"><img src='images/progress_bar.gif'></span></label>
								<input class="typeahead form-control input-sm" id="monto_servicio" onkeyup="separadorMilesOnKey(event,this);" type="text"  placeholder="Ingrese Monto del Servicio" title="Escriba el monto del servicio" autocomplete="off">
							</div>
						</div>
						<!--<div class="col-md-2 col-sm-2 col-xs-6">
							<div class="form-group">
								<label for="precio">5%</label>
								<input type="text" class="form-control input-sm cerar" id="venta_5" name="venta_5" title="Venta 5%" onkeyup="separadorMilesOnKey(event,this);">
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-6">
							<div class="form-group">
								<label for="precio">10%</label>
								<input type="text" class="form-control input-sm cerar" id="venta_10" name="venta_10" title="Venta 10%" onkeyup="separadorMilesOnKey(event,this);">
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-6">
							<div class="form-group">
								<label for="precio">Exentas</label>
								<input type="text" class="form-control input-sm cerar" id="exenta" name="exenta" title="Venta Exenta" onkeyup="separadorMilesOnKey(event,this);">
							</div>
						</div>-->
						<div class="col-md-2 col-sm-2 col-xs-6">
							<div class="form-group">
								<p style="line-height:12px">&nbsp;</p>
								<button type="button" class="btn btn-primary btn-md" id="agregar_producto" style="float:right">Agregar</button>
							</div>
						</div>
					</div>	
					<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<div id="mensaje"></div>
							<table id="tabla_productos" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true"></table>
						</div>
					</div>
					<br>
				</div>
				<div class="col-md-6 col-sm-12 col-xs-12">
					<h3 class="titulo_pestanas" style="line-height:40px;">Pagos</h3>
					<input type="hidden" id="hidden_descuento" value="0">
					<div class="row">
						<div class="col-md-4 col-sm-4 col-xs-12" id="div_metodo_pago">
							<label>Método de Pago:</label>
							<select id="metodo_pago" class="form-control input-sm">
							  <option value="Efectivo">Efectivo</option>
							  <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
							  <option value="Tarjeta de Débito">Tarjeta de Débito</option>
							  <option value="Cheque">Cheque</option>
							  <option value="Giro">Giro</option>
							  <option value="Descuento">Descuento</option>
						  </select>
						</div>
						<div class="col-md-3 col-sm-3 col-xs-6" id="div_tipo_giro" style="display:none">
							<div class="form-group">
								<label for="tipo_giro">Tipo Giro</label>
								<input type="text" class="form-control input-sm" id="tipo_giro" title="Ej: Tigo, Personal, Banco, etc" autocomplete="off">
							</div>
						</div>
						<div class="col-md-3 col-sm-3 col-xs-6" id="div_cod_aut" style="display:none">
							<div class="form-group">
								<label for="cod_aut">C. Aut.</label>
								<input type="text" class="form-control input-sm" id="cod_aut" title="Código de Autorización" autocomplete="off" onkeypress="return soloNumeros(event)">
							</div>
						</div>
						<div class="col-md-3 col-sm-3 col-xs-6" id="div_monto">
							<div class="form-group">
								<label for="monto">Monto</label>
								<input type="text" class="form-control input-sm" id="monto" title="Monto a pagar" autocomplete="off" onkeyup="separadorMilesOnKey(event,this);">
                                                                <input type="hidden" id="iva">
							</div>
						</div>
						<div class="col-md-3 col-sm-3 col-xs-6" id="div_saldo">
							<div class="form-group">
								<label for="saldo">Saldo</label>
								<input type="text" class="form-control input-sm" id="saldo" title="Saldo" value="0" readonly>
							</div>
						</div>
						<div class="col-md-2 col-sm-2 col-xs-6">
							<div class="form-group">
								<p style="line-height:12px">&nbsp;</p>
								<button type="button" class="btn btn-info btn-sm" id="agregar_pago" style="float:right">Agregar Pago</button>
							</div>
						</div>
					</div>	
					<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<div id="mensaje_pagos"></div>
							<table id="tabla_pagos" data-pagination="false" data-classes="table table-hover table-condensed" data-striped="true"></table>
							<div style="float:right">
								<br>
								<button type="button" class="btn btn-success btn-md submit_btn" id="guardar" onclick="modalImprimir()">Continuar</button>
							</div>
						</div>
					</div>
					<br>
				</div>
			</div>
			<br>
			
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
									<div class="form-group">
										<label for="tipo_editar">Total a Pagar</label>
										<p style="font-size:32px;line-height:40px" class="p_total_pagar">Gs. 0</p>
									</div>
									<div class="form-group">
										<label for="tipo_editar">Monto entregado</label>
										<!--<input class="form-control input-md" type="number" pattern="(?=.{1,10}$)\d{1,3}(?:.\d{3})+" id="monto_entregado" step="50" onkeyup="calculoCambio()" autocomplete="off" style="font-weight:bold;font-size:20px">-->
										<input class="form-control input-md" type="text" id="monto_entregado" onkeyup="separadorMilesOnKey(event,this);calculoCambio()" autocomplete="off" style="font-weight:bold;font-size:20px">
									</div>
									<div class="form-group">
										<label for="tipo_editar">Cambio</label>
										<input class="form-control input-sm" type="text" id="cambio" readonly style="font-weight:bold;font-size:16px">
									</div>
								</div>
							</div>
						
						<div id="mensaje_imprimir"></div>
					</div>

					<div class="modal-footer">
						
						<div class="row">
<!--							<div class="col-md-6 col-sm-6 col-xs-6">
								<button type="button" class="btn btn-primary btn-sm" id="btn_imprimir_ticket" onclick="imprimir('t');deshabilitar()">Imprimir Comprobante (ticket)</button>
							</div>-->
							<div class="col-md-6 col-sm-6 col-xs-6" style="float:right">
								<button type="button" class="btn btn-danger btn-sm"  id="btn_imprimir_fact" onclick="imprimir('f');deshabilitar()" style="float:right">Imprimir Factura</button>
							</div>
						</div>
					</div>
				</div>
			  </div>
			</div>
			
			<!-- MODA REGISTRAR CLIENTE -->
			<div class="modal modal-cliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
				<div class="modal-dialog modal modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<button aria-label="Close" data-dismiss="modal" class="close" type="button" onclick="javascript:$('#ruc_carga').val('');"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title">Registrar Cliente<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
						</div>
						<div class="modal-body">
							<form class="form" id="form_cliente" method="post" enctype="multipart/form-data" action="inc/administrar-clientes-data.php?q=cargar">
								<div class="container-fluid">
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
										<div id="mensaje_cliente"></div>
										
										<br>
											<div class="row">
												<div class="col-md-3 col-sm-6 col-xs-12">
													<div class="form-group">
														<label for="ruc_carga">RUC / CI</label>
														<input class="form-control input-sm" type="text" name="ruc_carga" id="ruc_carga" autocomplete="off" onblur="buscarRUC(this.id,'razon_social_carga','mensaje_ruc_carga');">
														<span id="mensaje_ruc_carga"></span>
													</div>
												</div>
												
												<div class="col-md-9 col-sm-12 col-xs-12">
													<div class="form-group">
														<label for="razon_social_carga">Nombre / Razón Social<span id="loading_razon" style="display:none;margin-left:10px"><img src='images/progress_bar.gif'></span></label>
														<input class="form-control input-sm" type="text" name="razon_social_carga" id="razon_social_carga" autocomplete="off">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-3 col-sm-6 col-xs-12">
													<div class="form-group">
														<label for="telefono_carga">Teléfono</label>
														<input class="form-control input-sm" id="telefono_carga" name="telefono_carga" type="text" autocomplete="off">
													</div>
												</div>
												<div class="col-md-9 col-sm-6 col-xs-12">
													<div class="form-group">
														<label for="direccion_carga">Dirección</label>
														<input class="form-control input-sm" id="direccion_carga" name="direccion_carga" type="text" autocomplete="off">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-8 col-sm-6 col-xs-12">
													<div class="form-group">
														<label for="email_carga">E-mail</label>
														<input class="form-control input-sm" id="email_carga" name="email_carga" type="email" autocomplete="off">
													</div>
												</div>
												<div class="col-md-4 col-sm-6 col-xs-12">
													<div class="form-group">
														<label for="tipo_carga">Tipo</label>
														<select id="tipo_carga" name="tipo_carga" class="form-control input-sm" style="padding: 0">
														<option value='Minorista'>Minorista</option>
														<option value='Mayorista'>Mayorista</option>
														</select>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12 col-sm-12 col-xs-12">
													<span id="msjModalAdd"></span>
													<button type="submit" class="btn btn-success btn-md submit_btn" style="float:right">Guardar</button>
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
			
			<!-- MODAL BUSCAR CLIENTE -->
			<div class="modal modal-buscar-cliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
				<div class="modal-dialog modal modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<button aria-label="Close" data-dismiss="modal" class="close" type="button" onclick="javascript:$('#ruc_carga').val('');"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title">Buscar Cliente<a href="#mySmallModalLabel" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
						</div>
						<div class="modal-body">
							<span id="toolbar"><label style="font-size:14px;color:#cd2d1b">Seleccione un cliente de la lista con doble click</label></span>
							<table id="tabla_clientes" data-url="inc/facturar-data.php?q=buscar_razon_social" data-toolbar="#toolbar" data-show-export="false" data-search="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="false" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true" data-single-select="true" data-click-to-select="false"></table>
						</div>
					</div>
				</div>
			</div>
			
			
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
	

	<script>
            function Condicion_factura(){
            con=$("#condicion").val();	
            
            if(con=='credito'){
            $("#agregar_pago").hide();	
            $("#mostrar_vencimiento").show();	
            $ ('#fecha_vencimiento').val (Date.today().add({months:1}).toString("yyyy-MM-dd"));
            }else if(con=='contado'){
            $("#agregar_pago").show();	
            $("#mostrar_vencimiento").hide();	
            }
            }
	</script>
	
	
    <script src="js/menuHover.js"></script>
  </body>
</html>