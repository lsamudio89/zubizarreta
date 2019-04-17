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
			
			$q="SELECT SQL_CALC_FOUND_ROWS *, $luque as luque, $sanlorenzo as sanlorenzo,$casamatriz as casamatriz,$alberdi as alberdi, $deposito as deposito, $total as total, p.stock_minimo 
			FROM productos p WHERE p.stock_minimo > 1 AND p.id_producto NOT IN (SELECT id_producto FROM compra_detalles WHERE estado='En tránsito') $where ORDER BY $sort $order LIMIT $offset, $limit";

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