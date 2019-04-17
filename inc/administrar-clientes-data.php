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
			if (!isset($sort)) $sort = 2;
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|', razon_social, ruc, telefono, direccion, email, tipo) LIKE '%$search%'";
			}
			
			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS *, case estado when '1' then 'Habilitado' when '0' then 'Deshabilitado' when '2' then 'Moroso' end as nombre_estado FROM clientes WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
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
			$ruc = $db->clearText($_POST['ruc_carga']);
			$razon_social = $db->clearText($_POST['razon_social_carga']);
			$telefono = $db->clearText($_POST['telefono_carga']);
			$direccion = $db->clearText($_POST['direccion_carga']);
			$email = $db->clearText($_POST['email_carga']);
			$tipo = $db->clearText($_POST['tipo_carga']);
			
			if (empty($razon_social)){
				echo alertDismiss("Error. Favor ingrese nombre y apellido del cliente o Razón Social", "error");
				exit;
			}
		
			if ($ruc){
				$db->setQuery("INSERT INTO clientes (razon_social, ruc, telefono, direccion, email, tipo, estado, usuario, fecha) VALUES ('$razon_social','$ruc','$telefono','$direccion','$email','$tipo','1','$usuario',NOW())");
			}else{
				$db->setQuery("INSERT INTO clientes (razon_social, telefono, direccion, email, tipo, estado, usuario, fecha) VALUES ('$razon_social','$telefono','$direccion','$email','$tipo','1','$usuario', NOW())");
			}
		
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				echo alertDismiss("Cliente registrado correctamente", "ok");
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			$id_cliente = $_POST['hidden_id_cliente'];
			$ruc = $db->clearText($_POST['ruc_editar']);
			$razon_social = $db->clearText($_POST['razon_social_editar']);
			$telefono = $db->clearText($_POST['telefono_editar']);
			$direccion = $db->clearText($_POST['direccion_editar']);
			$email = $db->clearText($_POST['email_editar']);
			$tipo = $db->clearText($_POST['tipo_editar']);
			$estado = $db->clearText($_POST['estado_editar']);

			$db->setQuery("UPDATE clientes SET ruc='$ruc', razon_social='$razon_social', telefono='$telefono', direccion='$direccion',email='$email',tipo='$tipo', estado='$estado', usuario='$usuario' WHERE id_cliente = '$id_cliente'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				echo alertDismiss("Cliente modificado correctamente", "ok");
			}

		break;
		
		case 'eliminar':
			$success = false;
			$id = $_POST['id_cliente'];
			$nombre = $_POST['nombre'];
			
			//SE HACE UPDATE ANTES PARA DEJAR REGISTRADO EL USUARIO QUE VA A ELIMINAR EL REGISTRO MEDIANTE TRIGGER
			/*$db = DataBase::conectar();
			$db->setQuery("UPDATE productos SET usuario_del='$usuario' WHERE id_cliente = '$id'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
				exit;
			}*/
			
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM clientes WHERE id_cliente = $id");

			if($db2->alter()){
				echo alertDismiss("Cliente '$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db2->getError(), "error");
			}
			
		break;		

	}


?>