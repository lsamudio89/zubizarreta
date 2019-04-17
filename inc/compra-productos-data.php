<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$moneda = datosSucursal($id_usuario)->moneda;
	
	switch ($q){
		
		case 'buscar':
		
			$db = DataBase::conectar();
			$buscar = $db->clearText($_GET['filtro']);
			$db->setQuery("SELECT p.id_producto, p.producto, FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, 
				FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, tp.tipo, pf.foto FROM productos p 
				LEFT JOIN productos_fotos pf ON pf.id_producto=p.id_producto LEFT JOIN tipos_productos tp ON tp.id_tipo=p.id_tipo
				WHERE CONCAT_WS('|',p.id_producto,p.producto,tp.tipo) LIKE '%$buscar%' GROUP BY p.id_producto");
			
			$rows = $db->loadObjectList();
			
			if (empty($rows)){
				$palabras = "";
				$buscar_array = explode(" ", $buscar);
				foreach ($buscar_array as $b){
					$palabras .= "%".$b."%";
				}
				$db->setQuery("SELECT p.id_producto, p.producto, FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, 
				FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, tp.tipo, pf.foto FROM productos p 
				LEFT JOIN productos_fotos pf ON pf.id_producto=p.id_producto LEFT JOIN tipos_productos tp ON tp.id_tipo=p.id_tipo
				WHERE CONCAT_WS('|',p.id_producto,p.producto,tp.tipo) LIKE '%$palabras%' GROUP BY p.id_producto");
				$rows = $db->loadObjectList();
			}

			echo json_encode($rows);
		break;
		
		case 'guardar':
		
			$db = DataBase::conectar();
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 
			$total=0;
			$fecha = fechaMYSQL($_POST['fecha']);
			$id_sucursal = $db->clearText($_POST['id_sucursal']);
			$descripcion = $db->clearText($_POST['descripcion']);
			
			$db->setQuery("INSERT INTO compra_productos (fecha, id_sucursal, descripcion, usuario) VALUES ('$fecha','$id_sucursal','$descripcion','$usuario')");
			
			if(!$db->alter()){
				$db->rollback();  //Revertimos los cambios
				echo alertDismiss("Error al insertar las compras: ". $db->getError(), "error");
				exit;
			}else{
				$last_id = $db->getLastID();
				foreach ( $_POST['datos'] as $key => $valor )
				{
					$id_producto = $db->clearText($valor['id_producto']);
					$producto = $db->clearText($valor['producto']);
					$cantidad = $db->clearText(quitaSeparadorMiles($valor['cantidad']));
					$costo = $db->clearText(quitaSeparadorMiles($valor['costo']));
									
					$db->setQuery("INSERT INTO compra_detalles (id_compra_producto, id_producto, cantidad, costo, id_sucursal, fecha, usuario, estado) 
									VALUES ('$last_id', '$id_producto', '$cantidad', '$costo', '$id_sucursal', NOW(), '$usuario','En tránsito')");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error al insertar detalles: ". $db->getError(), "error");
						exit;
					}
					
					//Verificamos si el producto ya existe en tabla stock
					$db->setQuery("SELECT id_producto FROM stock WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal");
					$row = $db->loadObject();
					//Si no existe insertamos el producto con stock 0
					if(empty($row)){
						$db->setQuery("INSERT INTO stock(id_producto, stock, id_sucursal, usuario, fecha) VALUES($id_producto, 0, $id_sucursal, '$usuario', NOW())");
						if(!$db->alter()){
							$db->rollback();  //Revertimos los cambios
							echo alertDismiss("Error al insertar stock: ". $db->getError(), "error");
							exit;
						}
					}
					$total += ($costo*$cantidad);
				}
				
				$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades-$total WHERE id_sucursal=$id_sucursal");
				if(!$db->alter()){
					$db->rollback();  //Revertimos los cambios
					echo alertDismiss("Error al actualizar el disponible: ". $db->getError(), "error");
					exit;
				}
				
				$db->commit(); //Aplicamos los cambios en BD
				echo alertDismiss("Compra de productos registrada con éxito. Recargando página...", "ok");
			}
		break;
	}


?>