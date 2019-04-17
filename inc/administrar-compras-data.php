<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$moneda = datosSucursal($id_usuario)->moneda;
	$id_sucursal_usu = datosUsuario($id_usuario)->id_sucursal;
	$fecha=date('Y-m-d H:i:s');
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 3;
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|',cd.id_producto, p.producto, cd.fecha) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS cd.id_compra_detalle, cd.id_producto, p.producto, cd.cantidad, IFNULL(cd.cant_recibida,0) AS cant_recibida, 
				cd.cantidad-IFNULL(cd.cant_recibida,0) AS cant_pendiente, '0' as cant_a_recibir, FORMAT(IFNULL(cd.costo,0),0,'de_DE') AS costo, cd.fecha, cd.usuario, cd.fecha_modifica, cd.usuario_modifica
				FROM compra_detalles cd LEFT JOIN productos p ON cd.id_producto=p.id_producto WHERE IFNULL(cd.cant_recibida,0) < cantidad $where ORDER BY $sort $order LIMIT $offset, $limit");
			}else{
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS cd.id_compra_detalle, cd.id_producto, p.producto, cd.cantidad, IFNULL(cd.cant_recibida,0) AS cant_recibida, 
				cd.cantidad-IFNULL(cd.cant_recibida,0) AS cant_pendiente, '0' as cant_a_recibir, IFNULL(cd.costo,0) AS costo, cd.fecha, cd.usuario, cd.fecha_modifica, cd.usuario_modifica
				FROM compra_detalles cd LEFT JOIN productos p ON cd.id_producto=p.id_producto WHERE IFNULL(cd.cant_recibida,0) < cantidad $where ORDER BY $sort $order LIMIT $offset, $limit");
			}
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
					
		case 'guardar':
		
			$db = DataBase::conectar();
			$error=0;
			foreach ($_POST['datos'] as $key => $val){
				$id_compra_detalle = $val['id_compra_detalle'];
				$cant_a_recibir = $db->clearText(quitaSeparadorMiles($val['cant_a_recibir']));
				if ($cant_a_recibir > 0){
					$db->setQuery("UPDATE compra_detalles SET cant_recibida = IFNULL(cant_recibida,0) + '$cant_a_recibir', fecha_modifica=NOW(), usuario_modifica='$usuario' WHERE id_compra_detalle = '$id_compra_detalle'");
			
					if(!$db->alter()){
						echo alertDismiss("Error: ". $db->getError(), "error");
						$error=1;
					}
				}
			}
			
			if ($error==0){				
				echo alertDismiss("Compras actualizadas correctamente.", "ok");
			}

		break;

	}


?>