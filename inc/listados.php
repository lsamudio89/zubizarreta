<?php
	include ("funciones.php");
	//verificaLogin();
	$q = $_REQUEST['q'];
	
	switch ($q){

		case 'rubros':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_rubro, rubro from rubros where estado=1 order by rubro");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;

		case 'usuarios':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_usuario, nombre_usuario from usuarios order by 2");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'sucursales':
			$where="";
			if (isset($_REQUEST['id'])){
				if ($_REQUEST['id']!=1)	$where = "AND id_sucursal=".$_REQUEST['id'];
			}
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_sucursal, concat_ws(' - ',nombre_empresa,sucursal) as sucursal FROM sucursales WHERE estado=1 $where ORDER BY sucursal");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;

		case 'roles':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_rol, rol from roles where estado = 1 order by 2");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'menus':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_menu, CONCAT_WS('->',menu,submenu) as menu FROM menus WHERE estado=1 ORDER BY orden");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'departamentos':
			$db = DataBase::conectar();
			$db->setQuery("SELECT departamento from departamentos where estado = 1 order by 1");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		
		break;
		
		case 'categorias':
			$db = DataBase::conectar();
			//$db->setQuery("SELECT id_categoria, categoria, estado from categorias order by categoria");
			/*$db->setQuery("SELECT c.id_categoria AS id_cat, c.categoria AS categoria, cp.id_categoria AS cat_padre_id, cp.categoria AS cat_padre
						   FROM categorias c LEFT JOIN categorias cp ON c.id_categoria_padre = cp.id_categoria WHERE c.estado = 1 ORDER BY cp.categoria,c.categoria");*/
			
			$db->setQuery("SELECT a.id_categoria padre_id, a.categoria padre_cat, a.descripcion padre_desc, b.id_categoria hijo_id, b.categoria hijo_cat, b.descripcion hijo_desc
							FROM categorias a LEFT OUTER JOIN categorias b ON a.id_categoria = b.id_padre
							WHERE a.id_padre = 0 ORDER BY a.categoria, b.categoria");
			/*$db->setQuery("SELECT a.id_categoria padre_id, a.categoria padre_cat, a.descripcion padre_desc,
							IFNULL(b.id_categoria,a.id_categoria) as hijo_id, IFNULL(b.categoria,a.categoria) hijo_cat, 
							b.descripcion hijo_desc
							FROM categorias a LEFT OUTER JOIN categorias b ON a.id_categoria = b.id_padre
							WHERE a.id_padre = 0 ORDER BY a.categoria, b.categoria");*/
			$rows = $db->loadObjectList();
			if ($rows) echo json_encode($rows);
		break;
	}

?>