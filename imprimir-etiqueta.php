<?php
	include ("inc/funciones.php");
	//verificaLogin("");
	$id = $_REQUEST['id_producto'];

	$db = DataBase::conectar();
	$db->setQuery("SELECT id_producto, FORMAT(precio_vta_min,0,'de_DE') as precio, producto FROM productos WHERE id_producto='$id'");
	$row = $db->loadObject();
	if (empty($row)){
		exit;
	}
	$id_producto = $row->id_producto;
	$producto = strtoupper(substr($row->producto,0,16));
	$precio = $row->precio;
	
?>
<style type="text/css">
.consolas{
	font-family: Consolas;
	font-size: 15pt;
	text-align: center;
}
.precio{
	line-height:2px;
}

.producto{
	line-height:0px;
}

.barcode{
	font-family: 'IDAHC39M Code 39 Barcode';
	font-size: 11pt;
	text-align: center;
	line-height:40px;
}

.padding{
	padding:0 1cm 0 1cm;
}

</style>

<body>

<table>
<td>
	<p class="precio consolas">Gs. <?php echo $precio; ?></p>
	<p class="barcode"><?php echo "*".$id_producto."*"; ?></p>
</td>
<td class="padding">
	<p class="precio consolas">Gs. <?php echo $precio; ?></p>
	<p class="barcode"><?php echo "*".$id_producto."*"; ?></p>
</td>
<td>
	<p class="precio consolas">Gs. <?php echo $precio; ?></p>
	<p class="barcode"><?php echo "*".$id_producto."*"; ?></p>
</td>
</table>


</body>



<script>
	var imprimir='<?php echo $_REQUEST['imprimir']; ?>';
	var recargar='<?php echo $_REQUEST['recargar']; ?>';
	if (imprimir=="si"){
		window.print();
	}
	if (recargar=="si"){
		window.onunload = refreshParent;
		function refreshParent() {
			window.opener.location.reload();
		}
	}
</script>