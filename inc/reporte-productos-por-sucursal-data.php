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
				$where = "AND CONCAT_WS('|',p.id_producto, producto, t.tipo, stock_minimo, precio_vta_min, precio_vta_may, p.estado) LIKE '%$search%'";
			}
			
			if($moneda=='Gs.'){
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, s.stock, p.stock_minimo, FORMAT(IFNULL(p.costo,0),0,'de_DE') AS costo, 
				FORMAT(p.precio_vta_min,0,'de_DE') AS precio_vta_min, FORMAT(p.precio_vta_may,0,'de_DE') AS precio_vta_may, 
				FORMAT((precio_vta_min-costo),0,'de_DE') AS ganancia_min, FORMAT((precio_vta_may-costo),0,'de_DE') AS ganancia_may, p.usuario, p.estado, t.id_tipo, t.tipo
            FROM productos p 
				LEFT JOIN tipos_productos t ON t.id_tipo=p.id_tipo
				LEFT JOIN stock s ON (s.id_producto=p.id_producto AND s.id_sucursal='$id_sucursal_usu')
				WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
			}else{
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, s.stock, p.stock_minimo, IFNULL(p.costo,0) AS costo, 
				p.precio_vta_min, p.precio_vta_may, 
				(precio_vta_min-costo) AS ganancia_min, (precio_vta_may-costo) AS ganancia_may, p.usuario, p.estado, t.id_tipo, t.tipo
            FROM productos p 
				LEFT JOIN tipos_productos t ON t.id_tipo=p.id_tipo
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
		
		case 'productos_por_sucursal':
			$id_producto= $_REQUEST['id_producto'];
			$q="select * from sucursales order by id_sucursal ASC";
			$db = DataBase::conectar();
			$r=mysqli_query($db,$q);
			echo mysqli_error($db);
			echo "<table class='table table-striped'>";
			echo "
			<tr>
			<th>ID_SUCURSAL</th>
			<th>SUCURSAL</th>
			<th style='text-align:right;'>STOCK</th>
			</tr>
			";
			while ($row=mysqli_fetch_array($r)){
			$id_sucursal=$row['id_sucursal'];
			$stock=$row['stock'];
			$sucursal=$row['sucursal'];
			
			$row_stock=RowMaestro('stock','id_producto',$id_producto,'id_sucursal',$id_sucursal);
			$stock=poner_puntos($row_stock['stock']);
			
			if (empty($stock)){
			$stock=0;	
			}
			
			echo "
			<tr>
			<td>$id_sucursal</td>
			<td>$sucursal</td>
			<td style='text-align:right;'>$stock</td>
			</tr>
			";
			
			$sum_stock=$sum_stock+$stock;
			}
			echo "
			<tr>
			<th colspan=2>TOTAL DISPONIBLES</th>
			<th style='text-align:right;'>$sum_stock</th>
			</tr>
			";
			echo "</table>";
		
		break;
		
			case 'ver2':
			$db = DataBase::conectar();
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = "total";
			$search = $_REQUEST['search'];

			if (isset($search)){
				$where = "AND CONCAT_WS('|',p.id_producto, producto, p.estado) LIKE '%$search%'";
			}
			
			$luque="IFNULL((select stock from stock s where p.id_producto=s.id_producto and s.id_sucursal=3),0)";
			$sanlorenzo="IFNULL((select stock from stock s where p.id_producto=s.id_producto and s.id_sucursal=2),0)";
			$casamatriz="IFNULL((select stock from stock s where p.id_producto=s.id_producto and s.id_sucursal=1),0)";
			$deposito="IFNULL((select stock from stock s where p.id_producto=s.id_producto and s.id_sucursal=5),0)";
			$alberdi="IFNULL((select stock from stock s where p.id_producto=s.id_producto and s.id_sucursal=6),0)";
			
			$total=("$luque+$sanlorenzo+$casamatriz+$deposito");
			
			$q="SELECT SQL_CALC_FOUND_ROWS *, $luque as luque, $sanlorenzo as sanlorenzo,$casamatriz as casamatriz,$alberdi as alberdi, $deposito as deposito, $total as total FROM productos p WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit";
			$db->setQuery("$q");
				
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
			
			
	}


?>