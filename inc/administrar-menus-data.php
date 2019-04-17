<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	
	switch ($q){
		
		case 'ver':
						
			$db = DataBase::conectar();
			$db->setQuery("SELECT *, case estado when '1' then 'Habilitado' when '0' then 'Deshabilitado' end as nombre_estado FROM menus ORDER BY orden,menu,submenu");
							
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
			break;
			
		case 'cargar':
		
			$db = DataBase::conectar();

			$menu = $db->clearText($_POST['menu_carga']);
			$submenu= $db->clearText($_POST['submenu_carga']);
			$titulo = $db->clearText($_POST['titulo_carga']);
			$url = $db->clearText($_POST['url_carga']);
			$orden = $db->clearText($_POST['orden_carga']);
			$estado = 1;
			

			if (empty($menu)){
				echo alertDismiss("Error. Favor ingrese un nombre de menú", "error");
				exit;
			}
				
			$db->setQuery("INSERT INTO menus (menu,submenu,titulo,url,orden,estado) VALUES ('$menu','$submenu','$titulo','$url','$orden','$estado')");
			
			if($db->alter()){
				echo alertDismiss("Menú registrado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
					
		case 'editar':
			
			$db = DataBase::conectar();
			$id_menu = $_POST['hidden_id_menu'];
			$menu = $db->clearText($_POST['menu_editar']);
			$submenu= $db->clearText($_POST['submenu_editar']);
			$titulo = $db->clearText($_POST['titulo_editar']);
			$url = $db->clearText($_POST['url_editar']);
			$orden = $db->clearText($_POST['orden_editar']);
			$estado = $db->clearText($_POST['estado_editar']);

			if (empty($id_menu)){
				echo alertDismiss("Error no se encontró el ID del menú. Favor recargue la página e intente nuevamente.", "error");
				exit;
			}
			if (empty($menu)){
				echo alertDismiss("Error. Favor ingrese un nombre de rol", "error");
				exit;
			}
			
			$db->setQuery("UPDATE menus SET menu='$menu',submenu='$submenu',titulo='$titulo',url='$url',orden='$orden',estado='$estado' WHERE id_menu='$id_menu'");
	
			if($db->alter()){
				echo alertDismiss("Menú modificado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
		
		case 'eliminar':
			$success = false;
			$id = $_POST['id'];
			$nombre = $_POST['nombre'];
			
			//SE HACE UPDATE ANTES PARA DEJAR REGISTRADO EL USUARIO QUE VA A ELIMINAR EL REGISTRO MEDIANTE TRIGGER
			/*$db = DataBase::conectar();
			$db->setQuery("UPDATE proveedores SET usuario_del='$usuario' WHERE id_proveedor = '$id'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
				exit;
			}*/
			
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM menus WHERE id_menu = $id");

			if($db2->alter()){
				echo alertDismiss("Menú '$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db2->getError(), "error");
			}
			
		break;
		
				
	}


?>