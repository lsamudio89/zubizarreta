<?php
	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];

	switch ($q){
		case 'ver':
			
			$desde=($_REQUEST['desde']);
			$hasta=($_REQUEST['hasta']);
			
			$id_sucursal=$_POST['id_sucursal'];
			
			if (empty($id_sucursal)){
			$id_sucursal=$_SESSION['id_sucursal'];	
			}
			
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
			
			$hasta="$hasta 23:59:59";

			
			$db = DataBase::conectar();
		   //$q="SELECT *, f.total_a_pagar-f.total_costo as ganancia FROM facturas f, sucursales s where fecha>='$desde' and fecha<='$hasta' and f.id_sucursal=s.id_sucursal and f.id_sucursal='$id_sucursal' ORDER BY id_factura DESC";
			$query="SELECT f.id_factura, 
							s.sucursal, 
							f.numero, 
							DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') as fecha, 
							f.condicion_venta, 
							f.razon_social, 
							f.tipo_venta, 
							f.ruc, 
							
							(f.total_a_pagar+abs(f.descuento)) AS total_ventas, 
							
							SUM(p.monto) as pagos, 
							f.total_costo, 
							f.descuento, 
							IFNULL(SUM(p.comision_tarj),0) AS comision_tarj, 
							
			IF((SELECT metodo_pago FROM pagos p WHERE id_factura=f.id_factura AND metodo_pago='Nota' LIMIT 1)='Nota',f.total_a_pagar+abs(f.descuento)-f.total_costo-IFNULL(SUM(p.comision_tarj),0),f.total_a_pagar-f.total_costo-IFNULL(SUM(p.comision_tarj),0)) AS ganancia, 
							
							f.usuario
				FROM facturas f LEFT JOIN pagos p ON f.id_factura=p.id_factura INNER JOIN sucursales s ON s.id_sucursal=f.id_sucursal 
				WHERE p.metodo_pago != 'Descuento' AND 
				f.fecha>='$desde' and f.fecha<='$hasta' AND f.estado NOT LIKE 'Anulad%' $where_sucursal AND f.tipo='f' GROUP BY f.id_factura ORDER BY f.numero DESC";

			$db->setQuery("$query");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'ver_detalles':
			$id_factura = $_GET['id'];
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM factura_detalle WHERE id_factura=$id_factura");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		
		break;
		
		case 'ver_pagos':
			$id_factura = $_GET['id'];
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM pagos WHERE id_factura=$id_factura");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
			
			
	}
?>