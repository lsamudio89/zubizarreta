<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$moneda = datosConfig('moneda');
	
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM tipos_productos ORDER BY tipo");		
			$rows = $db->loadObjectList();
			echo json_encode($rows);
			break;
			
		case 'cargar':
			
			$db = DataBase::conectar();
			
			$tipo = $db->clearText($_POST['tipo_carga']);

			$db->setQuery("INSERT INTO tipos_productos (tipo,usuario,estado) VALUES ('$tipo','$usuario','1')");
			
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				echo alertDismiss("Tipo de producto registrado correctamente", "ok");
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			$id_tipo = $_POST['hidden_id_tipo'];
			$tipo = $db->clearText($_POST['tipo_editar']);			
			$estado = $db->clearText($_POST['estado_editar']);

			$db->setQuery("UPDATE tipos_productos SET tipo='$tipo', estado='$estado', usuario='$usuario' WHERE id_tipo = '$id_tipo'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{	
				echo alertDismiss("Tipo de Producto modificado correctamente. Recargando...", "ok");
			}

		break;
		
		case 'eliminar':
			$success = false;
			$id = $_POST['id'];
			$nombre = $_POST['nombre'];
			
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM tipos_productos WHERE id_tipo = $id");

			if($db2->alter()){
				echo alertDismiss("'$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db2->getError(), "error");
			}
			
		break;		

	}


?>