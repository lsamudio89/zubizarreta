<?php
	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];
	$usuario = $_SESSION['usuario'];
	$id_sucursal = $_SESSION['id_sucursal'];
	$id_rol = $_SESSION['id_rol'];
	
	switch ($q){
		
		case 'ver':
			
			$desde=($_REQUEST['desde']);
			$hasta=($_REQUEST['hasta']);
			$estado=$_REQUEST['estado'];
			
			if ($desde=="--" or empty($desde)){
			$desde=date('Y-m-01');	
			}
			
			if ($hasta=="--" or empty($hasta)){
			$hasta=date('Y-m-d');	
			}
			
			if ($estado){
			$ex.=" and estado='$estado'";	
			}
						
			$db = DataBase::conectar();
			$q="SELECT * FROM notas_cr where fecha>='$desde' and fecha<='$hasta' $ex and id_sucursal='$id_sucursal' ORDER BY id_nota DESC";
			$db->setQuery("$q");
							
			$rows = $db->loadObjectList();
			
			echo json_encode($rows);
		
			break;
			
		case 'cargar':
		
			$db = DataBase::conectar();
			$fecha= $db->clearText($_POST['fecha_carga']);
			$nro_factura= $db->clearText($_POST['nro_factura_carga']);
			$id_factura= $db->clearText($_POST['id_factura_carga']);
			$ruc= $db->clearText($_POST['ruc_carga']);
			$razon_social= $db->clearText($_POST['razon_social_carga']);
			$nro_nota= $db->clearText($_POST['nro_nota_carga']);
			$ar_item=$_POST['id_item'];
			$ar_cant=$_POST['cantidad'];
			$id_sucursal=$_POST['id_sucursal_carga'];
			$id_cliente=$_POST['id_cliente_carga'];
			
			$cant=count($ar_item);
			if (empty($nro_factura)){
				echo alertDismiss("Error. Ingrese Numero de Factura", "error");
				exit;
			}
				
			if (empty($id_factura)){
				echo alertDismiss("Falta id_sucursal", "error");
				exit;
			}
				
			$db->setQuery("INSERT INTO notas_cr(id_factura,id_cliente,id_sucursal,razon_social,ruc,nro_factura,nro_nota,fecha,total,estado) VALUES ('$id_factura','$id_cliente','$id_sucursal','$razon_social','$ruc','$nro_factura','$nro_nota','$fecha','$total','pendiente')");
			$db->alter();
			$id_nota=$db->getLastID();
			if($id_nota>0){
				echo alertDismiss("Nota CR registrado con éxito", "ok");
				
				//ahora recorre los items e inserta
				$x=0;
				while ($x<$cant){
				$id_producto=$ar_item[$x];	
				$cantidad=$ar_cant[$x];	
				
				$row_factura_detalle=RowMaestro('factura_detalle','id_factura',$id_factura,'id_producto',$id_producto);
				$costo=$row_factura_detalle['costo'];
				$precio_venta=$row_factura_detalle['precio_venta'];
				$total_costo=$costo*$cantidad;
				$total_venta=$precio_venta*$cantidad;
				$iva=$row_factura_detalle['iva'];
				$producto=$row_factura_detalle['producto'];
				
				$db2 = DataBase::conectar();
				$qi="INSERT INTO notas_cr_detalle(id_nota,id_producto,producto,cantidad,costo,precio_venta,total_costo,total_venta,iva,id_sucursal) 
				VALUES ('$id_nota','$id_producto','$producto','$cantidad','$costo','$precio_venta','$total_costo','$total_venta','$iva','$id_sucursal')";
				$db2->setQuery("$qi");
				$db2->alter();
				$id_nota_detalle=$db2->getLastID();
				
				//si agrego devuelve a stock
				if ($id_nota_detalle>0){
					$db3 = DataBase::conectar();	
					$qu="update stock set stock=stock+$cantidad where id_sucursal=$id_sucursal and id_producto=$id_producto";
					$db3->setQuery("$qu");
					$db3->alter();	
					
					//hacer un insert into al historial_stock
					$db4 = DataBase::conectar();
					$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
					VALUES ('$id_producto','$producto','$cantidad','$id_sucursal','Devuelto por nota CR Nro: $nro_nota',now(),'$usuario')";
					$db4->setQuery("$qi");
					$db4->alter();
					$id_historial_stock=$db4->getLastID();
					
					
				}else{
				echo alertDismiss("Error: $id_nota ". $db->getError(), "error");	
				}
				$x++;
				$monto=$monto+$total_venta;
				}
				//actualiza el monto total de la nota cr
				$db3 = DataBase::conectar();	
				$qu="update notas_cr set total='$monto' where id_nota=$id_nota";
				$db3->setQuery("$qu");
				$db3->alter();
				
			}else{
				echo alertDismiss("Error: ". $db->getError(), "error");
			}
			
		break;
					
		
		case 'anular':
			$success = false;
			$id_nota = $_GET['id_nota'];
			$db = DataBase::conectar();
			$db->setQuery("update notas_cr set estado='Anulado', usuario_anulo='$usuario', fecha_anulo=now() where id_nota='$id_nota' and estado='Pendiente'");
			if($db->alter()){
				echo alertDismiss("Nota nro <b>$id_nota</b> anulada correctamente", "ok");
				
				
				//vuelve a restar las cantidades de stock porque anulo la nota de credito
				$qu="select * from notas_cr_detalle where id_nota=$id_nota";
				$db = DataBase::conectar();
				$r=mysqli_query($db,$qu);
				while ($row=mysqli_fetch_array($r)){
				$cantidad=$row['cantidad'];
				$id_producto=$row['id_producto'];
				$id_sucursal=$row['id_sucursal'];
				$producto=$row['producto'];
				
				//vuelve a restar de sucursal
				$db3 = DataBase::conectar();	
				$qu="update stock set stock=stock-$cantidad where id_sucursal=$id_sucursal and id_producto=$id_producto";
				$db3->setQuery("$qu");
				$db3->alter();
				
				//vuelve a resgistrar en el historial de producto que se resto por anulacion de nota cr	
				$db4 = DataBase::conectar();
				$qi="INSERT INTO historial_stock(id_producto,producto,stock,id_sucursal,observaciones,fecha,usuario) 
				VALUES ('$id_producto','$producto','$cantidad','$id_sucursal','Resta de stock por Nota CR anulada id_nota=$id_nota',now(),'$usuario')";
				$db4->setQuery("$qi");
				$db4->alter();
				$id_historial_stocl=$db4->getLastID();
					
				}
							
			}else{
				echo alertDismiss("Error al anular nota nro <b>$id_nota</b>. ". $db->getError(), "error");
			}
			
		break;
		
		case 'buscar_factura':
			$row = NULL;
			$db = DataBase::conectar();
			$nro_factura= $db->clearText($_POST['nro_factura']);
			$tipo= $db->clearText($_POST['tipo']);
			if ($tipo=="f"){
				//$q="SELECT *,IFNULL((select nro_nota+1 from notas_cr order by nro_nota DESC limit 1),'000001')as nro_nota FROM facturas WHERE numero='$nro_factura' AND estado like '%pagad%'";				
				$q="SELECT f.id_factura,f.razon_social,f.estado,f.ruc as ruc,f.id_cliente,f.id_sucursal,f.fecha, CONCAT_WS('-',t.cod_establecimiento, t.punto_de_expedicion, f.numero) as numero, IFNULL((select nro_nota+1 from notas_cr order by nro_nota DESC limit 1),'000001')as nro_nota FROM facturas f INNER JOIN timbrados t ON t.id_timbrado=f.id_timbrado AND t.estado='Activo' WHERE CONCAT_WS('-',t.cod_establecimiento, t.punto_de_expedicion, f.numero) like '%$nro_factura%' and f.tipo='$tipo' AND f.estado like '%pagad%' AND f.id_sucursal=$id_sucursal";
			}else{
				$q="SELECT f.id_factura,f.razon_social,f.estado,f.ruc as ruc,f.id_cliente,f.id_sucursal,f.fecha, CONCAT_WS('-',t.cod_establecimiento, t.punto_de_expedicion, f.numero) as numero, IFNULL((select nro_nota+1 from notas_cr order by nro_nota DESC limit 1),'000001')as nro_nota FROM facturas f INNER JOIN timbrados t ON t.id_sucursal=f.id_sucursal WHERE f.numero='$nro_factura' and f.tipo='$tipo' AND f.id_sucursal=$id_sucursal AND f.estado like '%pagad%'";
			}
			$db->setQuery($q);
			$row = $db->loadObject();
			echo json_encode($row);
		break;
		
		case 'traer_items_nota':
		$id_factura=$_REQUEST['id_factura'];
		TraerItems_nota($id_factura);
		break;
	}
?>