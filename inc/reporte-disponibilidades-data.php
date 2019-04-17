<?php
	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_sucursal = $_SESSION['id_sucursal'];
	
	switch ($q){
		case 'ver':
			$db = DataBase::conectar();
			$q="SELECT * FROM sucursales ORDER BY id_sucursal";
			$db->setQuery("$q");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
			break;
	}
?>