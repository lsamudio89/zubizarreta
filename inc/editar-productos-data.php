<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$moneda = datosSucursal($id_usuario)->moneda;
	$id_sucursal_usu = datosUsuario($id_usuario)->id_sucursal;
	$fecha=date('Y-m-d H:i:s');
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
			$id_sucursal = $_REQUEST['id_sucursal'];
			$where = "";
		
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|',p.id_producto, p.producto) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT p.id_producto, p.producto, IFNULL(p.stock_minimo,0) AS stock_minimo, IFNULL(s.stock,0) AS stock, FORMAT(IFNULL(p.costo,0),0,'de_DE') AS costo, FORMAT(IFNULL(s.stock*p.costo,0),0,'de_DE') AS total_costo,  
				FORMAT(IFNULL(p.precio_vta_min,0),0,'de_DE') AS precio_vta_min, FORMAT(IFNULL(s.stock*p.precio_vta_min,0),0,'de_DE') AS total_vta_min,
				FORMAT(IFNULL(p.precio_vta_may,0),0,'de_DE') AS precio_vta_may, FORMAT(IFNULL(s.stock*p.precio_vta_may,0),0,'de_DE') AS total_vta_may,
				p.usuario FROM productos p LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal) WHERE 1=1 $where ORDER BY 2 ASC");
			}else{
				$db->setQuery("SELECT p.id_producto, p.producto, IFNULL(p.stock_minimo,0) AS stock_minimo, IFNULL(s.stock,0) AS stock, IFNULL(p.costo,0) AS costo, IFNULL(p.precio_vta_min,0) AS precio_vta_min, IFNULL(p.precio_vta_may,0) AS precio_vta_may, p.usuario
				FROM productos p LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal) WHERE 1=1 $where ORDER BY 2 ASC");
			}
			
			$rows = $db->loadObjectList();
		
			if ($rows){
				
				//echo "<tr><td data-editable='false'>Bike</td><td>330</td><td>240</td><td>1</td></tr>";
				echo json_encode($rows);
			}
			
		
		break;
					
		case 'guardar':
		
			$db = DataBase::conectar();
			$columna =  $db->clearText($_POST['columna']);
			$id_producto = $_POST['id_producto'];
			$id_sucursal = $_POST['id_sucursal'];
			$valor = $db->clearText(quitaSeparadorMiles($_POST['valor']));
			if ($columna=="stock"){
				//Verificamos si el producto ya existe en tabla stock
				$db->setQuery("SELECT id_producto FROM stock WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal");
				$row = $db->loadObject();
				//Si no existe insertamos el producto con stock 0
				if(empty($row)){
					$db->setQuery("INSERT INTO stock(id_producto, stock, id_sucursal, usuario, fecha, observaciones) VALUES($id_producto, $valor, $id_sucursal, '$usuario', NOW(), 'Insertado manualmente')");
					if(!$db->alter()){
						echo alertDismiss("Error al insertar stock: ". $db->getError(), "error_span");
						exit;
					}
				}else{
					$db->setQuery("UPDATE stock SET stock=$valor, fecha=NOW(), usuario='$usuario', observaciones='Modificado manualmente' WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal");
					if(!$db->alter()){
						echo alertDismiss("Error al actualizar el stock: ". $db->getError(), "error_span");
						exit;
					}
				}
				echo alertDismiss("Producto actualizado correctamente.", "ok_span");
			}else{
				$db->setQuery("UPDATE productos SET $columna=$valor WHERE id_producto=$id_producto");
		
				if(!$db->alter()){
					echo alertDismiss("Error al actualizar $columna: ". $db->getError(), "error_span");
				}else{
					echo alertDismiss("Producto actualizado correctamente.", "ok_span");
				}
			}
			
		break;

	}


?>