<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	
	switch ($q){
		
		case 'ver':
						
			$db = DataBase::conectar();
			$db->setQuery("SELECT *, case estado when '1' then 'Habilitado' when '0' then 'Deshabilitado' end as nombre_estado FROM sucursales ORDER BY sucursal ASC");
							
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
			break;
			
		case 'cargar':
		
			$db = DataBase::conectar();

			$sucursal = $db->clearText($_POST['sucursal_carga']);
			$direccion = $db->clearText($_POST['direccion_carga']);

			if (empty($sucursal)){
				echo alertDismiss("Error. Favor ingrese nombre del sucursal", "error");
				exit;
			}
				
			$db->setQuery("INSERT INTO sucursales (sucursal, direccion, estado) VALUES ('$sucursal','$direccion',1)");
			
			if($db->alter()){
				echo alertDismiss("Sucursal registrado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			
			$id_sucursal = $db->clearText($_POST['hidden_id_sucursal']);
			$sucursal = $db->clearText($_POST['sucursal_editar']);
			$direccion = $db->clearText($_POST['direccion_editar']);
			$estado = $db->clearText($_POST['estado_editar']);

			if (empty($sucursal)){
				echo alertDismiss("Error. Favor ingrese nombre del sucursal", "error");
				exit;
			}
			
			$db->setQuery("UPDATE sucursales SET sucursal='$sucursal', direccion='$direccion', estado='$estado'	WHERE id_sucursal = '$id_sucursal'");
	
			if($db->alter()){
				echo alertDismiss("sucursal modificado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
		
		case 'eliminar':

			$id = $_POST['id'];
			$nombre = $_POST['nombre'];
			$db = DataBase::conectar();
			
			$db->setQuery("DELETE FROM sucursales WHERE id_sucursal = $id");

			if($db->alter()){
				echo alertDismiss("sucursal '$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db->getError(), "error");
			}
			
		break;
	}


?>