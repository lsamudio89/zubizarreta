<?php
include("funciones.php");
verificaLogin();
$q = $_REQUEST['q'];
$usuario = $_SESSION['usuario'];
$id_sucursal = $_SESSION['id_sucursal'];

switch ($q) {
	case 'ver':
	$db = DataBase::conectar();
	$id_producto = $_REQUEST['id_producto'];
	$id_sucursal= $_REQUEST['id_sucursal'];
	
	//Parametros de ordenamiento, busqueda y paginacion
	$limit = $_REQUEST['limit'];
	$offset	= $_REQUEST['offset'];
	$order = 'DESC';
	$sort = $_REQUEST['sort'];
	if (!isset($sort)) $sort = 1;
	
	if ($id_producto){
	$ex.="and h.id_producto='$id_producto'";	
	}
	
	
	$q = "SELECT SQL_CALC_FOUND_ROWS h.id_compra_detalle,p.id_producto, p.producto, s.sucursal, DATE_FORMAT(fecha, '%d/%m/%Y %H:%i:%s') as fecha, FORMAT(IFNULL(h.flete,0),0,'de_DE') as flete, FORMAT(IFNULL(h.despacho,0),0,'de_DE') as despacho, FORMAT(IFNULL(h.costo-h.flete-h.despacho,0),0,'de_DE') as costo, FORMAT(IFNULL(h.costo,0),0,'de_DE') as total, h.usuario_modifica as usuario FROM compra_detalles h, sucursales s, productos p where s.id_sucursal=h.id_sucursal and h.id_producto=p.id_producto and h.id_sucursal_origen is null and flete>0 and despacho>0 $ex ORDER BY $sort $order LIMIT $offset, $limit";
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

if ($q=="buscar_producto"){
$db = DataBase::conectar();
$filtro = $db->clearText($_GET['term']);
$id_sucursal = $db->clearText($_GET['id_sucursal']);
$q="SELECT id_producto,producto FROM productos WHERE producto LIKE '%$filtro%' LIMIT 10";
$db->setQuery("$q");
$rows = $db->loadObjectList();
$total = 0;
foreach($rows as $r){
	$total++;
	$producto=$r->producto;
	$id_producto=$r->id_producto;
	
	$row_stock=RowMaestro('stock','id_sucursal',$id_sucursal,'id_producto',$id_producto);
	$stock_actual=$row_stock['stock'];
	
	$datos[] = array(
		'label' => $producto,		
		'stock_actual' => $stock_actual,	
		'value' => $id_producto
	  );
}
echo json_encode($datos);	
}

?>
