<?php
 
	include ("funciones.php");
	verificaLogin();

	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_usuario = $_SESSION['id_usuario'];
	$moneda = datosSucursal($id_usuario)->moneda;
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	
	switch ($q){
		
		case 'buscar_cliente':
			$row = NULL;
			$db = DataBase::conectar();
			$ruc = $db->clearText($_POST['ruc']);
			if (!empty($ruc)){
				$db->setQuery("SELECT id_cliente, ruc, razon_social, tipo FROM clientes WHERE ruc like '$ruc-%' AND estado=1");
				$row = $db->loadObject();
				
				if (empty($row)){
					$db->setQuery("SELECT id_cliente, ruc, razon_social, tipo FROM clientes WHERE ruc = '$ruc' AND estado=1");
					$row = $db->loadObject();
					if (empty($row)){
						$row = array('id_cliente'=>'', 'ruc'=>'', 'razon_social'=>'no registrado');
					}
				}
			}
			echo json_encode($row);
		break;
		
		case 'buscar_razon_social':
		
			$db = DataBase::conectar();
		
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			if (!isset($limit)) $limit = 10;
			$offset	= $_REQUEST['offset'];
			if (!isset($offset)) $offset = 0;
			$order = $_REQUEST['order'];
			if (!isset($order)) $order = 'asc';
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 2;
			$search = $_REQUEST['search'];



			if (isset($search)){
				$where = "AND CONCAT_WS('|',ruc,razon_social) LIKE '%$search%'";
			}
			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS * FROM clientes WHERE estado<>0 $where ORDER BY $sort $order LIMIT $offset, $limit");
			$rows = $db->loadObjectList();
			$db->setQuery("SELECT FOUND_ROWS() as total");		
			$total_row = $db->loadObject();
			$total = $total_row->total;

			
			if ($rows){
				$salida = array('total' => $total, 'rows' => $rows);
			}else{
				$salida = array('total' => 0, 'rows' => array());
			}
			
			/*if (empty($rows)){
				$buscar_array = explode(" ", $buscar);
				$buscar="";
				foreach ($buscar_array as $b){
					$buscar .= "%".$b."%";
				}
				$db->setQuery($query);
				$rows = $db->loadObjectList();
			}*/
			
			echo json_encode($salida);
			
			/*$db->setQuery("SELECT * FROM clientes WHERE estado<>0 ORDER BY razon_social");
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);*/
		break;
		
		
		
		case 'buscar':
		
			$db = DataBase::conectar();
			$buscar = $db->clearText($_GET['filtro']);
			$query = "SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, IFNULL(s.stock,0) AS stock, p.stock_minimo, pf.foto,
                                                FORMAT(IFNULL(p.costo,0),0,'de_DE') AS costo, 
						FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, 
						FORMAT(p.precio_distribuidor,0,'de_DE') AS precio_distribuidor,
                                                p.iva as iva,
                                                /*FORMAT(( p.iva as iva),*/
						FORMAT((p.precio_vta_min-p.costo),0,'de_DE') AS ganancia_min, 
						FORMAT((p.precio_vta_may-p.costo),0,'de_DE') AS ganancia_may
						FROM productos p
						LEFT JOIN productos_fotos pf ON pf.id_producto=p.id_producto 
						LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal')
						WHERE CONCAT_WS('|',p.id_producto,p.producto) LIKE '%$buscar%' GROUP BY p.id_producto";
			
			$db->setQuery($query);
			$rows = $db->loadObjectList();	
			
			if (empty($rows)){
				$buscar_array = explode(" ", $buscar);
				$buscar="";
				foreach ($buscar_array as $b){
					$buscar .= "%".$b."%";
				}
				$db->setQuery($query);
				$rows = $db->loadObjectList();
			}
			echo json_encode($rows);
			
		break;
		
		case 'traer_nota':
			$row = NULL;
			$db = DataBase::conectar();
			$nro_nota = $db->clearText($_POST['nro_nota']);
			$db->setQuery("SELECT total FROM notas_cr WHERE nro_nota='$nro_nota' AND estado = 'Pendiente'");
			$row = $db->loadObject();
			
			if (!empty($row)){
				echo json_encode(["total"=>$row->total]);
			}else{
				echo json_encode($row);
			}
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
		
		case 'facturar':

			$descuento=0;
			$db = DataBase::conectar();
			
			//Desactivamos el autocommit para que no guarde los cambios hasta asegurarnos que no haya ningún error
			$db->autocommit(FALSE); 
			
			//DATOS DEL TIMBRADO
			$db->setQuery("SELECT id_timbrado, timbrado FROM timbrados WHERE id_sucursal='$id_sucursal' AND estado='Activo'");
			$tim = $db->loadObject();
			$id_timbrado = $tim->id_timbrado;
			$timbrado = $tim->timbrado;
			
			$tipo = $db->clearText($_POST['tipo']);
			if ($tipo=="t"){
				$estado = "Pagado";
			}else{
				$estado = "Pagada";
			}
			$ruc = $db->clearText($_POST['ruc']);
			$razon_social = $db->clearText($_POST['razon_social']);
			$condicion = $db->clearText($_POST['condicion']);
			$id_cliente = $db->clearText($_POST['id_cliente']);
			$descuento = $db->clearText($_POST['descuento']);
			$tipo_venta = $db->clearText($_POST['tipo_venta']);
			$nota_cr= $db->clearText($_POST['nota_cr']);
			$fecha_vencimiento= $db->clearText($_POST['fecha_vencimiento']);
			
			if (empty($ruc)){
				echo json_encode(["mensaje" => alertDismiss("Error. Favor ingrese RUC o CI del cliente", "error")]);
				exit;
			}
			if (empty($razon_social)){
				echo json_encode(["mensaje" => alertDismiss("Error. Favor ingrese Nombre o Razón Social del cliente", "error")]);
				exit;
			}
			
			//Recorremos los productos para sumar los totales y después insertar en tabla facturas
			$grabada_10=0; $iva_10=0; $venta_total=0; $total_costo=0; $total_a_pagar=0; $cantidad_sum=0;
                       
                        
			foreach ($_POST['productos'] as $key => $p1){
                                        $id_producto = $db->clearText($p1['id_producto']);
					$cantidad = $db->clearText(quitaSeparadorMiles($p1['cantidad']));
					//$cantidad_sum += $cantidad;
					//$venta_total += $db->clearText(quitaSeparadorMiles($p1['venta_total']));
					$row_iva = RowMaestro('productos', 'id_producto', $id_producto);
                                        $iva = $row_iva['iva'];
                                        if($iva == 5){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p1['venta_5']));
                                        }else if($iva == 10){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p1['venta_10']));
                                        }else if($iva == 0){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p1['exenta']));
                                        }
                                        
                                        $total_venta = $cantidad * $precio_venta;
                                        
                                        $total_costo += $db->clearText(quitaSeparadorMiles($p1['total_costo']));

			}
                        
                        
			$grabada_10 = $total_venta-abs($descuento);
			$iva_10 = $grabada_10 / 11; //La grabada tiene 10% de IVA incluido, con esto sacamos el IVA
			$imponible = $grabada_10-$iva_10; //Monto total sin IVA
			$total_a_pagar = $grabada_10;
			
			//Bloqueamos las tablas para que nadie pueda escribir en ella (en caso que haya concurrencia, cuida el nro de factura en caso que justo ingresen 2 ventas al mismo tiempo)
			$db->setQuery("LOCK TABLES facturas WRITE, factura_detalle WRITE, pagos WRITE, stock WRITE, sucursales WRITE, notas_cr WRITE, historial_stock WRITE");
			$db->alter();
			
			//No usamos grabada_5, iva_5 ni exenta, en este caso todos los productos se venden con IVA 10% incluido (grabada_10)
			$db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_sucursal='$id_sucursal' AND tipo='$tipo' AND estado != 'Anulado' AND id_timbrado='$id_timbrado'");
			$r_max = $db->loadObject();	
			$max = $r_max->ultimo_nro+1;
			
                        
			if ($condicion=='credito'){
				$estado="Pendiente";
				$saldo=$total_a_pagar;	
			}
                        if (empty($saldo)){
                            $saldo = 0;
                        }
			$db->setQuery("INSERT INTO facturas(numero,tipo,fecha,condicion_venta,id_timbrado,timbrado,id_sucursal,id_cliente,ruc,razon_social,cantidad,grabada_10,iva_10,descuento,total_costo,imponible,total_a_pagar,moneda,estado,usuario,tipo_venta,saldo,vencimiento)
			VALUES ('$max','$tipo',NOW(),'$condicion',$id_timbrado,'$timbrado','$id_sucursal','$id_cliente','$ruc','$razon_social','$cantidad_sum','$grabada_10','$iva_10','$descuento','$total_costo','$imponible','$total_a_pagar','$moneda','$estado','$usuario','$tipo_venta','$saldo','$fecha_vencimiento')");
			
			if(!$db->alter()){
				echo json_encode(["mensaje" => alertDismiss("Error al guardar la Factura. ". $db->getError(), "error")]);
				$db->rollback();  //Revertimos los cambios
				$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
				$db->alter();
				exit;
			}else{
				$last_id = $db->getLastID();
				
				//actualiza si utilizo una nota cr
				if ($nota_cr>0){
				$qu="update notas_cr set id_factura2='$last_id', estado='Utilizado' where nro_nota='$nota_cr'";
				$db->setQuery("$qu");
				$db->alter();			
				}

				//Recorremos los productos nuevamente para insertar en factura_detalle
                               
                           
				foreach ($_POST['productos'] as $key => $p2){
					$id_producto = $db->clearText($p2['id_producto']);
					$producto = $db->clearText($p2['producto']);
					$cantidad = $db->clearText(quitaSeparadorMiles($p2['cantidad']));
                                        $row_iva = RowMaestro('productos', 'id_producto', $id_producto);
                                        $iva = $row_iva['iva'];
                                        if($iva == 5){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p2['venta_5']));
                                        }else if($iva == 10){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p2['venta_10']));
                                        }else if($iva == 0){
                                            $precio_venta = $db->clearText(quitaSeparadorMiles($p2['exenta']));
                                        }
                                        
                                        $total_venta = $cantidad * $precio_venta;
                                            
                                        $db->setQuery("INSERT INTO factura_detalle (id_factura, id_producto, producto, cantidad, precio_venta, total_venta, iva) 
									VALUES ('$last_id', '$id_producto', '$producto', '$cantidad', '$precio_venta', '$total_venta','$iva')");
					if(!$db->alter()){
						echo json_encode(["mensaje" => alertDismiss("Error al insertar lo detalles de la factura. ". $db->getError(), "error")]);
						$db->rollback();  //Revertimos los cambios
						$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
						$db->alter();
						exit;
					}
					
					//Descontamos el stock
					$db->setQuery("UPDATE stock SET stock = stock - $cantidad, usuario = '$usuario', fecha = NOW() WHERE id_producto = '$id_producto' AND id_sucursal = $id_sucursal");
					if(!$db->alter()){
						echo json_encode(["mensaje" => alertDismiss("Error al actualizar el stock. ". $db->getError(), "error")]);
						$db->rollback();  //Revertimos los cambios
						$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
						$db->alter();
						exit;
					}else{
					
					$row_stock=RowMaestro('stock','id_sucursal',$id_sucursal,'id_producto',$id_producto);
					$stock_actual=$row_stock['stock'];			
						
					//hacer un insert into al historial_stock cuando se vende
					$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
					VALUES ('$id_producto','$producto','$stock_actual','$id_sucursal','$cantidad  vendido a $razon_social',now(),'$usuario')";
					$db->setQuery("$qi");
					$db->alter();	
					$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
					$db->alter();
					}
					
				}
				
				//Insertamos los pagos
				include('../cotizaciones.php');
				foreach ($_POST['pagos'] as $key => $p3){
					$metodo_pago = $db->clearText($p3['metodo_pago']);
					$monto = $db->clearText(quitaSeparadorMiles($p3['monto']));
					
					
					if ($metodo_pago=="Tarjeta de Crédito" || $metodo_pago=="Tarjeta de Débito"){
						$comision_tarj = round(($monto*10/100), 0);
					}else{
						$comision_tarj=0;
					}
					if ($metodo_pago=='Efectivo'){
					$monto_conversion=$monto;
					$venta=1;	
					}
					elseif ($metodo_pago=="Efectivo-peso"){
					$monto_conversion=round($monto/$venta_peso_arg);	
					$venta=$venta_peso_arg;
					}
					elseif ($metodo_pago=="Efectivo-real"){
					$monto_conversion=round($monto/$venta_real);	
					$venta=$venta_real;
					}
					elseif ($metodo_pago=="Efectivo-usd"){
					$monto_conversion=round($monto/$venta_dolar);	
					$venta=$venta_dolar;
					}

					$db->setQuery("INSERT INTO pagos (id_factura, metodo_pago, monto, comision_tarj, moneda, fecha) VALUES ('$last_id', '$metodo_pago','$monto', $comision_tarj, '$moneda', NOW())");
					if(!$db->alter()){
						echo json_encode(["mensaje" => alertDismiss("Error al insertar los pagos. ". $db->getError(), "error")]);
						$db->rollback();  //Revertimos los cambios
						$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
						$db->alter();
							
						exit;
					}
					if ($metodo_pago != "Descuento" and $metodo_pago != "Nota"){
						$db->setQuery("UPDATE sucursales SET disponibilidades = disponibilidades + ($monto-$comision_tarj) WHERE id_sucursal = $id_sucursal");
						if(!$db->alter()){
							echo json_encode(["mensaje" => alertDismiss("Error al actualizar el disponible. ". $db->getError(), "error")]);
							$db->rollback();  //Revertimos los cambios
							$db->setQuery("UNLOCK TABLES"); //Desbloqueamos las tablas
							$db->alter();
							exit;
						}
					}
				}

				$db->commit(); //Insertamos los datos en la BD
			}
	
			if ($tipo == "f") {
				echo json_encode(["mensaje" => alertDismiss("Factura generada con éxito.", "ok"), "id_factura" => $last_id]);
			}else{
				echo json_encode(["mensaje" => alertDismiss("Ticket generado con éxito.", "ok"), "id_factura" => $last_id]);
			}
			
		break;
	}


?>