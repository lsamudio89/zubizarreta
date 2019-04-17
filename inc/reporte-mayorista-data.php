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
		$where_sucursal="AND id_sucursal='$id_sucursal'";
	}
		
	if ($desde=="--" or empty($desde)){
		$desde=date('Y-m-d');	
	}
		
	if ($hasta=="--" or empty($hasta)){
		$hasta=date('Y-m-d');	
	}
			
	$db = DataBase::conectar();
	$q="SELECT ruc, razon_social, FORMAT(sum(cantidad),0,'de_DE') as cantidad, FORMAT(sum(total_costo),0,'de_DE') as costo, FORMAT(sum(total_a_pagar),0,'de_DE') as total_a_pagar, FORMAT(sum(total_a_pagar)-sum(total_costo),0,'de_DE') AS ganancia, tipo_venta FROM facturas WHERE tipo_venta='Mayorista' $where_sucursal AND DATE_FORMAT(fecha, '%Y-%m-%d') BETWEEN '$desde' AND '$hasta' AND estado NOT LIKE 'Anulad%' GROUP BY ruc, razon_social ORDER BY sum(cantidad) DESC";

	$db->setQuery("$q");
	$rows = $db->loadObjectList();
	echo json_encode($rows);
		
?>