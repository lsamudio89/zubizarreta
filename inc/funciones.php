<?php
include("mysql.php");

function url(){
	$host=$_SERVER['HTTP_HOST'];
	return "http://$host/jdm";
}

function datosConfig($x){
	$db = DataBase::conectar();
	$db->setQuery("SELECT $x FROM configuracion");
	$rows = $db->loadObject();
	return $rows->$x;
}

function poner_puntos($number){
if (is_numeric($number)){
$nro=number_format($number,0, ".", ".");
//$array=explode('.',$nro);
//$retorno=$array[0];
return $nro;
}
}



function RowMaestro($tabla,$campo,$id,$campo2="",$id2="",$campo3="",$id3="",$ex=""){
if (!empty($tabla) and !empty($campo) and !empty($id)){
if (!empty($campo2) and !empty($id2)){
$q="select * from $tabla where $campo='$id' and $campo2='$id2' $ex";	
}
else{
$q="select * from $tabla where $campo='$id' $ex limit 1";
}
if (!empty($campo3) and !empty($id3) and !empty($campo2) and !empty($id2)){
$q="select * from $tabla where $campo='$id' and $campo2='$id2' and $campo3='$id3' $ex";	
}
//echo $q;
$db = DataBase::conectar();
$r=@mysqli_query($db,$q);
$retorno=@mysqli_fetch_array($r);
return $retorno;
}
}

function datosUsuario($id){
	$db = DataBase::conectar();
	$db->setQuery("SELECT * from usuarios where md5(id_usuario)='$id'");
	$u = $db->loadObject();
	return $u;
}

function datosSucursal($id_usuario){
	$db = DataBase::conectar();
	$db->setQuery("SELECT * FROM sucursales l INNER JOIN usuarios u ON u.id_sucursal=l.id_sucursal AND md5(u.id_usuario)='$id_usuario'");
	$u = $db->loadObject();
	return $u;
}

function fechaLatina($fecha){
    $fecha = substr($fecha,0,10);
	/*$date = new DateTime($fecha);
	return $date->format('d/m/Y');*/
    list($anio,$mes,$dia)=explode("-",$fecha);
	if (!$anio){
		return "";
	}else{
		return $dia."/".$mes."/".$anio;
	}
}

function fechaLatinaHora($fecha){
	/*$date = new DateTime($fecha);
	return $date->format('d/m/Y H:i');*/
    list($anio,$mes,$dia)=explode("-",$fecha);
	$hora = substr($fecha,11,8);
	if (!$anio){
		return "";
	}else{
		return substr($dia,0,2)."/".$mes."/".$anio." ".$hora;
	}
}

function fechaMYSQL($fecha){
    $fecha = substr($fecha,0,10);
    list($dia,$mes,$anio)=explode("/",$fecha);
    return $anio."-".$mes."-".$dia;
}
function fechaMYSQLHora($fecha){
    $fecha_sola = substr($fecha,0,10);
	$fecha_hora = substr($fecha,11,16);
    list($dia,$mes,$anio)=explode("/",$fecha_sola);
	list($hora,$min) = explode(":",$fecha_hora);
    return $anio."-".$mes."-".$dia." ".$hora.":".$min;
}

function getAutoincrement($table){
	$db = DataBase::conectar();
	$db->setQuery("SELECT LPAD(`AUTO_INCREMENT`,9,'0') as auto FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
	$r = $db->loadObject()->auto;
	return $r;
}

function redondearGs($gs){
	if (strlen($gs) >= 4){
	   $a = (int)$gs / 100;
	   $b = round($a);
	   $c = $b * 100;
	   return $c;
	}else if (strlen($gs) <= 3)	{
		$a = (int)$gs / 100;
		$b = round($a);
	    $c = $b * 100;
		return $c;
	} 
}
function separadorMiles($number){
	if (is_numeric($number)){
		$nro=number_format($number,0, ".", ".");
		return $nro;
	}
}

function quitaSeparadorMiles($x){
	if($x) {
		return str_replace('.','',$x);
	}else{
		return 0;
	}
}

function fechaEspanol($x){
	$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
	$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	if ($x == "dia"){
		return $dias[date('w')];
	}else{
		return $dias[date('w')].", ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
	}
}


function menu($id){
	$db = DataBase::conectar();
	$db->setQuery("select us.rol, rm.id_menu, um.menu, um.submenu, um.url, um.orden, us.nombre_usuario 
		from usuarios us 
		inner join roles_menu rm on us.rol = rm.id_rol 
		inner join menus um on um.id_menu = rm.id_menu where um.estado = 1 and md5(us.id_usuario) = '$id' order by orden");
	$menus = $db->loadObjectList();

	$salida_menu = "<ul class='nav navbar-nav'>";
		//<li class='active'><a href='./index.php'>Inicio</a></li>";

	$menuActual = '';
	$usoSubmenu = 0;
	
	foreach($menus as $m){
		$id_menu = "menu".$m->id_menu;
		$submenu = $m->submenu;
		$menu = $m->menu;
		$url = $m->url;
		$nombre = ucfirst($m->nombre_usuario);

		if ($submenu == '-'){
			if ($usoSubmenu > 0){
				$salida_menu .= "</ul></li>";
			}
			$salida_menu .= "<li><a href='$url'>$menu</a></li>";
			$nombre_menu = $id;
		}else{
			if ($menu != $menuActual){
				if ($usoSubmenu > 0){
				$salida_menu .= "</ul></li>";
			}
				$salida_menu .= "<li class='dropdown'>
				  <a href='#' class='dropdown-toggle' data-toggle='dropdown'>$menu<b class='caret'></b></a>
				  <ul class='dropdown-menu'>";
				 
					$salida_menu .= "<li><a href='$url'>$submenu</a></li>";
				 
				  $menuActual = $menu;
				  $usoSubmenu++;
			}else{
				//SE CORRIGE LOGICA, HABIA PROBLEMAS CUANDO 2 MENU TENIAN UN SOLO SUBMENU CADA UNO (CASO USUARIO JUAN SUMINISTRO)
					$salida_menu .= "<li><a href='$url'>$submenu</a></li>";
				//	$usoSubmenu++;
			}
			
		}
	}

	##Nuevo menu de usuario
	$salida_menu .= "</ul></ul>

	<ul class='nav navbar-nav navbar-right'>
            <li class='dropdown'>
              <a href='#' class='dropdown-toggle' data-toggle='dropdown'><span class='glyphicon glyphicon-user'></span> $nombre<b class='caret'></b></a>
              <ul class='dropdown-menu'>
                <!--<li><a href='mi-cuenta.php'><span class='glyphicon glyphicon-edit'></span> Mi Cuenta</a></li>
                <li><a href='./sugerencias.php'><span class='glyphicon glyphicon glyphicon-info-sign'></span> Sugerencias</a></li>-->
                <li><a href='./logout.php'><span class='glyphicon glyphicon-log-out'></span> Salir</a></li>
              </ul>
            </li>
          </ul>
          ";
          // Si queres que se vea este form tenes que poner debajo del ul de arriba
      //     <form class='navbar-form navbar-right' method='POST' action='resultado-busqueda.php' role='search'>
		    //     <div class='form-group'>
		    //       <input type='text' class='form-control' name='codigo' placeholder='Ingrese Cod. Producto' pattern='.{1,}' title='5 caracteres como mínimo' required>
		    //     </div>
		    //     <button type='submit' class='btn btn-default' name='buscar_producto'>Buscar</button>
		    // </form>
	echo $salida_menu;
}

function nombrePagina($pagina){
	$db2 = DataBase::conectar();
	$db2->setQuery("SELECT titulo from menus where url like '%".$pagina."'");
	$pa = $db2->loadObject();
	return $pa->titulo;
}

function verificaLogin($pag=""){
	session_start([
		 'cookie_lifetime' => 86400,
	]);
	
	if(!isset($_SESSION['id_usuario']) && !isset($_COOKIE['3a60fbdR3c0Rd4R0ebf5'])){
		header('Location:index.php');
	}else if (isset($_COOKIE['3a60fbdR3c0Rd4R0ebf5'])){
		$_SESSION['id_usuario']=$_COOKIE['3a60fbdR3c0Rd4R0ebf5'];
		$_SESSION['usuario']=datosUsuario($_SESSION['id_usuario'])->nombre_usuario;
	}
	
	if($pag){
		//VERIFICAMOS SI TIENE PERMISO SOBRE LA PÁGINA
		$pag_tmp = explode("/",$pag);
		$pagina = end($pag_tmp);
		$id_usu = $_SESSION['id_usuario'];
		$db = DataBase::conectar();
		$db->setQuery("SELECT u.id_usuario FROM usuarios u INNER JOIN roles_menu rm ON rm.id_rol=u.rol INNER JOIN menus m ON rm.id_menu=m.id_menu WHERE md5(id_usuario)='$id_usu' AND m.url like '%/$pagina'");
		$row = $db->loadObject();
		if (!$row){
			echo "<p style='font:bold 16px Tahoma'>PAGINA NO ENCONTRADA<br>Si cree que se trata de un error, favor consulte con el administrador del sistema.<br><a href=".url()."/>Volver al Inicio</a></p>";
			exit;
		}
	}
	
}

function alertDismiss($msj, $tipo){
	
	switch ($tipo){
		case 'error':
			$salida = "<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-exclamation-sign'>&nbsp;</span>$msj</div>";
		break;
		
		case 'error_span':
			$salida = "<span class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-exclamation-sign'>&nbsp;</span>$msj</span>";
		break;
		
		case 'ok':
			$salida = "<div class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-ok'>&nbsp;</span>$msj</div>";
		break;
		
		case 'ok_span':
			$salida = "<span class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-ok'>&nbsp;</span>$msj</span>";
		break;
		
		case 'yellow':
			$salida = "<div class='alert alert-warning alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-ok'>&nbsp;</span>$msj</div>";
		break;
		
	}
	return $salida; 
}

function piePagina(){
	$pie = "<div id='footer'>
		  		<div class='container'>
					<p class='text-muted'>".datosConfig('nombre_sistema')." - Desarrollado por <a href='http://www.freelancer.com.py' target='blank'>Freelancers del Paraguay</a>
					</p>
				</div>
			</div>";
	return $pie;
}

function exportarExcel($datos, $titulo){
		
	$hoy=date('d-m-Y');
	$nombre='xls/Exportado_'.$titulo.'_'.$hoy.".xls";

	
	$xml = simplexml_load_string($datos);
	$salida = "<table border='1'>";
	foreach ($xml->Worksheet->Table->Row as $row) {
	   $celda = $row->Cell;
	   $salida .= "<tr>".$celda;
	   //echo "\t";
	   foreach ($celda as $cell) {
			$salida .= "<td>".$cell->Data."</td>";
			//echo "\t";
		}
		$salida .= "</tr>";
	}
	$salida .= "</table>";
	//print $salida;
	
	file_put_contents($nombre, utf8_decode($salida));
	
	echo $nombre;
}

function SelectSucursales($estado,$id_sucursal_post=""){
$q="select * from sucursales where estado='$estado' order by id_sucursal ASC";
$db = DataBase::conectar();
$r=mysqli_query($db,$q);
echo "<select class='form-control input-sm' name='id_sucursal' id='id_sucursal' onchange='Dispinibilidad_sucursal();'>
";
while ($row=mysqli_fetch_array($r)){
$id_sucursal=$row['id_sucursal'];
$sucursal=$row['sucursal'];

	if ($id_sucursal_post==$id_sucursal){
	$selcted="selected";	
	}else{
	$selcted="";	
	}

	echo "
	<option value='$id_sucursal' $selcted>$sucursal</option>
	";	
}
echo "<select>";
}

function TraerItems_nota($id_factura){
$q="select * from factura_detalle where id_factura='$id_factura'";
$db = DataBase::conectar();
$r=mysqli_query($db,$q);
echo "
<h3>Items a Devolver</h3>
<table class='table table-hover'>
<tr>
<th style='text-align:center;'>#</th>
<th style='text-align:center;'>Producto</th>
<th style='text-align:center;'>Cantidad</th>
<th style='text-align:center;'>Precio U.</th>
<th style='text-align:center;'>SubTotal</th>
<th style='text-align:center;'>Descontar</th>
</tr>
";
$i=1;
while ($row=mysqli_fetch_array($r)){
$cantidad=$row['cantidad'];
$descontar=$row['descontar'];
$id_factura_detalle=$row['id_factura_detalle'];
$descripcion=($row['producto']);
$id_producto=($row['id_producto']);
$precio_u=($row['precio_venta']);
$precio_u_punto=poner_puntos($precio_u);
$subtotal=$precio_u*$cantidad;
$subtotal_punto=poner_puntos($subtotal);

if($descontar==0){
$descontar="";
}

$checkbox="<input type='checkbox' style='width:25px; height:25px; margin:0px 0px 0px 3px; float:right;' name='id_item[]' onclick='Habilitar_campo($id_producto);' value='$id_producto' id='id_item_$id_producto'>";
$descontar="<input disabled style='text-align:center; width:50px; float:right; color:black;' type='text' value='$descontar' placeholder=0 id='cantidad_$id_producto' name='cantidad[]'>";

echo 
"
<tr>
<td style='text-align:center;'>$i</td>
<td style='text-align:left;'>$descripcion</td>
<td style='text-align:center;'>$cantidad</td>
<td style='text-align:right;'>$precio_u_punto</td>
<td style='text-align:right;'>$subtotal_punto</td>
<td style='text-align:center;'>$checkbox $descontar</td>
</tr>
";
$sum_subtotal=$sum_subtotal+$subtotal;

$i++;
}
$sum_subtotal_punto=poner_puntos($sum_subtotal);
echo 
"
<tr>
<th style='text-align:center;' colspan=4>TOTAL:</th>
<th style='text-align:right;'>$sum_subtotal_punto</th>
<th style='text-align:center;'>&nbsp;</th>
</tr>
";
echo "</table>";	
}

?>