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
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 1;
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|',p.id_producto, producto, stock_minimo, precio_vta_min, precio_vta_may, precio_distribuidor, p.estado) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, s.stock, p.stock_minimo,p.iva,p.tipo,FORMAT(IFNULL(p.costo,0),0,'de_DE') AS costo, 
				FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, FORMAT(p.precio_distribuidor,0,'de_DE') AS precio_distribuidor, FORMAT((precio_vta_min-costo),0,'de_DE') AS ganancia_min, FORMAT((precio_vta_may-costo),0,'de_DE') AS ganancia_may, p.usuario, p.estado
            FROM productos p LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal_usu')
				WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
				
				//Para el recuerdo, tomaba el ultimo costo de tabla stock, ahora ese campo puse en tabla productos
				/*$db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, s.stock, p.stock_minimo, FORMAT(IFNULL(cos.costo,0),0,'de_DE') AS costo, 
				FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, 
				FORMAT((precio_vta_min-costo),0,'de_DE') AS ganancia_min, FORMAT((precio_vta_may-costo),0,'de_DE') AS ganancia_may, p.usuario, p.estado, t.id_tipo, t.tipo
            FROM productos p 
				LEFT JOIN tipos_productos t ON t.id_tipo=p.id_tipo
				LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal_usu')
				LEFT JOIN (select cd.id_producto, cd.costo
				FROM compra_detalles cd
				INNER JOIN (select id_producto, max(id_compra_detalle) AS maxid FROM compra_detalles GROUP BY id_producto) AS cd2 
								ON cd.id_compra_detalle = cd2.maxid) AS cos
				ON p.id_producto=cos.id_producto WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");*/
			}else{
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, s.stock, p.stock_minimo,p.iva,p.tipo,IFNULL(p.costo,0) AS costo, 
				p.precio_vta_min, p.precio_distribuidor, p.precio_vta_may, (precio_vta_min-costo) AS ganancia_min, (precio_vta_may-costo) AS ganancia_may, p.usuario, p.estado FROM productos p 
				LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal_usu')
				WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
			}
			$rows = $db->loadObjectList();
			$db->setQuery("SELECT FOUND_ROWS() as total");		
			$total_row = $db->loadObject();
			$total = $total_row->total;

			
			if ($rows){
				$salida = array('total' => $total, 'rows' => $rows);
			}else{
				$salida = array('total' => 0, 'rows' => array());
			}
			
			echo json_encode($salida);
		
		break;
			
		/*case 'ver_tipos':
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM tipos_productos WHERE estado=1 ORDER BY tipo");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;*/
		
		case 'sgte_id':
			$db = DataBase::conectar();
			$db->setQuery("SHOW TABLE STATUS LIKE 'productos'");
			$row = $db->loadObject();
			$auto_increment = str_pad($row->Auto_increment, 7, "0", STR_PAD_LEFT);
			echo $auto_increment;
		break;
		

		case 'ver_fotos':
			$id_producto = $_POST['id_producto'];
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM productos_fotos WHERE id_producto=$id_producto");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
			
		case 'cargar':
			
			$db = DataBase::conectar();
			$error=0;
			$producto = $db->clearText($_POST['producto_carga']);
			//$id_tipo = $db->clearText($_POST['tipo_carga']);
			$stock_minimo = $db->clearText(quitaSeparadorMiles($_POST['stock_minimo_carga']));
			$precio_vta_min = $db->clearText(quitaSeparadorMiles($_POST['precio_vta_min_carga']));
			$precio_vta_may = $db->clearText(quitaSeparadorMiles($_POST['precio_vta_may_carga']));
			$precio_distribuidor = $db->clearText(quitaSeparadorMiles($_POST['precio_distribuidor_carga']));
			$iva= $db->clearText($_POST['iva_carga']);
			$tipo= $db->clearText($_POST['tipo_carga']);
						
			if (empty($producto)){
				echo alertDismiss("Error. Favor ingrese nombre del producto", "error");
				exit;
			}
			
			$db->setQuery("INSERT INTO productos (producto,stock_minimo,precio_vta_min,precio_vta_may,precio_distribuidor,usuario,estado,iva,tipo) VALUES ('$producto','$stock_minimo','$precio_vta_min','$precio_vta_may','$precio_distribuidor','$usuario','1','$iva','$tipo')");
			
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				$last_id = $db->getLastID();
				$db2 = DataBase::conectar();
			
				//Recorremos y guardamos las fotos
				if (isset($_POST['foto_carga'])){
					$fotos_carga = $_POST['foto_carga'];

					foreach($fotos_carga as $foto1){
						$foto2 = explode('archivos/',$foto1);
						$foto = end($foto2);
						$db2->setQuery("INSERT INTO productos_fotos(id_producto, foto) VALUES ('$last_id', '$foto')");
						if(!$db2->alter()){
							echo alertDismiss("Error al guardar las fotos. ". $db2->getError(), "error");
							$error=1;
						}			
					}
				}
				if ($error==0) echo alertDismiss("Producto registrado correctamente", "ok");
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 
			$id_producto = $_POST['id_editar'];
			$producto = $db->clearText($_POST['producto_editar']);
			$stock_minimo = $db->clearText(quitaSeparadorMiles($_POST['stock_minimo_editar']));
			$precio_vta_min = $db->clearText(quitaSeparadorMiles($_POST['precio_vta_min_editar']));
			$precio_vta_may = $db->clearText(quitaSeparadorMiles($_POST['precio_vta_may_editar']));
			$costo = $db->clearText(quitaSeparadorMiles($_POST['costo_editar']));
			$estado = $db->clearText($_POST['estado_editar']);
			$iva = $db->clearText($_POST['iva_editar']);
			$tipo = $db->clearText($_POST['tipo_editar']);
			$precio_distribuidor = $db->clearText(quitaSeparadorMiles($_POST['precio_distribuidor_editar']));
			
			if (empty($producto)){
				echo alertDismiss("Error. Favor ingrese nombre del producto", "error");
				exit;
			}

			$db->setQuery("UPDATE productos SET producto='$producto', stock_minimo='$stock_minimo', iva='$iva', tipo='$tipo',costo='$costo', precio_vta_min='$precio_vta_min',precio_vta_may='$precio_vta_may', precio_distribuidor='$precio_distribuidor', estado='$estado', usuario='$usuario' WHERE id_producto = '$id_producto'");
	
			if(!$db->alter()){
				$db->rollback();  //Revertimos los cambios
				echo alertDismiss("Error: ". $db->getError(), "error");
			}else{
				if (isset($_POST['foto_editar'])){
					//Eliminamos las fotos anteriores e insertamos las nuevas si es que hay
					$db->setQuery("DELETE FROM productos_fotos WHERE id_producto='$id_producto'");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error al eliminar las fotos. ". $db->getError(), "error");
						exit;
					}else{
						$foto_editar = $_POST['foto_editar'];
						foreach($foto_editar as $foto1){
							$foto2 = explode('archivos/',$foto1);
							$foto = end($foto2);
							$db->setQuery("INSERT INTO productos_fotos(id_producto, foto) VALUES ('$id_producto', '$foto')");
							if(!$db->alter()){
								$db->rollback();  //Revertimos los cambios
								echo alertDismiss("Error al actualizar las fotos. ". $db->getError(), "error");
								exit;
							}			
						}
					}
				}
				
				//Modificamos el costo del producto en la última compra
				$db->setQuery("UPDATE compra_detalles SET costo='$costo', fecha_modifica='$fecha', usuario_modifica='$usuario' WHERE id_producto='$id_producto' ORDER BY id_compra_detalle DESC LIMIT 1");
				if(!$db->alter()){
					$db->rollback();  //Revertimos los cambios
					echo alertDismiss("Error al actualizar el costo. ". $db->getError(), "error");
					exit;
				}
			
				$db->commit(); //Aplicamos los cambios en BD
				echo alertDismiss("Producto modificado correctamente.", "ok");

			}

		break;
		
		case 'eliminar':
			$success = false;
			$id = $_POST['id_producto'];
			$nombre = $_POST['nombre'];
			
			//SE HACE UPDATE ANTES PARA DEJAR REGISTRADO EL USUARIO QUE VA A ELIMINAR EL REGISTRO MEDIANTE TRIGGER
			/*$db = DataBase::conectar();
			$db->setQuery("UPDATE productos SET usuario_del='$usuario' WHERE id_producto = '$id'");
	
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error");
				exit;
			}*/
			
			$db2 = DataBase::conectar();
			$db2->setQuery("DELETE FROM productos WHERE id_producto = $id");

			if($db2->alter()){
				echo alertDismiss("Producto '$nombre' eliminado correctamente", "ok");
			}else{
				echo alertDismiss("Error al eliminar '$nombre'. ". $db2->getError(), "error");
			}
			
		break;		

	}


?>