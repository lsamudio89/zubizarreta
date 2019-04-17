<?php
	include ("funciones.php");
	verificaLogin();
	$q = $_REQUEST['q'];
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = $_SESSION['id_sucursal'];
	$moneda = datosSucursal($id_usuario)->moneda;
	$id_rol = datosUsuario($_SESSION['id_usuario'])->rol;
	
	switch ($q){
		
		case 'total_ventas':
			$db = DataBase::conectar();
			
			$tiempo = $_POST['tiempo'];
			
			if ($tiempo=="hoy"){
				$titulo = "VENTAS DE HOY";
				$where_actual = "DATE(fecha) = CURDATE()"; //Hoy
				$where_anterior = "DATE(fecha) = CURDATE() - INTERVAL 7 DAY"; //Hace una semana
			}else if  ($tiempo=="mes"){
				$titulo = "VENTAS DEL MES";
				$where_actual = "EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE())"; //Mes actual
				$where_anterior = "EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()-INTERVAL 1 MONTH)"; //Mes anterior
			}
			
			if ($id_rol>1){
				$sucursal = "AND id_sucursal=$id_sucursal";
			}
			
			//Datos actual
			$db->setQuery("SELECT IFNULL(SUM(cantidad),0) as cant_prod, IFNULL(SUM(total_a_pagar),0) as ventas FROM facturas WHERE $where_actual $sucursal AND estado NOT LIKE 'Anulad%'");
			$rows1 = $db->loadObject();
			$cant_actual = $rows1->cant_prod;
			$ventas_actual = $rows1->ventas;
			
			//Semana pasada o mes pasado
			$db->setQuery("SELECT IFNULL(SUM(cantidad),0) as cant_prod, IFNULL(SUM(total_a_pagar),0) as ventas FROM facturas WHERE $where_anterior $sucursal AND estado NOT LIKE 'Anulad%'");
			$rows2 = $db->loadObject();
			$cant_anterior = $rows2->cant_prod;
			$ventas_anterior = $rows2->ventas;

			$salida[] = estadisticaComparativa($tiempo, $cant_actual, $cant_anterior, 'productos', $titulo);
			$salida[] = estadisticaComparativa($tiempo, $ventas_actual, $ventas_anterior, $moneda, $titulo);
			
			echo json_encode($salida);
			
		break;
		
		case 'ganancias':
			$db = DataBase::conectar();
			$tiempo = $_POST['tiempo'];
			$ganancia_actual=0;
			$ganancia_anterior=0;
			if ($tiempo=="hoy"){
				$titulo = "GANANCIAS DE HOY";
				$where_actual = "DATE(f.fecha) = CURDATE()"; //Hoy
				$where_anterior = "DATE(f.fecha) = CURDATE() - INTERVAL 7 DAY"; //Hace una semana
			}else if  ($tiempo=="mes"){
				$titulo = "GANANCIAS DEL MES";
				$where_actual = "EXTRACT(YEAR_MONTH FROM f.fecha) = EXTRACT(YEAR_MONTH FROM CURDATE())"; //Mes actual
				$where_anterior = "EXTRACT(YEAR_MONTH FROM f.fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()-INTERVAL 1 MONTH)"; //Mes anterior
			}
			
			if ($id_rol>1){
				$sucursal = "AND f.id_sucursal=$id_sucursal";
			}
			
			//Datos actual
			//$db->setQuery("SELECT IFNULL(SUM(total_a_pagar-total_costo),0) as ganancia FROM facturas WHERE $where_actual $sucursal AND estado NOT LIKE  'Anulad%'");
			$db->setQuery("SELECT IFNULL(f.total_a_pagar,0)-IFNULL(f.total_costo,0)-IFNULL(SUM(p.comision_tarj),0) AS ganancia FROM facturas f LEFT JOIN pagos p ON f.id_factura=p.id_factura 
			WHERE p.metodo_pago != 'Descuento' AND $where_actual $sucursal AND f.estado NOT LIKE 'Anulad%' GROUP BY f.id_factura");
			$rows1 = $db->loadObjectList();
			foreach($rows1 as $r1){
				$ganancia_actual += $r1->ganancia;
			}
			
			//Semana pasada o mes pasado
			$db->setQuery("SELECT IFNULL(f.total_a_pagar,0)-IFNULL(f.total_costo,0)-IFNULL(SUM(p.comision_tarj),0) AS ganancia FROM facturas f LEFT JOIN pagos p ON f.id_factura=p.id_factura 
			WHERE p.metodo_pago != 'Descuento' AND $where_anterior $sucursal AND f.estado NOT LIKE 'Anulad%' GROUP BY f.id_factura");
			$rows2 = $db->loadObjectList();
			foreach($rows2 as $r2){
				$ganancia_anterior += $r2->ganancia;
			}

			//$salida[] = estadisticaComparativa($tiempo, $cant_actual, $cant_anterior, 'productos', $titulo);
			$salida[] = estadisticaComparativa($tiempo, $ganancia_actual, $ganancia_anterior, $moneda, $titulo);
			echo json_encode($salida);
			
		break;
		
		case 'gastos':
			$db = DataBase::conectar();
			$tiempo = $_POST['tiempo'];
			
			if ($id_rol>1){
				$sucursal = "AND id_sucursal=$id_sucursal";
			}
			
			if ($tiempo=="hoy"){
				$titulo = "GASTOS DE HOY";
				$where_actual = "DATE(fecha) = CURDATE()"; //Hoy
				$where_anterior = "DATE(fecha) = CURDATE() - INTERVAL 7 DAY"; //Hace una semana
			}else if ($tiempo=="mes"){
				$titulo = "GASTOS DEL MES";
				$where_actual = "EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE())"; //Mes actual
				$where_anterior = "EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()-INTERVAL 1 MONTH)"; //Mes anterior
			}
			
			//Datos actual
			$db->setQuery("SELECT IFNULL(SUM(monto),0) as monto FROM gastos WHERE $where_actual $sucursal AND estado != 0");
			$rows1 = $db->loadObject();
			$gasto_actual = $rows1->monto;
			
			//Semana pasada o mes pasado
			$db->setQuery("SELECT IFNULL(SUM(monto),0) as monto FROM gastos WHERE $where_anterior $sucursal AND estado != 0");
			$rows2 = $db->loadObject();
			$gasto_anterior = $rows2->monto;

			$salida[] = estadisticaComparativa($tiempo, $gasto_actual, $gasto_anterior, $moneda, $titulo);
			echo json_encode($salida);
			
		break;
		
		case 'productos_sucursal':

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
		
		
		case 'grafico_ventas':
			$db = DataBase::conectar();
			
			//ULTIMOS 30 DIAS
			/*$db->setQuery("SELECT f.fecha, IFNULL(sum(f.total_gs),0) as total_ventas, fd.cantidad FROM facturas f 
			LEFT JOIN (SELECT fd2.id_factura, SUM(fd2.cantidad) AS cantidad FROM factura_detalle fd2 INNER JOIN facturas f2 ON f2.id_factura = fd2.id_factura 
			GROUP BY DATE_FORMAT(f2.fecha, '%Y-%m-%d')) AS fd ON f.id_factura = fd.id_factura
			WHERE DATE(f.fecha) BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE() AND f.estado NOT LIKE  'Anulad%' 
			GROUP BY DATE_FORMAT(f.fecha, '%Y-%m-%d')
			ORDER BY f.id_factura");*/
			
			//DEL MES ACTUAL
			$db->setQuery("SELECT UNIX_TIMESTAMP(DATE(f.fecha))*1000 AS fecha, IFNULL(sum(f.total_gs),0) as total_ventas, fd.cantidad FROM facturas f 
			LEFT JOIN (SELECT fd2.id_factura, SUM(fd2.cantidad) AS cantidad FROM factura_detalle fd2 INNER JOIN facturas f2 ON f2.id_factura = fd2.id_factura 
			GROUP BY DATE_FORMAT(f2.fecha, '%Y-%m-%d')) AS fd ON f.id_factura = fd.id_factura
			WHERE DATE(f.fecha) BETWEEN subdate(curdate(), (day(curdate())-1)) AND CURDATE() AND f.estado NOT LIKE 'Anulad%' 
			GROUP BY DATE_FORMAT(f.fecha, '%Y-%m-%d')
			ORDER BY f.fecha");
			$rows = $db->loadObjectList();
			$suma_total_gs = 0;
			foreach($rows as $r){
				$fecha = $r->fecha;
				$monto = $r->total_ventas;
				$cantidad = $r->cantidad;
				$total_ventas[] = array($fecha, $monto);
				$cantidad_ventas[] = array($fecha, $cantidad);
				$suma_total_gs = $suma_total_gs + $monto;
			}
			$salida = array("total_ventas" => $total_ventas, "cantidad_ventas" => $cantidad_ventas, "suma_total_gs" => $suma_total_gs);
			echo json_encode($salida, JSON_NUMERIC_CHECK);
		
		break;
		
		case 'top_productos':
			$db = DataBase::conectar();
			
			//TOP 20 PRODUCTOS MAS VENDIDOS DESDE EL INICIO
			$db->setQuery("SELECT p.producto, SUM(fd.cantidad) cantidad FROM factura_detalle fd 
			LEFT JOIN productos p ON p.id_producto = fd.id_producto
			LEFT JOIN facturas f ON f.id_factura = fd.id_factura
			WHERE f.estado NOT LIKE 'Anulad%'
			GROUP BY fd.id_producto
			ORDER BY cantidad DESC LIMIT 20");
			$rows = $db->loadObjectList();
			
			foreach($rows as $r){
				$producto = utf8_decode($r->producto);
				$cantidad = $r->cantidad;
				$top_productos[] = array($producto, $cantidad);
			}
			$salida = array("top_productos" => $top_productos);
			echo json_encode($salida, JSON_NUMERIC_CHECK);
		
		break;
	
	}
	
	function estadisticaComparativa($tiempo, $valor_actual, $valor_anterior, $unidad, $titulo){
		if ($tiempo=="hoy"){
			$tiempo = fechaEspanol('dia');
		}
		if(stristr($titulo, 'gastos') === FALSE) {
			$movimiento = "ventas";
		}else{
			$movimiento = "gastos";
		}
		//Para comparar con la semana anterior, tuvo que haber al menos un producto vendido porque no se puede dividir por 0
		if ($valor_anterior > 0){
			$porcentaje = number_format(round(100 - ($valor_actual / $valor_anterior * 100),1), 1, ',', '');

			if ($porcentaje < 0){
			   //Se vendieron más productos que la semana anterior
			   $estadistica = abs($porcentaje)."% más que el $tiempo pasado";
			   $diferencia = poner_puntos(abs($valor_actual - $valor_anterior))." $unidad más que el $tiempo pasado";
			}elseif ($porcentaje > 0){
				//Se vendieron menos que la semana anterior
			   $estadistica = abs($porcentaje)."% menos que el $tiempo pasado";
			   $diferencia =poner_puntos(abs($valor_anterior - $valor_actual))." $unidad menos que el $tiempo pasado";
			}else{
				//No hay diferencias
			   $estadistica = "Igual que el $tiempo pasado";
			}
		}else{
			$estadistica = "El $tiempo pasado no hubo $movimiento";		
			$diferencia = $estadistica;		
		}
		$resultado['titulo'] = $titulo;
		$resultado['valor_actual'] = poner_puntos($valor_actual);
		$resultado['diferencia'] = $diferencia;
		$resultado['estadistica'] = $estadistica;
		
		return $resultado;
	}

?>