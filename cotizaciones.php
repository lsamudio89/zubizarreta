<?php
$db = DataBase::conectar();
$q="select * from cotizaciones order by id_cotizacion DESC limit 1";
$r=@mysqli_query($db,$q);
$row_cotiza=@mysqli_fetch_array($r);
$id_cotizacion=$row_cotiza['id_cotizacion'];
$venta_dolar=$row_cotiza['venta_dolar'];
$venta_peso_arg=$row_cotiza['venta_peso_arg'];
$venta_real=$row_cotiza['venta_real'];
?>