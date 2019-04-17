<?php
	include ("inc/funciones.php");
	verificaLogin("");
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_compra_producto= $_REQUEST['id_compra'];
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
	
	//DATOS DE LA CABECERA DE LA compra
	$q="SELECT * FROM compra_productos WHERE id_compra_producto='$id_compra_producto'";
	$db->setQuery("$q");
	$ft = $db->loadObject();
	if (empty($ft)){
		exit;
	}
	$fecha = $ft->fecha;
	$id_sucursal = $ft->id_sucursal;
	$usuario = $ft->usuario;
	
	
	//MONTO TOTAL A LETRAS
	require("inc/numeros-letras.php");		
	$v = new EnLetras();
	$sql="select sum(cantidad*costo) as total,id_sucursal_origen,id_sucursal from compra_detalles where id_compra_producto=$id_compra_producto";
	$r=mysqli_query($db,$sql);
	$row_total=mysqli_fetch_array($r);
	$total_a_pagar=$row_total['total'];
	$id_sucursal_ori=$row_total['id_sucursal_origen'];
	$id_sucursal_des=$row_total['id_sucursal'];
	$iva_10=round($total_a_pagar/11);
	
	//trae datos de sucrusal destino
	$row_sucursal_des=RowMaestro('sucursales','id_sucursal',$id_sucursal_des);
	$sucursal_destino=$row_sucursal_des['sucursal'];
	
	//trae datos de sucursal origen	
	$row_sucursal_ori=RowMaestro('sucursales','id_sucursal',$id_sucursal_ori);
	$sucursal_origen=$row_sucursal_ori['sucursal'];


	function conceptos($id_compra_producto,$item_max){
		$db = DataBase::conectar();
		$db->setQuery("SELECT * FROM compra_detalles WHERE id_compra_producto='$id_compra_producto'");
		$rows = $db->loadObjectList();
		$conceptos="";
		$i=0;
		foreach($rows as $r){
			$i++;
			$cantidad = $r->cantidad;
			$id_producto= $r->id_producto;
			$costo= $r->costo;
			$total_venta = $r->total_venta;
			$total_costo=$cantidad*$costo;
			
			$row_producto=RowMaestro('productos','id_producto',$id_producto);
			$producto=$row_producto['producto'];

			$conceptos .= " <tr>
							<td class='tg-cant'>$cantidad</td>
							<td class='tg-prod'>$producto</td>
							<td class='tg-montos'>".separadorMiles($costo)."</td>
							<td class='tg-montos'></td>
							<td class='tg-montos'></td>
							<td class='tg-montos'>".separadorMiles($total_costo)."</td>
						  </tr>";

		}
		if ($i <$item_max){
			$k = $item_max - $i;
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
<meta charset="utf-8">
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
    <th class="tg-s6z2" colspan="3"><br></th>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3">Fecha: <?php echo fechaLatinaHora($fecha); ?></td>
  </tr>
  <tr>
    <td class="tg-hgcj" colspan="3">REMISION<br></td>
  </tr>
  <tr>
    <td class="tg-gozu" colspan="3" rowspan="2">Asunción - Recalde Conel. Camilo Nº 1466 - Cel.: (0971) 866 893<br>San Lorenzo - María Auxiliadora, Calle Hernandarias c/ 10 de Agosto Nº 149<br>Luque - Av. José Concepción Ortiz c/ Av. Corrales Nº 632</td>
    <td class="tg-4kyz" colspan="3"><?php echo "001-001-$id_compra_producto"; ?></td>
  </tr>
  <tr>
    <td class="tg-izya" colspan="3"></td>
  </tr>
  <tr>
    <td class="tg-yw4l" colspan="6"></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Sucursal Origen: <?php echo $sucursal_origen; ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">Sucursal Destino: <?php echo $sucursal_destino; ?></td>
  </tr>
  <tr>
    <td class="tg-0e45" colspan="6">USUARIO: <?php echo "$usuario"; ?></td>
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
    <td class="tg-214n"></td>
    <td class="tg-214n"></td>
    <td class="tg-214n">Total</td>
  </tr>
  <?php echo conceptos($id_compra_producto,59); ?>
  <tr>
    <td class="tg-0e45" colspan="5">Total: <?php echo $letras; ?></td>
    <td class="tg-total" rowspan="2"><?php echo separadorMiles($total_a_pagar); ?></td>
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
