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
			$estado = $_REQUEST['estado'];
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 3;
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|',cd.id_compra_detalle,cd.estado,cd.id_producto, p.producto, cd.fecha) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS cd.id_compra_detalle, cd.id_producto, p.producto, cd.cantidad, IFNULL(cd.cant_recibida,0) AS cant_recibida, 
				cd.cantidad-IFNULL(cd.cant_recibida,0) AS cant_pendiente, '0' as cant_a_recibir, cd.flete , cd.despacho, FORMAT(IFNULL(cd.costo+cd.flete+cd.despacho,0),0,'de_DE') AS total,FORMAT(IFNULL(cd.costo,0),0,'de_DE') AS costo, cd.fecha, cd.usuario, cd.fecha_modifica, cd.usuario_modifica, cd.estado
				FROM compra_detalles cd LEFT JOIN productos p ON cd.id_producto=p.id_producto WHERE /*IFNULL(cd.cant_recibida,0) < cantidad AND*/ cd.estado='$estado' $where ORDER BY $sort $order LIMIT $offset, $limit");
			}else{
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS cd.id_compra_detalle, cd.id_producto, p.producto, cd.cantidad, IFNULL(cd.cant_recibida,0) AS cant_recibida, 
				cd.cantidad-IFNULL(cd.cant_recibida,0) AS cant_pendiente, '0' as cant_a_recibir, 0 as flete, IFNULL(cd.costo,0) AS costo, cd.fecha, cd.usuario, cd.fecha_modifica, cd.usuario_modifica, cd.estado
				FROM compra_detalles cd LEFT JOIN productos p ON cd.id_producto=p.id_producto WHERE /*IFNULL(cd.cant_recibida,0) < cantidad AND*/ cd.estado='$estado' $where ORDER BY $sort $order LIMIT $offset, $limit");
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
					
		case 'guardar':
		
			$db = DataBase::conectar();
			
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 
			
			$hay=0;

			foreach ($_POST['datos'] as $key => $val){
				$upd_costo="";
				$id_compra_detalle = $val['id_compra_detalle'];
				$cant_a_recibir = $db->clearText(quitaSeparadorMiles($val['cant_a_recibir']));
				$costo_solo = $db->clearText(quitaSeparadorMiles($val['costo']));
				$flete = $db->clearText(quitaSeparadorMiles($val['flete']));
				$despacho = $db->clearText(quitaSeparadorMiles($val['despacho']));
				$id_producto = $val['id_producto'];
				
				if ($cant_a_recibir > 0){
					$hay++;
					if ($flete > 0){
						$costo_total = $costo_solo+$flete+$despacho;
						$total_flete=$cant_a_recibir*$flete;
						$upd_flete="flete='$flete',";
						//Restamos del disponible de Casa Matriz
						$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades-$total_flete-$despacho WHERE id_sucursal=5");
						if(!$db->alter()){
							$db->rollback();  //Revertimos los cambios
							echo alertDismiss("Error al actualizar el disponible: ". $db->getError(), "error_span");
							exit;
						}
					}
					
					//si metio despacho actualizar
					if ($despacho>0){
						$upd_despacho="despacho='$despacho',";
					}
					
					//Actualizamos el costo en tabla productos
					$db->setQuery("UPDATE productos SET costo='$costo_total' WHERE id_producto='$id_producto'");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error al actualizar el costo en productos: ". $db->getError(), "error_span");
						exit;
					}

						
					//Actualizamos en la compra
					$db->setQuery("UPDATE compra_detalles SET cant_recibida = IFNULL(cant_recibida,0) + '$cant_a_recibir', costo='$costo_solo', $upd_flete $upd_despacho fecha_modifica=NOW(), usuario_modifica='$usuario' WHERE id_compra_detalle = '$id_compra_detalle'");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error: ". $db->getError(), "error_span");
						exit;
					}
					
					//Sumamos el stock en deposito
				
					//Verificamos si el producto ya existe en tabla stock
					$db->setQuery("SELECT id_producto FROM stock WHERE id_producto=$id_producto AND id_sucursal=5");
					$row = $db->loadObject();
					//Si no existe insertamos el producto con stock 0
					if(empty($row)){
						$db->setQuery("INSERT INTO stock(id_producto, stock, id_sucursal, usuario, fecha) VALUES($id_producto, 0,5, '$usuario', NOW())");
						if(!$db->alter()){
							$db->rollback();  //Revertimos los cambios
							echo alertDismiss("Error al insertar stock: ". $db->getError(), "error");
							exit;
						}
					}
					
					$db->setQuery("UPDATE stock SET stock=stock+$cant_a_recibir, usuario='$usuario', fecha=NOW() WHERE id_producto=$id_producto AND id_sucursal=5");
					if(!$db->alter()){
						$db->rollback();  //Revertimos los cambios
						echo alertDismiss("Error al actualizar stock: ". $db->getError(), "error_span");
						exit;
					}
					
					//Comprobamos si la cantidad recibida ya alcanzó a la cantidad comprada
					$db->setQuery("SELECT cantidad,cant_recibida FROM compra_detalles WHERE id_compra_detalle='$id_compra_detalle' AND cantidad=IFNULL(cant_recibida,0)");
					$row = $db->loadObject();
					if($row->cantidad){
						$db->setQuery("UPDATE compra_detalles SET estado='Completado' WHERE id_compra_detalle='$id_compra_detalle'");
						if(!$db->alter()){
							$db->rollback();  //Revertimos los cambios
							echo alertDismiss("Error al actualizar estado: ". $db->getError(), "error_span");
							exit;
						}
					}
				}
			}
			if ($hay>0){
				$db->commit(); //Aplicamos los cambios en BD
				echo alertDismiss("Compras actualizadas correctamente.", "ok_span");
			}else{
				echo alertDismiss("Error. No se ingresaron cantidades a recibir.", "error_span");
			}
			

		break;
		
		case 'extraviado':
			$db = DataBase::conectar();
			$id_compra_detalle = $_POST['id_compra_detalle'];
			$db->setQuery("UPDATE compra_detalles SET estado='Extraviado', fecha_modifica=NOW(), usuario_modifica='$usuario' WHERE id_compra_detalle='$id_compra_detalle'");
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error_span");
			}else{
				echo alertDismiss("Producto marcado como extraviado.", "ok_span");
			}
		break;
		
		case "guardar-flete":
			$db = DataBase::conectar();
			$id= $_POST['id'];
			$flete= $_POST['flete'];
			$costo= $_POST['costo'];
			$despacho= $_POST['despacho'];
			$q="UPDATE compra_detalles SET flete='$flete', costo='$costo', despacho='$despacho' WHERE id_compra_detalle='$id'";
			$db->setQuery("$q");
			if(!$db->alter()){
				echo alertDismiss("Error: ". $db->getError(), "error_span");
			}else{
				echo "Flete Actualizado $q";
			}
			break;

	}


?>