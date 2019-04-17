<?php
$url = "http://cotizext.maxicambios.com.py/maxicambios.xml";
//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$json=curl_exec($ch);
// Closing
curl_close($ch);

$arr = json_decode($json);

print_r($arr);

exit;
				
//$conten=file_get_contents($sitio);

//trae todas las compras de las monedas
preg_match_all('%<compra>(.*?)</compra>%i', $conten, $compras);
$compra_dolar=$compras[1][0];
$compra_peso_arg=$compras[1][1];
$compra_real=$compras[1][2];
$compra_peso_uy=$compras[1][3];
$compra_euro=$compras[1][4];
$compra_yen=$compras[1][5];

//trae todas las ventas de las monedas
preg_match_all('%<venta>(.*?)</venta>%i', $conten, $ventas);
$venta_dolar=$ventas[1][0];
$venta_peso_arg=$ventas[1][1];
$venta_real=$ventas[1][2];
$venta_peso_uy=$ventas[1][3];
$venta_euro=$ventas[1][4];
$venta_yen=$ventas[1][5];

//trae todas las monedas
preg_match_all('%<nombre>(.*?)</nombre>%i', $conten, $nombres);
$nombre_1=$nombres[1][0];
$nombre_2=$nombres[1][1];
$nombre_3=$nombres[1][2];
$nombre_4=$nombres[1][3];
$nombre_5=$nombres[1][4];
$nombre_6=$nombres[1][5];

?>