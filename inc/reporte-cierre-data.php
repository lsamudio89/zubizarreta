<?php
include("funciones.php");
verificaLogin();
$q = $_REQUEST['q'];
$usuario = $_SESSION['usuario'];
$id_sucursal = $_SESSION['id_sucursal'];

switch ($q) {
	case 'ver':
	$db = DataBase::conectar();
	$fecha = $_REQUEST['fecha'];
	$id_sucursal_superadmin = $_REQUEST['id_sucursal'];
	if ($id_sucursal_superadmin) {
		$id_sucursal = $id_sucursal_superadmin;
	}
	if (empty($fecha)) {
		$fecha = date('Y-m-d');
	}
	$desde = "$fecha 00:00:00";
	$hasta = "$fecha 23:59:59";
	$total_efectivo = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo'),0)";
	$total_peso = "IFNULL((select sum(monto_conversion) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-peso'),0)";
	$total_real = "IFNULL((select sum(monto_conversion) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-real'),0)";
	$total_usd = "IFNULL((select sum(monto_conversion) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-usd'),0)";
	$total_tarjeta = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago like '%Tarjeta%'),0)";
	$total_cheque = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Cheque'),0)";
	$total_giro = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago like 'Giro%'),0)";
	$total_descuento = "IFNULL((select sum(abs(monto)) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago = 'Descuento'),0)";
	$total_nota = "IFNULL((select sum(abs(monto)) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago = 'Nota'),0)";
	
	//si no es super admin muestra solo los que el facturo
	$row_usuario=RowMaestro('usuarios','nombre_usuario',$usuario);
	$rol=$row_usuario['rol'];
	
	if ($rol==1){
	//muestra todos	
	$ex="";	
	}else{
	//muestra solo los que el facturo
	$ex="and usuario='$usuario'";	
	}
	
	$q = "SELECT *, $total_efectivo as total_efectivo, $total_peso as total_peso, $total_real as total_real, $total_usd as total_usd, $total_tarjeta as total_tarjeta, $total_cheque as total_cheque, $total_giro as total_giro, $total_descuento as total_descuento, $total_nota as total_nota, (select group_concat(metodo_pago separator ' / ') AS metodo_pago FROM pagos where id_factura=f.id_factura) as metodo_pago
	FROM facturas f where fecha>='$desde' and fecha<='$hasta' and f.id_sucursal='$id_sucursal' AND f.estado NOT LIKE 'Anulad%' $ex ORDER BY f.id_factura";
	$db->setQuery("$q");
	$rows = $db->loadObjectList();
	echo json_encode($rows);
	break;
}

function ResumenTotales($fecha, $id_sucursal,$usuario){
	
	$desde = "$fecha 00:00:00";
	$hasta = "$fecha 23:59:59";
	$total_efectivo = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo' and p.monto>0),0)";
		$total_efectivo_peso = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-peso'),0)";
	$qtotal_efectivo_peso2 = "IFNULL((select sum(monto_conversion) as monto_conversion from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-peso'),0)";
	$total_efectivo_usd = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-usd'),0)";
	$qtotal_efectivo_usd2 = "IFNULL((select sum(monto_conversion) as monto_conversion from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-usd'),0)";
	$total_efectivo_real = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-real'),0)";
		$qtotal_efectivo_real2 = "IFNULL((select sum(monto_conversion) as monto_conversion from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Efectivo-real'),0)";
	$total_tarjeta = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago like '%Tarjeta%'),0)";
	$total_cheque = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago='Cheque'),0)";
	$total_giro = "IFNULL((select sum(monto) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago like 'Giro%'),0)";
	$total_descuento = "IFNULL((select sum(abs(monto)) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago = 'Descuento'),0)";
	$total_nota = "IFNULL((select sum(abs(monto)) as monto from pagos p where f.id_factura=p.id_factura and p.metodo_pago = 'Nota'),0)";
	
	//si no es super admin muestra solo los que el facturo
	$row_usuario=RowMaestro('usuarios','nombre_usuario',$usuario);
	$rol=$row_usuario['rol'];
	
	if ($rol==1){
	//muestra todos	
		$ex="";	
	}else{
	//muestra solo los que el facturo
		$ex="and usuario='$usuario'";	
	}
	
	$q = "SELECT *, 
	$total_efectivo as total_efectivo, 
	$total_efectivo_peso as total_efectivo_peso,
	$qtotal_efectivo_peso2 as total_efectivo_peso2,  
	$total_efectivo_usd as total_efectivo_usd, 
	$qtotal_efectivo_usd2 as total_efectivo_usd2, 
	$total_efectivo_real as total_efectivo_real,
	$qtotal_efectivo_real2 as total_efectivo_real2,
	$total_tarjeta as total_tarjeta, 
	$total_cheque as total_cheque, 
	$total_giro as total_giro, 
	$total_descuento as total_descuento, 
	$total_nota as total_nota
	FROM facturas f WHERE DATE_FORMAT(f.fecha,'%Y-%m-%d')='$fecha' and f.id_sucursal='$id_sucursal' $ex ORDER BY f.id_factura";
	$db = DataBase::conectar();
	$r = mysqli_query($db, $q);
	echo mysqli_error($db);
	echo "<table class='table table-striped' style='width:40%;'>";

	while ($row = mysqli_fetch_array($r)) {
		$total_efectivo = $row['total_efectivo'];
		$total_efectivo_peso = $row['total_efectivo_peso'];
		$total_efectivo_real = $row['total_efectivo_real'];
		$total_efectivo_usd = $row['total_efectivo_usd'];
		$total_cheque = $row['total_cheque'];
		$total_tarjeta = $row['total_tarjeta'];
		$total_giro = $row['total_giro'];
		$total_descuento = $row['total_descuento'];
		$total_nota = $row['total_nota'];
		
		$sum_total_efectivo_peso2 += $row['total_efectivo_peso2'];
		$sum_total_efectivo_real2 += $row['total_efectivo_real2'];
		$sum_total_efectivo_usd2 += $row['total_efectivo_usd2'];
		
		$sum_total_efectivo = $sum_total_efectivo + $total_efectivo;
		$sum_total_efectivo_peso = $sum_total_efectivo_peso + $total_efectivo_peso;
		$sum_total_efectivo_real = $sum_total_efectivo_real + $total_efectivo_real;
		$sum_total_efectivo_usd = $sum_total_efectivo_usd + $total_efectivo_usd;
		$sum_total_cheque = $sum_total_cheque + $total_cheque;
		$sum_total_tarjeta = $sum_total_tarjeta + $total_tarjeta;
		$sum_total_giro = $sum_total_giro + $total_giro;
		$sum_total_descuento = $sum_total_descuento + $total_descuento;
		$sum_total_nota = $sum_total_nota + $total_nota;
	}
	
	$sum_total_efectivo_punto = poner_puntos($sum_total_efectivo);
	$sum_total_efectivo_peso_punto = poner_puntos($sum_total_efectivo_peso);
	$sum_total_efectivo_real_punto = poner_puntos($sum_total_efectivo_real);
	$sum_total_efectivo_usd_punto = poner_puntos($sum_total_efectivo_usd);
	$sum_total_cheque_punto = poner_puntos($sum_total_cheque);
	$sum_total_tarjeta_punto = poner_puntos($sum_total_tarjeta);
	$sum_total_giro_punto = poner_puntos($sum_total_giro);
	$sum_total_descuento_punto = poner_puntos($sum_total_descuento);
	$sum_total_nota_punto = poner_puntos($sum_total_nota);
	
	$db->setQuery("SELECT IFNULL(SUM(monto),0) AS total_vales FROM vales WHERE id_sucursal=$id_sucursal AND fecha LIKE '$fecha%' AND estado !='Anulado' $ex");
	$row = $db->loadObject();
	$total_vales = poner_puntos($row->total_vales);
	
	$db->setQuery("SELECT IFNULL(SUM(p.monto),0) AS total_anulados FROM pagos p INNER JOIN facturas f ON f.id_factura=p.id_factura WHERE f.id_sucursal=$id_sucursal AND DATE_FORMAT(p.fecha,'%Y-%m-%d')='$fecha' AND f.estado LIKE 'Anulad%'");
	$row = $db->loadObject();
	$total_anulados = poner_puntos($row->total_anulados);
	
	$db->setQuery("SELECT IFNULL(SUM(monto),0) AS total_cobro FROM pagos WHERE id_sucursal=$id_sucursal and pago_cr=1 AND fecha LIKE '$fecha%' $ex");
	$row = $db->loadObject();
	$total_cobro=($row->total_cobro);
	$total_cobro_punto= poner_puntos($row->total_cobro);
	
	
	$sum_total_venta_punto = poner_puntos($sum_total_efectivo + $sum_total_efectivo_peso + $sum_total_efectivo_real + $sum_total_efectivo_usd + $sum_total_cheque + $sum_total_tarjeta + $sum_total_giro + $sum_total_nota + $sum_total_anulado+ $total_cobro);
	
	echo "
	<tr>
	<th>Total Efectivo Gs.:</th>
	<td style='text-align:right;'>$sum_total_efectivo_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<tr>
	<th>Total Efectivo Cobro Credito:</th>
	<td style='text-align:right;'>$total_cobro_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Efectivo Peso:</th>
	<td style='text-align:right;'>$sum_total_efectivo_peso_punto</td>
	<td style='text-align:right;'>$ ".poner_puntos($sum_total_efectivo_peso2)."</td>
	</tr>
	<tr>
	<th>Total Efectivo Real:</th>
	<td style='text-align:right;'>$sum_total_efectivo_real_punto</td>
	<td style='text-align:right;'>Rs.".poner_puntos($sum_total_efectivo_real2)."</td>
	</tr>
	<tr>
	<th>Total Efectivo Dolar:</th>
	<td style='text-align:right;'>$sum_total_efectivo_usd_punto</td>
	<td style='text-align:right;'>Usd.".poner_puntos($sum_total_efectivo_usd2)."</td>
	</tr>
	<tr>
	<th>Total Tarjeta:</th>
	<td style='text-align:right;'>$sum_total_tarjeta_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Cheques:</th>
	<td style='text-align:right;'>$sum_total_cheque_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Giros:</th>
	<td style='text-align:right;'>$sum_total_giro_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Vales:</th>
	<td style='text-align:right;'>$total_vales</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Descuentos:</th>
	<td style='text-align:right;'>$sum_total_descuento_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<th>Total Anulados:</th>
	<td style='text-align:right;'>$total_anulados</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr>
	<th>Total Nota CR:</th>
	<td style='text-align:right;'>$sum_total_nota_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	<tr class='success'>
	<th>Total Ventas</th>
	<td style='text-align:right;'>$sum_total_venta_punto</td>
	<td style='text-align:right;'></td>
	</tr>
	";
	echo "</table>";
}

?>
