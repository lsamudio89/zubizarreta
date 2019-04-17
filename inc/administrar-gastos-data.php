<?php

	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_sucursal = $_SESSION['id_sucursal'];
	$id_rol = $_SESSION['id_rol'];
	
	switch ($q){
		
		case 'ver':
			
			$desde=fechaMYSQL($_REQUEST['desde']);
			$hasta=fechaMYSQL($_REQUEST['hasta']);
			
			if ($desde=="--" or empty($desde)){
			$desde=date('Y-m-01');	
			}
			
			if ($hasta=="--" or empty($hasta)){
			$hasta=date('Y-m-d');	
			}
						
			$db = DataBase::conectar();
			$q="SELECT * FROM gastos where fecha>='$desde' and fecha<='$hasta' and estado=1 ORDER BY id_gasto";
			$db->setQuery("$q");
							
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
			break;
			
		case 'cargar':
		
			$db = DataBase::conectar();

			$fecha= $db->clearText($_POST['fecha_carga']);
			$monto= $db->clearText($_POST['monto_carga']);
			$id_sucursal_post= $db->clearText($_POST['id_sucursal']);
			$monto=str_replace('.','',$monto);
			$descripcion= $db->clearText($_POST['descripcion_carga']);

			if (empty($monto)){
				echo alertDismiss("Error. Favor ingrese monto", "error");
				exit;
			}
				
			if (empty($id_sucursal)){
				echo alertDismiss("Falta id_sucursal", "error");
				exit;
			}
			
			if ($id_rol==1 and $id_sucursal_post>0){
			$id_sucursal=$id_sucursal_post;	
			}
				
			$db->setQuery("INSERT INTO gastos(monto,fecha,hora,usuario,descripcion,id_sucursal) VALUES ('$monto','$fecha',now(),'$usuario','$descripcion','$id_sucursal')");
			
			if($db->alter()){
				echo alertDismiss("Gasto registrado con éxito", "ok");
				
				$qu="update sucursales set disponibilidades=disponibilidades-$monto where id_sucursal=$id_sucursal";
				$db->setQuery("$qu");
				$db->alter();
				
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
					
		
		case 'anular':
			$success = false;
			$id_gasto = $_GET['id_gasto'];
			$db = DataBase::conectar();
			$db->setQuery("update gastos set estado=0, usuario_anulo='$usuario', fecha_anulo=now() where id_gasto='$id_gasto'");

			if($db->alter()){
				echo alertDismiss("gasto nro <b>$id_gasto</b> anulado correctamente", "ok");

				$row_gasto=RowMaestro('gastos','id_gasto',$id_gasto);
				$monto=$row_gasto['monto'];
				
				//vuelve a sumar a disponibilidades
				$qu="update sucursales set disponibilidades=disponibilidades+$monto where id_sucursal=$id_sucursal";
				$db->setQuery("$qu");
				$db->alter();
							
			}else{
				echo alertDismiss("Error al anular gasto nro <b>$id_gasto</b>. ". $db->getError(), "error");
			}
			
		break;
	}

if($q=="traer_disponible" and $_GET['id_sucursal']>0){
$id_sucursal=$_GET['id_sucursal'];
$db = DataBase::conectar();
$q="select disponibilidades from sucursales where id_sucursal=$id_sucursal ";
$r=mysqli_query($db,$q);
$row=mysqli_fetch_array($r);
$disponibilidades=$row['disponibilidades'];

if (empty($disponibilidades)){
$disponibilidades=0;	
}

$disponibilidades_punto=poner_puntos($disponibilidades);

if ($disponibilidades>0){
$sty="style='color:green;'";
$disabled="";	
}else{
$sty="style='color:red;'";
$disabled="disabled";
}

echo "<b $sty>Gs. $disponibilidades_punto</b>";
}
?>