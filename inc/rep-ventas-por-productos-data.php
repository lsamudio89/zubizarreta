<?php
	include ("funciones.php");
	verificaLogin();
	$usuario = $_SESSION['usuario'];
	
	$desde=($_REQUEST['desde']);
	$hasta=($_REQUEST['hasta']);
			
	$id_sucursal=$_POST['id_sucursal'];
			
	if ($id_sucursal=="todas"){
		$where_sucursal="";
	}else{
		$where_sucursal="AND f.id_sucursal='$id_sucursal'";
	}
		
	if ($desde=="--" or empty($desde)){
		$desde=date('Y-m-d');	
	}
		
	if ($hasta=="--" or empty($hasta)){
		$hasta=date('Y-m-d');	
	}
			
	$db = DataBase::conectar();
	$q="SELECT p.id_producto, p.producto, SUM(fd.cantidad) AS cantidad, FORMAT(SUM(fd.costo),0,'de_DE') AS costo, FORMAT(SUM(fd.precio_venta),0,'de_DE') AS precio_venta, FORMAT(SUM(fd.total_costo),0,'de_DE') AS total_costo, 
	FORMAT(SUM(fd.total_venta),0,'de_DE') AS total_venta,  FORMAT(SUM(fd.total_venta)-SUM(fd.total_costo),0,'de_DE') as ganancia, f.tipo_venta
		FROM factura_detalle fd INNER JOIN productos p ON p.id_producto=fd.id_producto
		INNER JOIN facturas f ON f.id_factura=fd.id_factura
		WHERE f.estado NOT LIKE 'Anulad%' AND DATE_FORMAT(f.fecha, '%Y-%m-%d') BETWEEN '$desde' AND '$hasta' $where_sucursal
		GROUP BY fd.id_producto, f.tipo_venta";
	$db->setQuery("$q");
	$rows = $db->loadObjectList();
	echo json_encode($rows);
		
?>