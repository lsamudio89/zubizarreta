	<?php
	include_once("inc/funciones.php");
	$usu = trim($_POST['u']);
	$pass = $_POST['p'];
	$remember = $_POST['r'];
	$mensaje = "";
	
	if (isset($_POST['p2'])){
		$pass2 = $_POST['p2'];
		
		if (empty($usu)) {
			$mensaje = array("mensaje" => alertDismiss("Escriba un nombre de usuario","error"));
		}else if (empty($pass)){
			$mensaje = array("mensaje" => alertDismiss("Escriba una contraseña","error"));
		}else if ($pass != $pass2){
			$mensaje = array("mensaje" => alertDismiss("Las contraseñas no coinciden. Favor reintente","error"));
		}else{
				$db = DataBase::conectar();
				$db->setQuery("UPDATE usuarios SET password=md5('$pass'), estado=1 WHERE nombre_usuario='$usu'");
				if($db->alter()){
					$mensaje = array("mensaje" => alertDismiss("Contraseña cambiada con éxito. Recargando...","ok"), "redirect" => "./index.php");
				}else{
					$mensaje = array("mensaje" => alertDismiss("Error: ". $db->getError(),"error"));
				}
				
		}

		echo json_encode($mensaje);
		
	}else{
		if (empty($usu)) {
			$mensaje = array("mensaje" => alertDismiss("Escriba un nombre de usuario","error"));
		}else{
			if (empty($pass)) {
			$mensaje = array("mensaje" => alertDismiss("Escriba una contraseña","error"));
			}else{
				$db = DataBase::conectar();
				$db->setQuery("select id_usuario, nombre_usuario, id_sucursal,rol,estado from usuarios where nombre_usuario='$usu' and password=md5('$pass') and estado > 0");
				$u = $db->loadObject();
				
				if ($u){
					//CONTRASEÑA EXPIRADA
					if ($u->estado == "2"){
						$mensaje = array("redirect" => "./login-nuevo-pass.php");
						session_start();
						$_SESSION['usuario_nuevo_pass'] = $u->nombre_usuario;
					}else{
						$id_usuario = md5($u->id_usuario);
						$nombre_usuario = $u->nombre_usuario;
						$id_sucursal = $u->id_sucursal;
						$rol= $u->rol;
						session_start([
							'cookie_lifetime' => 43200,
						]);
						$_SESSION['id_usuario'] = $id_usuario;
						$_SESSION['id_sucursal'] = $id_sucursal;
						$_SESSION['id_rol'] = $rol;
						$_SESSION['usuario'] = $nombre_usuario;
						$mensaje = array("mensaje" => "Correcto");
						if ($remember == "true"){
							#Recordar por 6 meses
							setcookie("3a60fbdR3c0Rd4R0ebf5",$id_usuario, time()+3600*24*180, "/");
						}
					}
				}else{
					$mensaje = array("mensaje" => alertDismiss("Nombre de usuario o contraseña incorrecta","error"));
				}
			}
		}
		if ($mensaje["mensaje"] == "Correcto"){
			$mensaje = array("redirect" => "./inicio.php");
		}
		
		echo json_encode($mensaje);
	}
	
	
	
?>
