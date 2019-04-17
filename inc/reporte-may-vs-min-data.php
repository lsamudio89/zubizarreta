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
	$db->setQuery("SELECT s.sucursal, f.tipo_venta, FORMAT(sum(f.total_costo),0,'de_DE') as total_costo, FORMAT(sum(f.total_a_pagar),0,'de_DE') as total_a_pagar, 
						FORMAT(sum(f.total_a_pagar-f.total_costo),0,'de_DE') AS ganancias FROM facturas f INNER JOIN sucursales s ON s.id_sucursal=f.id_sucursal 
						WHERE f.estado NOT LIKE 'Anulad%' AND DATE_FORMAT(f.fecha, '%Y-%m-%d') BETWEEN '$desde' AND '$hasta' $where_sucursal
						GROUP BY f.tipo_venta, f.id_sucursal ORDER BY f.tipo_venta");
	$rows = $db->loadObjectList();

	echo json_encode($rows);
		
?>