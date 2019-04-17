<?php
	include ("funciones.php");
	verificaLogin("");
	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_rol = datosUsuario($id_usuario)->rol;
	
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
				$where = "AND CONCAT_WS(' ',f.numero,f.tipo,f.fecha,f.ruc,f.razon_social,f.estado,f.usuario,f.tipo_venta,s.sucursal) LIKE '%$search%'";
			}
			$where_sucursal="";
			if ($id_rol > 1){
				$where_sucursal=" WHERE f.id_sucursal=$id_sucursal";
			}
			$query="SELECT SQL_CALC_FOUND_ROWS f.id_factura, f.numero, f.cantidad, CASE f.tipo when 't' then 'Comprobante' when 'f' then 'Factura' end as tipo, DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') AS fecha, f.ruc, f.razon_social, FORMAT(f.total_a_pagar,0,'de_DE') AS total_a_pagar, f.descuento, f.estado, f.usuario, DATE_FORMAT(f.fecha_anulada,'%d/%m/%Y %H:%i:%s') AS fecha_anulada,f.usuario_anulo, f.tipo_venta, f.id_sucursal, s.sucursal FROM facturas f LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal $where_sucursal
			$where ORDER BY $sort $order LIMIT $offset, $limit";
			
			$db->setQuery($query);
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
			
		case 'anular':
			
			$db = DataBase::conectar();
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 
			$ok=0;
			$id = $_POST['id'];
			$nro = $_POST['nro'];
			$id_suc = $_POST['id_suc'];
			
			//ANULAMOS LA FACTURA
			$db->setQuery("UPDATE facturas SET estado='Anulado', fecha_anulada=now(), usuario_anulo='$usuario' WHERE id_factura=$id");
			if(!$db->alter()){
				echo alertDismiss("Error al anular la factura: ". $db->getError(), "error");
				$db->rollback();  //Revertimos los cambios
				exit;
			}
			
			//SUMAMOS STOCK
			$db->setQuery("SELECT id_producto,cantidad,id_factura FROM factura_detalle WHERE id_factura=$id");		
			$rows_stock = $db->loadObjectList();
			foreach($rows_stock as $rs){
				$cantidad=$rs->cantidad;
				$id_producto=$rs->id_producto;
				$id_factura=$rs->id_factura;
				
				$row_factura=RowMaestro('facturas','id_factura',$id_factura);
				$id_sucursal=$row_factura['id_sucursal'];
				
				$db->setQuery("UPDATE stock SET stock=stock+$cantidad WHERE id_producto=$id_producto and id_sucursal='$id_sucursal'");
				if(!$db->alter()){
					echo alertDismiss("Error al actualizar el stock ". $db->getError(), "error");
					$db->rollback();  //Revertimos los cambios
					exit;
				}
				$row_stock=RowMaestro('stock','id_sucursal',$id_sucursal,'id_producto',$id_producto);
				$stock_actual=$row_stock['stock'];	
				
				$row_producto=RowMaestro('productos','id_producto',$id_producto);
				$producto=$row_producto['producto'];
				$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
					VALUES ('$id_producto','$producto','$stock_actual','$id_sucursal','$cantidad FACTURA ANULADA NRO: $id_factura',now(),'$usuario')";
				$db->setQuery("$qi");
				if(!$db->alter()){
				echo alertDismiss("Error al actualizar historial stock ". $db->getError(), "error");
				$db->rollback();  //Revertimos los cambios
				exit;
				}
			}
			
			//SUMAMOS LOS PAGOS PARA DEVOLVER AL DISPONIBLE
			$db->setQuery("SELECT monto, comision_tarj FROM pagos WHERE id_factura=$id");		
			$rows_p = $db->loadObjectList();
			$montos_pagados=0; $tj_pagados=0;
			foreach($rows_p as $rp){
				$montos_pagados += $rp->monto;
				$tj_pagados += $rp->comision_tarj;
			}
			$total_pagados = $montos_pagados-$tj_pagados;
			
			//PONEMOS EN NEGATIVO SU PAGO
			$db->setQuery("UPDATE pagos SET monto=-$total_pagados, fecha=now(), comision_tarj=0 WHERE id_factura=$id");
			if(!$db->alter()){
				echo alertDismiss("Error al cerar pagos ". $db->getError(), "error");
				$db->rollback();  //Revertimos los cambios
				exit;
			}
			
			//SUMAMOS AL DISPONIBLE
			$db->setQuery("UPDATE sucursales SET disponibilidades=disponibilidades-$total_pagados WHERE id_sucursal = $id_suc");
			if(!$db->alter()){
				echo alertDismiss("Error al actualizar el disponible: ". $db->getError(), "error");
				$db->rollback();  //Revertimos los cambios
				exit;
			}
			
			$db->commit(); //Insertamos los datos en la BD
			echo alertDismiss("Factura o Comprobante '$nro' anulado exitosamente.", "ok");
			
		break;
	
				
	}
?>