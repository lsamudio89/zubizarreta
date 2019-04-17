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
	$q="SELECT f.fecha, fd.id_producto, fd.producto, SUM(fd.cantidad) AS cantidad, FORMAT(SUM(fd.total_venta),0,'de_DE') AS total_venta FROM factura_detalle fd
		LEFT JOIN facturas f ON f.id_factura=fd.id_factura WHERE DATE_FORMAT(f.fecha, '%Y-%m-%d') BETWEEN '$desde' AND '$hasta' 
		$where_sucursal AND f.estado NOT LIKE 'Anulad%' GROUP BY fd.id_producto ORDER BY cantidad DESC;";
	$db->setQuery("$q");
	$rows = $db->loadObjectList();
	echo json_encode($rows);
		
?>