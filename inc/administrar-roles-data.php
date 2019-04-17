<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$db->setQuery("SELECT r.id_rol, r.rol, r.estado, case r.estado when '1' then 'Habilitado' when '0' then 'Deshabilitado' end as nombre_estado, 
							group_concat(distinct concat_ws('->',m.menu,m.submenu) separator '&nbsp; // &nbsp;') as menus, 
							group_concat(distinct u.nombre_usuario separator '; ') as miembros FROM roles r
							LEFT JOIN roles_menu rm ON rm.id_rol=r.id_rol
							LEFT JOIN usuarios u ON u.rol = r.id_rol
							LEFT JOIN menus m ON m.id_menu = rm.id_menu
							group by r.rol ORDER BY r.rol ");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'ver_menus_asignados':
			$id_rol = $_POST['id_rol'];
			$db = DataBase::conectar();
			$db->setQuery("SELECT m.id_menu, concat_ws('->',m.menu,m.submenu) as menus FROM roles_menu rm INNER JOIN menus m ON m.id_menu=rm.id_menu WHERE rm.id_rol=$id_rol ORDER BY m.orden");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
			
		case 'cargar':
			$db = DataBase::conectar();
			$rol = $db->clearText($_POST['rol_carga']);
			if (empty($rol)){
				echo alertDismiss("Error. Favor ingrese un nombre de rol", "error");
				exit;
			}
			$db->setQuery("INSERT INTO roles (rol) VALUES ('$rol')");
		
			if($db->alter()){
				echo alertDismiss("Rol registrado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
		break;
					
		case 'editar':
			
			$db = DataBase::conectar();
			$id_rol = $_POST['hidden_id_rol'];
			$rol = $db->clearText($_POST['rol_editar']);
			$estado =  $db->clearText($_POST['estado_editar']);

			if (empty($id_rol)){
				echo alertDismiss("Error no se encontró el ID del usuario. Favor recargue la página e intente nuevamente.", "error");
				exit;
			}
			if (empty($rol)){
				echo alertDismiss("Error. Favor ingrese un nombre de rol", "error");
				exit;
			}
			
			$db->setQuery("UPDATE roles SET rol='$rol', estado='$estado' WHERE id_rol='$id_rol'");
	
			if($db->alter()){
				echo alertDismiss("Rol modificado con éxito", "ok");
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
			$db2->setQuery("DELETE FROM roles WHERE id_rol = $id");

			if($db2->alter()){
				echo alertDismiss("Rol '$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db2->getError(), "error");
			}
			
		break;
		
		case 'asignar_menus':
			$id_rol = $_POST['id_rol'];
			if (empty($id_rol)){
				echo alertDismiss("Error. Favor seleccione un rol", "error");
				exit;
			}
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM roles_menu WHERE id_rol = '$id_rol'");
			if(!$db2->alter()){
				echo alertDismiss("Error al eliminar los menús del rol seleccionado. Favor intente nuevamente. ". $db2->getError(), "error");
			}else{
				$db = DataBase::conectar();
				foreach ($_POST['menus'] as $menu_seleccionado){
					$db->setQuery("INSERT INTO roles_menu (id_rol, id_menu) VALUES ('$id_rol','$menu_seleccionado')");
			
					if(!$db->alter()){
						echo alertDismiss("Error: ". $db->getError(), "error");
						exit;
					}else{
						$ok = "ok";
					}
				}
					
				if ($ok == "ok"){
					echo alertDismiss("Menús asignados con éxito. Recargando...", "ok");
				}
			}
		break;
		
				
	}


?>