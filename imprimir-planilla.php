<?php
  include ("inc/funciones.php");
  //$pag = basename($_SERVER['PHP_SELF']);
  verificaLogin();
  setlocale(LC_ALL,"es_ES");
  $id_usuario = $_SESSION['id_usuario'];
  $rol = datosUsuario($id_usuario)->rol;
  
  function Items($id){

$q="select * from factura_detalle where id_factura='$id' order by producto ASC";
$db = DataBase::conectar();
$r=mysqli_query($db,$q);
echo mysqli_error($db);

$row_factura=RowMaestro('facturas','id_factura',$id);
$tipo_venta=$row_factura['tipo_venta'];
$razon_social=$row_factura['razon_social'];
$numero=$row_factura['numero'];
$total_a_pagar=poner_puntos($row_factura['total_a_pagar']);
$descuento_factura=poner_puntos($row_factura['descuento']);



if ($tipo_venta=='Mayorista'){
$th_mayorista="<th style='text-align:right;'>Mayorista</th>";	
$th_obs="<th style='text-align:right;'>$tipo_venta</th>";
}else{
$th_mayorista="";
$th_obs="";	
}

echo "
<table class='table table-bordered' style='width:100%;'>

<tr>
<th>Nro:</th>
<td style='text-align:center;' colspan=3>$numero</td>
$th_mayorista
</tr>

<tr>
<th>Cliente:</th>
<td style='text-align:center;' colspan=4>$razon_social</td>
</tr>

<tr>
<th>Producto</th>
<th style='text-align:right;'>Precio Normal</th>
$th_mayorista
<th style='text-align:center;'>Cantidad</th>
<th style='text-align:right;'>Totales</th>
</tr>
";
while ($row=mysqli_fetch_array($r)){
$id_factura_detalle=$row['id_factura_detalle'];
$producto=$row['producto'];
$cantidad=$row['cantidad'];
$precio_venta=$row['precio_venta'];
$id_producto=$row['id_producto'];
$total=$cantidad*$precio_venta;
$precio_venta_punto=poner_puntos($precio_venta);
$total_punto=poner_puntos($total);

$totales_punto=poner_puntos($precio_venta*$cantidad);

if ($tipo_venta=="Mayorista"){
$td_mayorista="<td style='text-align:right;'>$precio_venta_punto</td>";

// se debe crear un campo precio_vta_min dentro de la tabla factura_detalle para saber cual era el precio normal en ese momento
// ahora voy a estirar el precio_vta_min de la tabla producto por el momento.. para mostrar cuanto estaba y cuanto se le cobro

$row_producto=RowMaestro('productos','id_producto',$id_producto);
$precio_vta_min_punto=poner_puntos($row_producto['precio_vta_min']);
$precio_vta_min=$row_producto['precio_vta_min'];
$colspan=1;
$th="<th></th>";
}else{
$precio_vta_min=$precio_venta;	
$precio_vta_min_punto=$precio_venta_punto;	
$colspan=0;
$th="";
}
	echo "
	<tr>
	<td>$producto</th>
	<td style='text-align:right;'>$precio_vta_min_punto</td>
	$td_mayorista
	<td style='text-align:center;'>$cantidad</td>
	<td style='text-align:right;'>$totales_punto</td>
	</tr>
	";	
	
	$normal=$normal+($precio_vta_min*$cantidad);
	$mayorista=$mayorista+($precio_venta*$cantidad);
}



if ($descuento_factura<0){
	echo "
	<tr>
	<th  style='text-align:center;' colspan=4>DESCUENTO</th>
	<td style='text-align:right;'>$descuento_factura</td>
	</tr>
	";	
}



$normal_punto=poner_puntos($normal);
$mayorista_punto=poner_puntos($mayorista);

	echo "
	<tr>
	<th style='text-align:center;' colspan=$colspan>TOTAL A PAGAR</th>
	<th style='text-align:right;'>&nbsp;</th>
	<th style='text-align:right;'>&nbsp;</th>
	$th
	<th style='text-align:right;'>$total_a_pagar</th>
	</tr>
	";	
echo "</table>";

$descuento=$normal-$mayorista;
$descuento_punto=poner_puntos($descuento);

if ($descuento>0){
echo "<h3 style='text-align: right;'>AHORRO: $descuento_punto</h3>";	
}

}
 
?>
<link rel="stylesheet" href="css/bootstrap.min.css">
<center><img src="images/logo.jpg" width="30%"></center>
<?php
$id=$_REQUEST['id'];
Items($id);
?>
<script>
window.print(); 
window.opener.location.reload();
</script>
