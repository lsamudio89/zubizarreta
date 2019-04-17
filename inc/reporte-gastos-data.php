<?php
	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_sucursal = $_SESSION['id_sucursal'];
	
	switch ($q){
		case 'ver':
			
			$desde=($_REQUEST['desde']);
			$hasta=($_REQUEST['hasta']);
			
			$id_sucursal_superadmin=$_REQUEST['id_sucursal'];
			
			if ($id_sucursal_superadmin){
				
				if ($id_sucursal_superadmin=="todas"){
					$sucursal="";
				}else{
					$sucursal="AND g.id_sucursal='$id_sucursal_superadmin'";
				}
			}else{
				$sucursal="AND g.id_sucursal='$id_sucursal'";
			}
			
			if ($desde=="--" or empty($desde)){
			$desde=date('Y-m-d');	
			}
			
			if ($hasta=="--" or empty($hasta)){
			$hasta=date('Y-m-d');	
			}
			
			$hasta="$hasta 23:00:00";

			
			$db = DataBase::conectar();
			$q="SELECT g.id_gasto, FORMAT(g.monto,0,'de_DE') AS monto, g.fecha, g.hora, g.descripcion, g.usuario, g.id_sucursal, concat_ws(' - ',s.nombre_empresa,s.sucursal) as sucursal, g.estado 
			FROM gastos g INNER JOIN sucursales s ON s.id_sucursal = g.id_sucursal WHERE g.fecha>='$desde' and g.fecha<='$hasta' and g.estado=1 $sucursal ORDER BY g.id_gasto DESC";
			$db->setQuery("$q");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
	}
?>