<?php
	include ("inc/funciones.php");
	verificaLogin("");
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_nota = $_REQUEST['id_nota'];
	$db = DataBase::conectar();

	//DATOS DEL TIMBRADO
	$db->setQuery("SELECT * FROM timbrados WHERE id_sucursal='$id_sucursal' AND estado='Activo'");
	$tim = $db->loadObject();
	$timbrado = $tim->timbrado;
	$cod_est = $tim->cod_establecimiento;
	$punto_exp = $tim->punto_de_expedicion;
	$ini_vigencia = fechaLatina($tim->inicio_vigencia);
	$fin_vigencia = fechaLatina($tim->fin_vigencia);
	$ruc_empresa = $tim->ruc;
	
	//DATOS DE LA CABECERA DE LA nota
	$db->setQuery("SELECT * FROM notas_cr WHERE id_nota='$id_nota'");
	$ft = $db->loadObject();
	if (empty($ft)){
		exit;
	}
	$nro_ft = $ft->nro_nota;
	$fecha = $ft->fecha;
	$ruc = $ft->ruc;
	$razon_social = $ft->razon_social;
	$nro_factura = $ft->nro_factura;
	$total_a_pagar = $ft->total;
	
	//MONTO TOTAL A LETRAS
	require("inc/numeros-letras.php");		
	$v = new EnLetras();
	$letras = strtoupper($v->ValorEnLetras($ft->total,""));
	$iva_10=round($total_a_pagar/11);


	function conceptos($id_nota){
		$db = DataBase::conectar();
		$db->setQuery("SELECT cantidad, producto, precio_venta, total_venta FROM notas_cr_detalle WHERE id_nota='$id_nota'");
		$rows = $db->loadObjectList();
		$conceptos="";
		$i=0;
		foreach($rows as $r){
			$i++;
			$cantidad = $r->cantidad;
			$producto = $r->producto;
			$precio_unitario = $r->precio_venta;
			$total_venta = $r->total_venta;

			$conceptos .= " <tr>
							<td class='tg-cant'>$cantidad</td>
							<td class='tg-prod'>$producto</td>
							<td class='tg-montos'>".separadorMiles($precio_unitario)."</td>
							<td class='tg-montos'>-</td>
							<td class='tg-montos'>-</td>
							<td class='tg-montos'>".separadorMiles($total_venta)."</td>
						  </tr>";

		}
		if ($i < 19){
			$k = 19 - $i;
			for ($j=1; $j<=$k; $j++) {
				$conceptos .= "<tr>
					<td class='tg-cant'>&nbsp;</td>
					<td class='tg-prod'>&nbsp;<br></td>
					<td class='tg-montos'>&nbsp;<br></td>
					<td class='tg-montos'>&nbsp;<br></td>
					<td class='tg-montos'>&nbsp;<br></td>
					<td class='tg-montos'>&nbsp;<br></td>
				 </tr>";
			}
		}
		
		return $conceptos;
		
	}
?>


<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;margin:0px auto;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:1px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:1px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg .tg-s6z2{text-align:center;border-bottom:none}
.tg .tg-214n{font-size:11px;text-align:center}
.tg .tg-x361{font-style:italic;font-size:11px;text-align:center;border-bottom:none;padding-top:4px}
.tg .tg-0e45{font-size:11px}
.tg .tg-cant{font-size:12px;text-align:center;border-bottom:none;border-top:none}
.tg .tg-prod{font-size:12px;border-bottom:none;border-top:none}
.tg .tg-montos{font-size:12px;text-align:right;border-bottom:none;border-top:none}
.tg .tg-total{font-size:12px;text-align:right;}
.tg .tg-gozu{font-size:10px;text-align:center;border-top:none;border-bottom:none}
.tg .tg-hgcj{font-weight:bold;text-align:center;border-top:none;border-bottom:none}
.tg .tg-4kyz{font-size:20px;text-align:center;border-top:none;border-bottom:none}
.tg .tg-izya{font-size:18px;text-align:center;border-top:none;}
.tg .tg-yw4l{vertical-align:top;border-left:none;border-right:none}
.tg .tg-copia{font-size:8px;text-align:right;border:none}
.imagen { float: left; } 
.logo { height: 50px; margin:5px 5px 5px 20px; width: auto; }
#jose{ font-size:14px; }
#span_liq{ margin-right:150px; }
#span_iva10{ margin-right:150px; }
.condicion{font-weight:bold;font-size:12px}
</style>
<table class="tg" style="undefined;table-layout: fixed; width: 786px">
<colgroup>
<col style="width: 66px">
<col style="width: 345px">
<col style="width: 93px">
<col style="width: 93px">
<col style="width: 93px">
<col style="width: 96px">
</colgroup>
  <tr>
    <th class="tg-x361" colspan="3" rowspan="3"><div class="imagen"><img class='logo' src='images/logo-factura.png'></div><center><strong id="jose">José Segundo Decoud.</strong><br><strong>Servicios Personales.<br>Venta al por mayor y menor de todo tipo de partes, piezas y accesorios para vehículos</strong></th>
    <th class="tg-s6z2" colspan="3">TIMBRADO Nº.: <?php echo $timbrado; ?><br></th>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3">Válido desde el <?php echo $ini_vigencia; ?> hasta el <?php echo $fin_vigencia; ?></td>
  </tr>
  <tr>
    <td class="tg-hgcj" colspan="3">RUC: <?php echo $ruc_empresa; ?><br></td>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3" rowspan="2">Asunción - Recalde Conel. Camilo Nº 1466 - Cel.: (0971) 866 893<br>San Lorenzo - María Auxiliadora, Calle Hernandarias c/ 10 de Agosto Nº 149<br>Luque - Av. José Concepción Ortiz c/ Av. Corrales Nº 632</td>
    <td class="tg-4kyz" colspan="3">Nota de Credito</td>
  </tr>
  <tr>
    <td class="tg-izya" colspan="3"><?php echo $cod_est."-".$punto_exp."-".$nro_ft; ?></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="6"></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Fecha: <?php echo fechaLatinaHora($fecha); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">RUC / CI: <?php echo $ruc; ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Nombre o Razón Social: <?php echo strtoupper($razon_social); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">FACTURA Nº: <?php echo "001-001-$nro_factura"; ?></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="6"></td>
  </tr>
  <tr>
    <td class="tg-214n" rowspan="2">Cant.</td>
    <td class="tg-214n" rowspan="2">Descripción</td>
    <td class="tg-214n" rowspan="2">Precio<br>Unitario</td>
    <td class="tg-214n" colspan="3">Valor de Venta<br></td>
  </tr>
  <tr>
    <td class="tg-214n">Exentas</td>
    <td class="tg-214n">5%</td>
    <td class="tg-214n">10%</td>
  </tr>
  <?php echo conceptos($id_nota); ?>
  <tr>
    <td class="tg-0e45" colspan="3">Subtotales:</td>
    <td class="tg-total"></td>
    <td class="tg-total"></td>
    <td class="tg-total"><?php echo separadorMiles($total_a_pagar); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="5">Total Credito: <?php echo $letras; ?></td>
    <td class="tg-total" rowspan="2"><?php echo separadorMiles($total_a_pagar); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="5"><span id="span_liq">Liquidación del IVA: (5%) - </span><span id="span_iva10">(10%) <?php echo separadorMiles($iva_10); ?></span><span id="span_totaliva">Total IVA: <?php echo separadorMiles($iva_10); ?></span><br></td>
  </tr>
  <tr><td colspan="6" class="tg-copia">Original: CLIENTE</td></tr>
</table>
<br><br>
<table class="tg" style="undefined;table-layout: fixed; width: 786px">
<colgroup>
<col style="width: 66px">
<col style="width: 345px">
<col style="width: 93px">
<col style="width: 93px">
<col style="width: 93px">
<col style="width: 96px">
</colgroup>
  <tr>
    <th class="tg-x361" colspan="3" rowspan="3"><div class="imagen"><img class='logo' src='images/logo-factura.png'></div><center><strong id="jose">José Segundo Decoud.</strong><br><strong>Servicios Personales.<br>Venta al por mayor y menor de todo tipo de partes, piezas y accesorios para vehículos</strong></th>
    <th class="tg-s6z2" colspan="3">TIMBRADO Nº.: <?php echo $timbrado; ?><br></th>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3">Válido desde el <?php echo $ini_vigencia; ?> hasta el <?php echo $fin_vigencia; ?></td>
  </tr>
  <tr>
    <td class="tg-hgcj" colspan="3">RUC: <?php echo $ruc_empresa; ?><br></td>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3" rowspan="2">Asunción - Recalde Conel. Camilo Nº 1466 - Cel.: (0971) 866 893<br>San Lorenzo - María Auxiliadora, Calle Hernandarias c/ 10 de Agosto Nº 149<br>Luque - Av. José Concepción Ortiz c/ Av. Corrales Nº 632</td>
    <td class="tg-4kyz" colspan="3">Nota de Credito</td>
  </tr>
  <tr>
    <td class="tg-izya" colspan="3"><?php echo $cod_est."-".$punto_exp."-".$nro_ft; ?></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="6"></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Fecha: <?php echo fechaLatinaHora($fecha); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">RUC / CI: <?php echo $ruc; ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Nombre o Razón Social: <?php echo strtoupper($razon_social); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">FACTURA Nº: <?php echo "001-001-$nro_factura"; ?></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="6"></td>
  </tr>
  <tr>
    <td class="tg-214n" rowspan="2">Cant.</td>
    <td class="tg-214n" rowspan="2">Descripción</td>
    <td class="tg-214n" rowspan="2">Precio<br>Unitario</td>
    <td class="tg-214n" colspan="3">Valor de Venta<br></td>
  </tr>
  <tr>
    <td class="tg-214n">Exentas</td>
    <td class="tg-214n">5%</td>
    <td class="tg-214n">10%</td>
  </tr>
  <?php echo conceptos($id_nota); ?>
  <tr>
    <td class="tg-0e45" colspan="3">Subtotales:</td>
    <td class="tg-total"></td>
    <td class="tg-total"></td>
    <td class="tg-total"><?php echo separadorMiles($total_a_pagar); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="5">Total Credito: <?php echo $letras; ?></td>
    <td class="tg-total" rowspan="2"><?php echo separadorMiles($total_a_pagar); ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="5"><span id="span_liq">Liquidación del IVA: (5%) - </span><span id="span_iva10">(10%) <?php echo separadorMiles($iva_10); ?></span><span id="span_totaliva">Total IVA: <?php echo separadorMiles($iva_10); ?></span><br></td>
  </tr>
  <tr><td colspan="6" class="tg-copia">Duplicado: ARCH. TRIBUTARIO</td></tr>
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
