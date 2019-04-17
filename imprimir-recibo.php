<?php
  include ("inc/funciones.php");
  //$pag = basename($_SERVER['PHP_SELF']);
  verificaLogin();
  setlocale(LC_ALL,"es_ES");
  $id_pago=$_REQUEST['id'];
  
  $row_pago=RowMaestro('pagos','id_pago',$id_pago);
  $razon_social=$row_pago['razon_social'];
  $fecha_pago=fechaLatina($row_pago['fecha']);
  $hora_pago=$row_pago['hora'];
  $id_factura=$row_pago['id_factura'];
  $monto=poner_puntos($row_pago['monto']);
  
  $rowf=RowMaestro('facturas','id_factura',"$id_factura");
  $vencimiento=fechaLatina($rowf['fecha_vencimiento']);
  $usuario=$rowf['usuario'];
  $razon_social=$rowf['razon_social'];
  $ruc=$rowf['ruc'];
  $saldo=poner_puntos($rowf['saldo']);
  $total=poner_puntos($rowf['total_a_pagar']);
  
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
<link href="css/factura.css" rel="stylesheet" media="print">
<link href="css/factura.css" rel="stylesheet">
</head>
<style>
.tg td {
    font-family: monospace;
    border-style: inherit;
}
</style>
<table class="tg" style="width: 100%;     border-spacing: 0; margin: 0px auto;" border="0">

<tr>
<td style="text-align:center; border-style: inherit;" colspan="2">JDM</td>
</tr>
<tr>
<td colspan="2" style="border-top:2px dashed black;">Recibo Nro: <?php echo $id_pago;?></td>
</tr>
<tr>
<td style="border-style: inherit;">Fecha: <?php echo $fecha_pago;?></td>
<td style="border-style: inherit; text-align:right;">Hora: <?php echo $hora_pago;?></td>
</tr>
<tr>
<td colspan="2" style="  border-left: 0px; border-right: 0;">Ruc: <?php echo $ruc;?></td>
</tr>
<tr>
<td colspan="2" style="border-bottom:2px dashed black; border-top: 0px;  border-left: 0px; border-right: 0;">Cliente: <?php echo $razon_social;?></td>
</tr>
<tr>
<td colspan="2" style="border-style: inherit;">Factura Nro: <?php echo $id_factura;?></td>
</tr>
<tr>
<td colspan="2" style="border-style: inherit;">Monto Factura: <?php echo $total;?></td>
</tr>
<tr>
<td colspan="2" style="border-style: inherit;">Importe: <?php echo $monto;?></td>
</tr>
<tr>
<td colspan="2" style="border-style: inherit;">Saldo: <?php echo $saldo;?></td>
</tr>
<tr>
<td colspan="2" style="border-top:2px dashed black; border-bottom:2px dashed black;">Atendido por: <?php echo $usuario;?></td>
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

</html>