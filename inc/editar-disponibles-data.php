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
		
		case 'ver_disponibles':
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
				$where = "AND CONCAT_WS('|',hd.disponible, hd.observaciones, hd.fecha, hd.usuario) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS hd.id_disponible, hd.id_sucursal, CONCAT_WS(' - ',s.nombre_empresa, s.sucursal) AS sucursal, FORMAT(IFNULL(hd.disponible,0),0,'de_DE') AS disponible, hd.observaciones, hd.fecha, hd.usuario FROM historial_disponibles hd LEFT JOIN sucursales s ON s.id_sucursal=hd.id_sucursal WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
			}else{
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS hd.id_disponible, hd.id_sucursal, CONCAT_WS(' - ',s.nombre_empresa, s.sucursal) AS sucursal, hd.disponible, hd.observaciones, hd.fecha, hd.usuario FROM historial_disponibles hd LEFT JOIN sucursales s ON s.id_sucursal=hd.id_sucursal WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
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
		
		case 'ver_disp_sucursal':
			$db = DataBase::conectar();
			$id_sucursal = $_POST['id_sucursal'];
			$db->setQuery("SELECT disponibilidades FROM sucursales WHERE id_sucursal='$id_sucursal'");
			$row = $db->loadObject();
			echo $row->disponibilidades;
		break;
					
		case 'guardar':
		
			$db = DataBase::conectar();
			$error=0;
			$disponible = $db->clearText(quitaSeparadorMiles($_POST['disponible']));
			$motivo = $db->clearText($_POST['motivo']);
			$id_sucursal = $_POST['id_sucursal'];
			
			$db->setQuery("UPDATE sucursales SET disponibilidades='$disponible' WHERE id_sucursal='$id_sucursal'");
		
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				sleep(0.5);
				$db->setQuery("UPDATE historial_disponibles SET observaciones='$motivo', usuario='$usuario' WHERE id_sucursal='$id_sucursal' ORDER BY id_disponible DESC LIMIT 1");
				if(!$db->alter()){
					echo alertDismiss("Error: ". $db->getError(), "error");
				}else{
					echo alertDismiss("Disponible modificado correctamente.", "ok");
				}
			}


		break;

	}


?>