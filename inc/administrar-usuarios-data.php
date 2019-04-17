<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	
	switch ($q){
		
		case 'ver':
						
			$db = DataBase::conectar();
			$db->setQuery("SELECT u.*, r.id_rol, r.rol, s.sucursal, s.id_sucursal FROM usuarios u LEFT JOIN roles r ON r.id_rol=u.rol LEFT JOIN sucursales s ON s.id_sucursal=u.id_sucursal ORDER BY 1 desc");
							
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
			break;
			
		case 'cargar':
		
			$db = DataBase::conectar();

			$nombre_usuario = $db->clearText($_POST['usuario_carga']);
			$nombre = $db->clearText($_POST['nombre_carga']);
			$apellido = $db->clearText($_POST['apellido_carga']);
			$ci = $db->clearText($_POST['ci_carga']);
			$cargo = $db->clearText($_POST['cargo_carga']);
			$departamento = $db->clearText($_POST['departamento_carga']);
			$telefono = $db->clearText($_POST['telefono_carga']);
			$celular = $db->clearText($_POST['celular_carga']);
			$direccion = $db->clearText($_POST['direccion_carga']);
			$email = $db->clearText($_POST['email_carga']);
			$id_rol =  $db->clearText($_POST['rol_carga']);
			$password =  md5($db->clearText($_POST['password_carga']));
			$sucursal = $db->clearText($_POST['sucursal_carga']);

			if (empty($nombre_usuario)){
				echo alertDismiss("Error. Favor ingrese un nombre de usuario", "error");
				exit;
			}
				
			$db->setQuery("INSERT INTO usuarios (nombre_usuario,nombre,apellido,departamento,cargo,ci,email,telefono,celular,direccion,password,estado,rol,id_sucursal) VALUES ('$nombre_usuario','$nombre','$apellido','$departamento','$cargo','$ci','$email','$telefono','$celular','$direccion','$password',2,'$id_rol','$sucursal')");
			
			if($db->alter()){
				echo alertDismiss("Usuario registrado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
					
		case 'editar':
			
			$db = DataBase::conectar();
			$id_usuario = $_POST['hidden_id_usuario'];
			$nombre_usuario = $db->clearText($_POST['usuario_editar']);
			$nombre = $db->clearText($_POST['nombre_editar']);
			$apellido = $db->clearText($_POST['apellido_editar']);
			$ci = $db->clearText($_POST['ci_editar']);
			$cargo = $db->clearText($_POST['cargo_editar']);
			$departamento = $db->clearText($_POST['departamento_editar']);
			$telefono = $db->clearText($_POST['telefono_editar']);
			$celular = $db->clearText($_POST['celular_editar']);
			$direccion = $db->clearText($_POST['direccion_editar']);
			$email = $db->clearText($_POST['email_editar']);
			$id_rol =  $db->clearText($_POST['rol_editar']);
			$estado =  $db->clearText($_POST['estado_editar']);
			$sucursal = $db->clearText($_POST['sucursal_editar']);

			if (empty($id_usuario)){
				echo alertDismiss("Error no se encontró el ID del usuario. Favor recargue la página e intente nuevamente.", "error");
				exit;
			}
			
			$db->setQuery("UPDATE usuarios SET nombre='$nombre', apellido='$apellido', departamento='$departamento', cargo='$cargo', ci='$ci', email='$email', telefono='$telefono', celular='$celular', direccion='$direccion', estado='$estado', rol='$id_rol', id_sucursal='$sucursal' WHERE id_usuario='$id_usuario'");
	
			if($db->alter()){
				echo alertDismiss("Usuario modificado con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
		
		case 'eliminar':
			$success = false;
			$id = $_POST['id'];
			$nombre_usuario = $_POST['nombre'];
			
			//SE HACE UPDATE ANTES PARA DEJAR REGISTRADO EL USUARIO QUE VA A ELIMINAR EL REGISTRO MEDIANTE TRIGGER
			/*$db = DataBase::conectar();
			$db->setQuery("UPDATE proveedores SET usuario_del='$usuario' WHERE id_proveedor = '$id'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
				exit;
			}*/
			
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM usuarios WHERE id_usuario = $id");

			if($db2->alter()){
				echo alertDismiss("Usuario '$nombre_usuario' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre_usuario'. ". $db2->getError(), "error");
			}
			
		break;
		
		case 'restablecer_password':
			$db = DataBase::conectar();
			$nombre_usuario = $db->clearText($_POST['nombre']);
			
			$db->setQuery("UPDATE usuarios SET password=md5('$nombre_usuario'), estado=2 WHERE nombre_usuario='$nombre_usuario'");
	
			if($db->alter()){
				echo alertDismiss("Contraseña del Usuario '$nombre_usuario' restablecida con éxito", "ok");
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
		
		break;
		
	}


?>