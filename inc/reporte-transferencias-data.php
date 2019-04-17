<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 1;
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|', c.fecha, so.sucursal, sd.sucursal, costo) LIKE '%$search%'";
			}
			
			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS c.id_compra_producto, cd.fecha, so.sucursal as sucursal_origen, sd.sucursal as sucursal_destino, FORMAT(SUM(cd.costo*cd.cantidad),0,'de_DE') AS total_costo,
								SUM(cd.cantidad) AS cantidad 
								FROM compra_productos c INNER JOIN compra_detalles cd ON cd.id_compra_producto=c.id_compra_producto
								INNER JOIN sucursales so ON so.id_sucursal=c.id_sucursal_origen INNER JOIN sucursales sd ON sd.id_sucursal=c.id_sucursal WHERE 1=1 $where
								GROUP BY cd.id_compra_producto ORDER BY $sort $order LIMIT $offset, $limit");
			$rows = $db->loadObjectList();
			
			$db->setQuery("SELECT FOUND_ROWS() as total");		
			$total_row = $db->loadObject();
			$total = $total_row->total;
			
			if ($rows){
				$salida = array('total' => $total, 'rows' => $rows);
			}else{
				$salida = array('total' => 0, 'rows' => array());
			}
			
			echo json_encode($salida);
		
		break;

		case 'ver_detalles':
			$db = DataBase::conectar();
			$id_compra_producto=$_GET['id'];
			$db->setQuery("SELECT cd.id_compra_detalle, p.producto, cd.cantidad, FORMAT(cd.costo,0,'de_DE') AS costo, cd.fecha, cd.usuario
								FROM compra_detalles cd INNER JOIN productos p ON p.id_producto=cd.id_producto WHERE cd.id_compra_producto=$id_compra_producto ORDER BY producto");
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
		break;

	}


?>