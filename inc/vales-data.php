<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_rol = datosUsuario($id_usuario)->rol;
	
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 2;
			$search = $_REQUEST['search'];
			
			if ($id_rol != 1){
				$where = "AND v.id_sucursal='$id_sucursal'";
			}

			if (isset($search)){
				$where .= " AND CONCAT_WS('|', v.monto, v.fecha, v.motivo, v.usuario, s.sucursal) LIKE '%$search%'";
			}
			
	
			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS v.id_vale, v.fecha, v.nro_vale, FORMAT(v.monto,0,'de_DE') AS monto, v.motivo, v.estado, v.usuario, v.id_sucursal, s.sucursal, CASE v.estado WHEN '1' then 'Procesado' WHEN '0' THEN 'Anulado' END AS nombre_estado 
			FROM vales v INNER JOIN sucursales s ON s.id_sucursal=v.id_sucursal WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
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

		case 'cargar':
			$db = DataBase::conectar();
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 

			$nro_vale = $db->clearText($_POST['nro_vale']);
			$monto = $db->clearText(quitaSeparadorMiles($_POST['monto']));
			$motivo = $db->clearText($_POST['motivo']);

			if (empty($nro_vale)){
				echo alertDismiss("Error. Favor ingrese número de vale.", "error");
				exit;
			}
			if (empty($monto)){
				echo alertDismiss("Error. Favor ingrese monto.", "error");
				exit;
			}

			$db->setQuery("INSERT INTO vales (fecha, nro_vale, monto, motivo, estado, usuario, id_sucursal) VALUES (NOW(),'$nro_vale','$monto','$motivo',1,'$usuario',$id_sucursal)");
		
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
				$db->rollBack();  //Revertimos los cambios
				exit;
			}
			
			$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades-$monto WHERE id_sucursal=$id_sucursal");
			if(!$db->alter()){
				echo alertDismiss("Error al actualizar el disponible: ". $db->getError(), "error");
				$db->rollBack();  //Revertimos los cambios
				exit;
			}			

			$db->commit(); //Aplicamos los cambios en BD
			echo alertDismiss("Vale guardado correctamente", "ok");
			
		break;
					
		case 'anular':
			$db = DataBase::conectar();
			$db->autocommit(FALSE); 
			
			$id = $_POST['id'];
			
			$db->setQuery("SELECT * FROM vales WHERE id_vale=$id");
			$row = $db->loadObject();
			$nro_vale = $row->nro_vale;
			$monto = $row->monto;
			$sucursal = $row->id_sucursal;
			
			$db->setQuery("UPDATE vales SET estado=0 WHERE id_vale=$id");

			if(!$db->alter()){
				echo alertDismiss("Error al anular '$nro_vale'. ". $db->getError(), "error");
				$db->rollBack();  //Revertimos los cambios
				exit;
			}
			
			$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades+$monto WHERE id_sucursal=$sucursal");
			if(!$db->alter()){
				echo alertDismiss("Error al actualizar el disponible: ". $db->getError(), "error");
				$db->rollBack();  //Revertimos los cambios
				exit;
			}			

			$db->commit(); //Aplicamos los cambios en BD
			echo alertDismiss("Vale '$nro_vale' anulado correctamente", "ok");
			
		break;		

	}


?>