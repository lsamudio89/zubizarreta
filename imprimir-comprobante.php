<?php
	include ("inc/funciones.php");
	verificaLogin("");
	$id_factura = $_REQUEST['id'];
	
	//DATOS DE LA CABECERA DE LA FACTURA
	$db = DataBase::conectar();
	$db->setQuery("SELECT * FROM facturas WHERE id_factura='$id_factura'");
	$ft = $db->loadObject();
	if (empty($ft)){
		exit;
	}
	$nro_ft = $ft->numero;
	$fecha = $ft->fecha;
	$ruc = $ft->ruc;
	$razon_social = $ft->razon_social;
	$total_a_pagar = $ft->total_a_pagar;
	$tipo_venta = $ft->tipo_venta;
	
	//PRODUCTOS
	$db->setQuery("SELECT fd.cantidad, fd.producto, fd.precio_venta, p.precio_vta_min, fd.total_venta FROM factura_detalle fd LEFT JOIN productos p ON fd.id_producto=p.id_producto WHERE fd.id_factura='$id_factura'");
	$rows = $db->loadObjectList();
	$conceptos="";
	$i=0;
	$total_precio_normal=0;
	foreach($rows as $r){
		$i++;
		$cantidad = $r->cantidad;
		$producto = $r->producto;
		$precio_unitario = $r->precio_venta;
		$total_venta = $r->total_venta;
		$precio_vta_min = $r->precio_vta_min;
		$total_precio_normal += ($cantidad*$precio_vta_min);
		$conceptos .= "<tr>
			<td class='tg-cantidad'>$cantidad</td>
			<td class='tg-producto'>$producto</td>";
		if ($tipo_venta=="Mayorista"){
			$conceptos .= "<td class='tg-precio'>".separadorMiles($precio_unitario)."</td>";
			$conceptos .= "<td class='tg-precio'>".separadorMiles($precio_vta_min)."</td>";

		}else{
			$conceptos .= "<td class='tg-precio' colspan='2'>".separadorMiles($precio_unitario)."</td>";
		}
		$conceptos .= "<td class='tg-precio' colspan='2'>".separadorMiles($total_venta)."</td>";
	}
			
	if ($tipo_venta=="Mayorista"){
		$titulo = "PLANILLA<br>MAYORISTA";
		$mayorista_titulo = "<td class='tg-pi53'>Precio Mayorista<br></td><td class='tg-pi53'>Precio Normal</td>";
		$descuento_titulo = "Ahorro:";
		$descuento = $total_precio_normal-$total_a_pagar;
	}else{
		$titulo = "COMPROBANTE";
		$mayorista_titulo = "<td class='tg-pi53' colspan='2'>Precio Unitario</td>";
		if (!empty($ft->descuento)){
			$descuento_titulo = "Descuento:";
			$descuento = $ft->descuento;
		}
	}

?>
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:1px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:1px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg .tg-precio{font-size:12px;text-align:right;border-top:none;border-bottom:none}
.tg .tg-x361{font-style:italic;font-size:12px;text-align:center}
.tg .tg-cantidad{font-size:12px;text-align:center;border-top:none;border-bottom:none}
.tg .tg-producto{font-size:12px;border-top:none;border-bottom:none}
.tg .tg-montos{font-size:12px;text-align:right}
.tg .tg-yw4l{vertical-align:top;border-bottom:none;border-right:none;border-left:none}
.tg .tg-izya{font-size:18px;text-align:center;border-top:none;border-bottom:none}
.tg .tg-gozu{font-size:10px;text-align:center}
.tg .tg-13pz{font-size:18px;text-align:center;vertical-align:top;border-top:none}
.tg .tg-3sk9{font-weight:bold;font-size:12px}
.tg .tg-pi53{font-weight:bold;font-size:12px;text-align:center}
.logo { height: 50px; margin:5px 5px 5px 20px; width: auto; }
.normal{font-weight:normal}

</style>
<table class="tg" style="undefined;table-layout: fixed; width: 725px">
<colgroup>
<col style="width: 96px">
<col style="width: 345px">
<col style="width: 93px">
<col style="width: 93px">
<col style="width: 98px">
</colgroup>
  <tr>
    <th class="tg-x361" colspan="3" rowspan="3"><div class="imagen"><img class='logo' src='images/logo-factura.png'></div></th>
    <th class="tg-yw4l" colspan="2"></th>
  </tr>
  <tr>
    <td class="tg-izya" colspan="2" rowspan="2"><?php echo $titulo; ?></td>
  </tr>
  <tr>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3" rowspan="2">Asunción - Recalde Conel. Camilo Nº 1466 - Cel.: (0971) 866 893<br>San Lorenzo - María Auxiliadora, Calle Hernandarias c/ 10 de Agosto Nº 149<br>Luque - Av. José Concepción Ortiz c/ Av. Corrales Nº 632</td>
    <td class="tg-13pz" colspan="2" rowspan="2">Nº <?php echo $nro_ft; ?></td>
  </tr>
  <tr>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="5"></td>
  </tr>
  <tr>
    <td class="tg-3sk9" colspan="5">Fecha: <span class="normal"><?php echo fechaLatinaHora($fecha); ?></span></td>
  </tr>
  <tr>
    <td class="tg-3sk9" colspan="5">Cliente: <span class="normal"><?php echo $razon_social; ?></span></td>
  </tr>
  <tr>
    <td class="tg-3sk9" colspan="5">RUC / CI: <span class="normal"><?php echo $ruc; ?></span></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="5"></td>
  </tr>
  <tr>
    <td class="tg-pi53">Cantidad</td>
    <td class="tg-pi53">Producto</td>
    <?php echo $mayorista_titulo; ?>
    <td class="tg-pi53">Total</td>
  </tr>
  <?php echo $conceptos; ?>
  <tr>
    <td class="tg-3sk9" colspan="4"><?php echo $descuento_titulo; ?></td>
    <td class="tg-montos"><?php echo separadorMiles($descuento); ?></td>
  </tr>
  <tr>
    <td class="tg-3sk9" colspan="4">Total a Pagar:<br></td>
    <td class="tg-montos"><?php echo separadorMiles($total_a_pagar); ?></td>
  </tr>
</table>

<script>
	var imprimir='<?php echo $_REQUEST['imprimir']; ?>';
	var recargar='<?php echo $_REQUEST['recargar']; ?>';
	if (imprimir=="si"){
		window.print();
	}
	if (recargar=="si"){
		window.onunload = refreshParent;
		function refreshParent() {
			window.opener.location.reload();
		}
	}
</script>