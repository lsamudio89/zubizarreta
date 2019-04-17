<?php

	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$moneda = datosSucursal($id_usuario)->moneda;
	$id_sucursal = datosSucursal($id_usuario)->id_sucursal;
	
	switch ($q){
		
		case 'sucursal_destino':
			//$id_sucursal = $_POST['id_suc'];
			$db = DataBase::conectar();
			//if ($id_sucursal==1){
				$db->setQuery("SELECT id_sucursal, concat_ws(' - ',nombre_empresa,sucursal) as sucursal FROM sucursales WHERE estado=1 ORDER BY sucursal");
			/*}else{
				$db->setQuery("SELECT id_sucursal, concat_ws(' - ',nombre_empresa,sucursal) as sucursal FROM sucursales WHERE estado=1 AND id_sucursal='$id_sucursal'");
			}*/
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'buscar':
		
		$db = DataBase::conectar();
			$db = DataBase::conectar();
			$buscar = $db->clearText($_GET['filtro']);
			$id_sucursal = $db->clearText($_GET['sucursal']);
			$db->setQuery("SELECT p.id_producto, p.producto, s.stock, FORMAT(p.costo,0,'de_DE') AS costo, pf.foto FROM productos p 
				LEFT JOIN productos_fotos pf ON pf.id_producto=p.id_producto
                INNER JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal')
				WHERE CONCAT_WS('|',p.id_producto,p.producto) LIKE '%$buscar%' GROUP BY p.id_producto");
			
			$rows = $db->loadObjectList();
			
			if (empty($rows)){
				$palabras = "";
				$buscar_array = explode(" ", $buscar);
				foreach ($buscar_array as $b){
					$palabras .= "%".$b."%";
				}
				$db->setQuery("SELECT p.id_producto, p.producto, s.stock, FORMAT(p.costo,0,'de_DE') AS costo, pf.foto FROM productos p 
				LEFT JOIN productos_fotos pf ON pf.id_producto=p.id_producto
                INNER JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal')
				WHERE CONCAT_WS('|',p.id_producto,p.producto) LIKE '$palabras' GROUP BY p.id_producto");
				$rows = $db->loadObjectList();
			}

			echo json_encode($rows);
		break;
		
			case 'buscar_por_codigo':
			$db = DataBase::conectar();
			
			$id_producto = $db->clearText($_REQUEST['codigo']);
			
			$query = "SELECT p.id_producto, p.producto, IFNULL(s.stock,0) AS stock, p.stock_minimo, FORMAT(IFNULL(p.costo,0),0,'de_DE') AS costo, 
						FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, 
						FORMAT((p.precio_vta_min-p.costo),0,'de_DE') AS ganancia_min, FORMAT((p.precio_vta_may-p.costo),0,'de_DE') AS ganancia_may
						FROM productos p
						LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal')
						WHERE p.id_producto='$id_producto'";
						
			$db->setQuery($query);
			$rows = $db->loadObject();	
			if (empty($rows)){
				echo json_encode(array());
			}else{
				echo json_encode($rows);
			}
			
		break;
		
		
		case 'transferir':
		
			$db = DataBase::conectar();
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE);
			$total=0;
			$fecha = date("Y-m-d");
			$id_sucursal_ori = $db->clearText($_POST['id_sucursal_ori']);
			$id_sucursal_des = $db->clearText($_POST['id_sucursal_des']);
			$descripcion = $db->clearText($_POST['descripcion']);

			$db->setQuery("INSERT INTO compra_productos (fecha, id_sucursal_origen, id_sucursal, descripcion, usuario) VALUES ('$fecha','$id_sucursal_ori', '$id_sucursal_des','$descripcion','$usuario')");
			
			if(!$db->alter()){
				$db->rollback();  //Revertimos los cambios
				echo alertDismiss("Error al guardar la transferencia: ". $db->getError(), "error");
			}else{
				$last_id = $db->getLastID();
				
				foreach ($_POST['datos'] as $key => $valor)
				{
					$id_producto = $db->clearText($valor['id_producto']);
					$producto = $db->clearText($valor['producto']);
					$cantidad = $db->clearText(quitaSeparadorMiles($valor['cantidad']));
					$costo = $db->clearText(quitaSeparadorMiles($valor['costo']));
									
					$db->setQuery("INSERT INTO compra_detalles (id_compra_producto, id_producto, cantidad, cant_recibida, costo, id_sucursal_origen, id_sucursal, fecha, usuario) 
									VALUES ('$last_id', '$id_producto', '$cantidad', '$cantidad', '$costo', '$id_sucursal_ori', '$id_sucursal_des', now(), '$usuario')");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error al insertar detalles: ". $db->getError(), "error");
						exit;
					}else{
						
						//Restamos el stock en sucursal origen
						$db->setQuery("UPDATE stock SET stock=stock-$cantidad, usuario='$usuario', fecha=NOW() WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal_ori");
						if(!$db->alter()){
							$db->rollback();  //Revertimos los cambios
							echo alertDismiss("Error al actualizar stock en origen: ". $db->getError(), "error");
							exit;
						}
						
						//Sumamos el stock en sucursal destino
						//Verificamos si el producto ya existe en tabla stock
						$db->setQuery("SELECT id_producto FROM stock WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal_des");
						$row = $db->loadObject();
						//Si no existe insertamos el producto con su stock
						if(empty($row)){
							$db->setQuery("INSERT INTO stock(id_producto, stock, id_sucursal, usuario, fecha) VALUES($id_producto, $cantidad, $id_sucursal_des, '$usuario', NOW())");
							if(!$db->alter()){
								$db->rollback();  //Revertimos los cambios
								echo alertDismiss("Error al insertar stock en destino: ". $db->getError(), "error");
								exit;
							}
						//Si existe actualizamos el stock
						}else{ 
							$db->setQuery("UPDATE stock SET stock=stock+$cantidad, usuario='$usuario', fecha=NOW() WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal_des");
							if(!$db->alter()){
								$db->rollback();  //Revertimos los cambios
								echo alertDismiss("Error al actualizar stock en destino: ". $db->getError(), "error");
								exit;
								
							}else{
								
					//trae datos de sucursal origen	
					$row_sucursal_ori=RowMaestro('sucursales','id_sucursal',$id_sucursal_ori);
					$sucursal_origen=$row_sucursal_ori['sucursal'];
					
					$row_stock=RowMaestro('stock','id_sucursal',$id_sucursal_ori,'id_producto',$id_producto);
					$stock_actual_ori=$row_stock['stock'];
								
					//trae datos de sucrusal destino
					$row_sucursal_des=RowMaestro('sucursales','id_sucursal',$id_sucursal_des);
					$sucursal_destino=$row_sucursal_des['sucursal'];
					$row_stock=RowMaestro('stock','id_sucursal',$id_sucursal_des,'id_producto',$id_producto);
					$stock_actual_des=$row_stock['stock'];
					
					//hacer un insert into al historial_stock de los detalles de origen
					$db4 = DataBase::conectar();
					$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
					VALUES ('$id_producto','$producto','$stock_actual_ori','$id_sucursal_ori','$cantidad transferidos a $sucursal_destino',now(),'$usuario')";
					$db4->setQuery("$qi");
					echo mysqli_error($db4);
					$db4->alter();
					
					//hacer un insert into al historial_stock de los detalles de destino
					$db4 = DataBase::conectar();
					$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
					VALUES ('$id_producto','$producto','$stock_actual_des','$id_sucursal_des','$cantidad transferidos desde $sucursal_origen',now(),'$usuario')";
					$db4->setQuery("$qi");
					echo mysqli_error($db4);
					$db4->alter();
							}
						}
					}
					$total += ($costo*$cantidad);
				}
				
				//Actualizamos lo disponible en cada sucursal
				//Sumamos en el origen
				$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades+$total WHERE id_sucursal=$id_sucursal_ori");
				if(!$db->alter()){
					$db->rollback();  //Revertimos los cambios
					echo alertDismiss("Error al actualizar el disponible en origen: ". $db->getError(), "error");
					exit;
				}			
				//Restamos en el destino
				$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades-$total WHERE id_sucursal=$id_sucursal_des");
				if(!$db->alter()){
					$db->rollback();  //Revertimos los cambios
					echo alertDismiss("Error al actualizar el disponible en destino: ". $db->getError(), "error");
					exit;
				}
				
				$db->commit(); //Aplicamos los cambios en BD
				//echo alertDismiss("Transferencia de productos realizada con éxito. Recargando página...", "ok");
				echo $last_id;
			}
		break;
		
		
	}


?>