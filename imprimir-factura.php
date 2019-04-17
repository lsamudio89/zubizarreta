<?php
	include ("inc/funciones.php");
	verificaLogin("");
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
	$id_factura = $_REQUEST['id'];
	$db = DataBase::conectar();

	//DATOS DE LA CABECERA DE LA FACTURA
	$db->setQuery("SELECT * FROM facturas WHERE id_factura='$id_factura' ");
	$ft = $db->loadObject();
	if (empty($ft)){
		exit;
	}
	$nro_ft = $ft->numero;
	$fecha = $ft->fecha;
	$condicion = $ft->condicion_venta;
	$ruc = $ft->ruc;
	$razon_social = $ft->razon_social;
	$grabada_10 = $ft->grabada_10;
	$grabada_5 = $ft->grabada_5;
	$iva =  $ft->iva;
	$id_cliente = $ft->id_cliente;
	$total_a_pagar = $ft->total_a_pagar;
	$tipo_venta = $ft->tipo_venta;
	$descuento = abs($ft->descuento);
	$id_timbrado = $ft->id_timbrado;
        
        //DATOS DE factura detalles
	$db->setQuery("SELECT * FROM factura_detalle 
	left join productos on factura_detalle.id_producto = productos.id_producto
	WHERE factura_detalle.id_factura='$id_factura'");
	$fdet = $db->loadObjectList();
        $total_iva10=0;
        $total_iva5=0;
        $total_exenta=0;
        foreach($fdet as $r){
			$i++;
			$id_producto = $r->id_producto;
			$precio_venta_det = $r->precio_venta;
			$total_venta = $r->total_venta;
			$iva=trim($r->iva);
			$producto = trim($r->producto);
			$tipo = trim($r->tipo);
                       
               if($iva==5){
                   $iva5[$producto]=round($total_venta/21);
                   $total_venta = $total_venta;
                   
                   if($tipo == 'TASAS'){
                   $total_iva5_tasas=$total_iva5_tasas+$iva5[$producto];
                   $subtotal_5_tasa[$producto] =  $subtotal_5_tasa[$producto]+$total_venta; 
                   $suma_5_tasa = $suma_5_tasa+$subtotal_5_tasa[$producto];
                   }
                   elseif($tipo == 'GASTOS'){
                   $total_iva5_gastos=$total_iva5_gastos+$iva5[$producto];
                   $subtotal_5_gasto[$producto] =  $subtotal_5_gasto[$producto]+$total_venta; 
                   $suma_5_gasto = $suma_5_gasto+$subtotal_5_gasto[$producto];
                   }
                   elseif($tipo == 'HONORARIOS PROFESIONALES'){
                   $total_iva5_hp=$total_iva5_hp+$iva5[$producto];
                   $subtotal_5_gasto[$producto] =  $subtotal_5_gasto[$producto]+$total_venta; 
                   $suma_5_hp = $suma_5_hp+$subtotal_5_gasto[$producto];
                   }
                   elseif($tipo == 'OTROS'){
                   $total_iva5_gasto=$total_iva5_otros+$iva5[$producto];
                   $subtotal_5_gasto[$producto] =  $subtotal_5_gasto[$producto]+$total_venta; 
                   $suma_5_otros = $suma_5_otros+$subtotal_10_gasto[$producto];
                   }
                   
                   $total_iva5=$total_iva5_gastos+$total_iva5_hp+$total_iva5_otros;;
                   $suma_subtotal_iva5_gastos=$subtotal_5_gasto[$producto];
                   $suma_subtotal_5 = $suma_5_gasto+$suma_5_hp+$suma_5_otros;
               }    
               elseif($iva==10){
             	 $iva10[$producto]=round($total_venta/11);
             	 $total_venta = $total_venta; 
             	 if($tipo == 'TASAS'){
                   $total_iva10_tasas=$total_iva10_tasas+$iva10[$producto];
                   $subtotal_10_tasa[$producto] =  $subtotal_5_tasa[$producto]+$total_venta;
                   $suma_10_tasa = $suma_10_tasa+$subtotal_10_tasa[$producto];
                   }
                   elseif($tipo == 'GASTOS'){
                   $total_iva10_gastos=$total_iva10_gastos+$iva10[$producto];
                   $subtotal_10_gasto[$producto] = $subtotal_10_gasto[$producto]+$total_venta; 
                   $suma_10_gasto = $suma_10_gasto+$subtotal_10_gasto[$producto];
                   }
                   elseif($tipo == 'HONORARIOS PROFESIONALES'){
                   $total_iva10_hp=$total_iva10_hp+$iva10[$producto];
                   $subtotal_10_gasto[$producto] = $subtotal_10_gasto[$producto]+$total_venta; 
                   $suma_10_hp = $suma_10_hp+$subtotal_10_gasto[$producto];
                   }
                   elseif($tipo == 'OTROS'){
                   $total_iva10_otros=$total_iva10_otros+$iva10[$producto];
                   $subtotal_10_gasto[$producto] = $subtotal_10_gasto[$producto]+$total_venta; 
                   $suma_10_otros = $suma_10_otros+$subtotal_10_gasto[$producto];
                   }
                   
                   $total_iva10=$total_iva10_gastos+$total_iva10_hp+$total_iva10_otros;
                   $suma_subtotal_10 = $suma_10_gasto+$suma_10_hp+$suma_10_otros;
                   
                   
               }   
               elseif($iva==0){
                   $exenta[$producto]=round($total_venta/1);
                   $total_venta = $total_venta; 
                   if($tipo == 'TASAS'){
                   $total_exenta_tasas=$total_exenta_tasas+$exenta[$producto];
                   $suma_exenta_tasa = $suma_exenta_tasa+$subtotal_exenta_tasa[$producto];
                   }
                   elseif($tipo == 'GASTOS'){
                   $total_exenta_gastos=$total_exenta_gastos+$exenta[$producto];
                   $subtotal_exenta_gasto[$producto] =  $total_venta;
                   $suma_exenta_gasto = $suma_exenta_gasto+$subtotal_exenta_gasto[$producto];
                   }
                   elseif($tipo == 'HONORARIOS PROFESIONALES'){
                   $total_exenta_gasto=$total_exenta_hp+$exenta[$producto];
                   $subtotal_exenta_hp[$producto] =  $total_venta;
                   $suma_exenta_hp = $suma_exenta_hp+$subtotal_exenta_gasto[$producto];
                   }
                   elseif($tipo == 'OTROS'){
                   $total_exenta_otros=$total_exenta_otros+$exenta[$producto];
                    $subtotal_exenta_gasto[$producto] =  $total_venta;
                    $suma_subtotal_exenta_gastos=$subtotal_5_gasto[$producto];
                    $suma_exenta_otros = $suma_exenta_otros+$subtotal_exenta_gasto[$producto];
                   }
                   
                   
                   $total_exenta=$total_exenta_tasas+$total_exenta_gastos+$total_exenta_hp+$total_exenta_otros;
                   $suma_subtotal_exenta = $suma_exenta_gasto+$suma_exenta_hp+$suma_exenta_otros;
               }
               
               $suma_iva5_total = $suma_5_tasa+$suma_10_tasa; 
               $suma_iva5_10 = $total_iva10+$total_iva5; 
               $impuesto_tasas = $total_iva5_tasas+$total_iva10_tasas+$total_exenta_tasas;
               
               $impuesto_gastos = $total_iva5_gastos+$total_iva10_gastos+$total_exenta_gastos;
              $subtotales_gastos = $suma_subtotal_5+$suma_subtotal_10+$suma_subtotal_exenta;
              
                   

		}
        
        

	$row_cliente=RowMaestro('clientes','id_cliente',$id_cliente);
	$direccion=$row_cliente['direccion'];
	$telefono=$row_cliente['telefono'];
	
	if ($condicion=="contado"){
		$x_contado=" X";
		$x_credito="";
	}else{
		$x_credito=" X";
		$x_contado="";
	}

        
	//DATOS DEL TIMBRADO
	$db->setQuery("SELECT * FROM timbrados WHERE id_timbrado='$id_timbrado'");
	$tim = $db->loadObject();
	$timbrado = $tim->timbrado;
	$cod_est = $tim->cod_establecimiento;
	$punto_exp = $tim->punto_de_expedicion;
	$ini_vigencia = fechaLatina($tim->inicio_vigencia);
	$fin_vigencia = fechaLatina($tim->fin_vigencia);
	$ruc_empresa = $tim->ruc;
        
        
	
	//MONTO TOTAL A LETRAS
	require("inc/numeros-letras.php");		
	$v = new EnLetras();
	$letras = strtoupper($v->ValorEnLetras($ft->total_a_pagar,""));


	
?>

<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:4px 6px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:4px 6px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-c3ow{border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-0pky{border-color:inherit;text-align:left;vertical-align:top}
.tg .tg-xldj{border-color:inherit;text-align:left}
.tg .tg-dvpl{border-color:inherit;text-align:right;vertical-align:top}
.tg .tg-0lax{text-align:left;vertical-align:top}
</style>
<table class="tg" style="undefined;table-layout: fixed; width: 838px">
<colgroup>
<col style="width: 195px">
<col style="width: 81px">
<col style="width: 73px">
<col style="width: 147px">
<col style="width: 120px">
<col style="width: 111px">
<col style="width: 111px">
</colgroup>
  <tr>
    <th class="tg-0pky" colspan="4">FECHA DE EMISIÓN: <?php echo $fecha; ?></th>
    <th class="tg-0pky" colspan="3">Condición              Contado   ( <?php echo $x_contado; ?>  )               Crédito( <?php echo $x_credito; ?>  )</th>
  </tr>
  <tr>
    <td class="tg-xldj" colspan="5">NOMBRE:  <?php echo strtoupper($razon_social); ?></td>
    <td class="tg-0pky" colspan="2">RUC/CI: <?php echo $ruc; ?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="5">DIRECCIÓN: <?php echo strtoupper($direccion); ?></td>
    <td class="tg-0pky" colspan="2">TELÉFONO: <?php echo strtoupper($telefono); ?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="7">CONTRIBUYENTE IRACIS&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SI (&nbsp;&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NO&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;)</td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="2">NRO:</td>
    <td class="tg-0pky" colspan="5">CONCEPTO:</td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="7"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">1) REEMBOLSO DE IMPUESTOS/TASAS</td>
    <td class="tg-c3ow">IMPUESTOS TASAS</td>
    <td class="tg-c3ow" colspan="3">VALORES DE VENTAS / SERVICIOS</td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4"></td>
    <td class="tg-c3ow">EXENTAS</td>
    <td class="tg-c3ow">5%</td>
    <td class="tg-c3ow">10%</td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Tasas Judiciales (Ventas)</td>
    <td class="tg-0pky">
        <?php 
        if(!empty($exenta['Tazas Judiciales (Ventas)'])){
            echo poner_puntos($exenta['Tazas Judiciales (Ventas)']);   
        }elseif(!empty($iva5['Tazas Judiciales (Ventas)'])){
            echo poner_puntos($iva5['Tazas Judiciales (Ventas)']);    
        }elseif(!empty($iva10['Tazas Judiciales (Ventas)'])){
            echo poner_puntos($iva10['Tazas Judiciales (Ventas)']);    
        }
        
        ?>
    </td>
    <td class="tg-0pky" style="text-align:right;"></td>
    <td class="tg-0pky" style="text-align:right;"></td>
    <td class="tg-0pky" style="text-align:right;"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Tasas Judiciales (Hipotecas-Prendas)</td>
    <td class="tg-0pky"><?php 
        if(!empty($exenta['Tazas Judiciales (Hipotecas-Prendas)'])){
            echo poner_puntos($exenta['Tazas Judiciales (Hipotecas-Prendas)']); 
        }elseif(!empty($iva5['Tazas Judiciales (Hipotecas-Prendas)'])){
            echo poner_puntos($iva5['Tazas Judiciales (Hipotecas-Prendas)']);  
        }elseif(!empty($iva10['Tazas Judiciales (Hipotecas-Prendas)'])){
            echo poner_puntos($iva10['Tazas Judiciales (Hipotecas-Prendas)']);  
        }
        ?></td>
    <td class="tg-0pky" style="text-align:right;"></td>
    <td class="tg-0pky" style="text-align:right;"></td>
    <td class="tg-0pky" style="text-align:right;"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Impuesto Municipal (Ventas / Créditos)</td>
    <td class="tg-0pky"><?php 
        if(!empty($exenta['Impuestos Municipal (Ventas / Créditos)'])){
            echo poner_puntos($exenta['Impuestos Municipal (Ventas / Créditos)']); 
        }elseif(!empty($iva5['Impuestos Municipal (Ventas / Créditos)'])){
            echo poner_puntos($iva5['Impuestos Municipal (Ventas / Créditos)']);  
        }elseif(!empty($iva10['Impuestos Municipal (Ventas / Créditos)'])){
            echo poner_puntos($iva10['Impuestos Municipal (Ventas / Créditos)']);  
        }
        ?></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Impuesto Inmobiliarios</td>
    <td class="tg-0pky"><?php 
        if(!empty($exenta['Impuesto Inmobiliarios'])){
            echo poner_puntos($exenta['Impuesto Inmobiliarios']); 
        }elseif(!empty($iva5['Impuesto Inmobiliarios'])){
            echo poner_puntos($iva5['Impuesto Inmobiliarios']);  
        }elseif(!empty($iva10['Impuesto Inmobiliarios'])){
            echo poner_puntos($iva10['Impuesto Inmobiliarios']);  
        }
        ?></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Tasas Especiales</td>
    <td class="tg-0pky"><?php 
        if(!empty($exenta['Tasas Especiales'])){
            echo poner_puntos($exenta['Tasas Especiales']); 
        }elseif(!empty($iva5['Tasas Especiales'])){
            echo poner_puntos($iva5['Tasas Especiales']);  
        }elseif(!empty($iva10['Tasas Especiales'])){
            echo poner_puntos($iva10['Tasas Especiales']);  
        }
        ?></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3">Tasas Automotores</td>
    <td class="tg-0pky"><?php 
        if(!empty($exenta['Tasas Automotores'])){
            echo poner_puntos($exenta['Tasas Automotores']); 
        }elseif(!empty($iva5['Tasas Automotores'])){
            echo poner_puntos($iva5['Tasas Automotores']);  
        }elseif(!empty($iva10['Tasas Automotores'])){
            echo poner_puntos($iva10['Tasas Automotores']);  
        }
        ?></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-dvpl" colspan="3">TOTAL:</td>
    <td class="tg-0pky"><?php echo poner_puntos($impuesto_tasas); ?></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">2) GASTOS</td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Gastos Administrativos</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_otros['Gastos Administrativos']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Gastos Administrativos']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Gastos Administrativos']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certificado de condiciones de dominio</td>
     <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_otros['Certificado de condiciones de dominio']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Certificado de condiciones de dominio']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Certificado de condiciones de dominio']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certidicado de Anotaciones personales</td>
     <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_otros['Certificado de Anotaciones personales']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Certificado de Anotaciones personales']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Certificado de Anotaciones personales']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certidicados de Vigencia Poder/SD/AI/Capitulación</td>
     <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Certidicados de Vigencia Poder/SD/AI/Capitulación']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Certificados de Vigencia Poder/SD/AI/Capitulación']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Certificados de Vigencia Poder/SD/AI/Capitulación']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Expedición de copias/ fotocopias</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Expedición de copias/ fotocopias']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Expedición de copias/ fotocopias']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Expedición de copias/ fotocopias']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certificado Municipal</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Certificado Municipal']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Certificado Municipal']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Certificado Municipal']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certificado ANDE / ESSAP</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Certificado ANDE / ESSAP']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Certificado ANDE / ESSAP']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Certificado ANDE / ESSAP']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Certificado Catastral</td>
    <td class="tg-0pky"><?php echo poner_puntos($exenta['Certificado Catastral']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($iva5['Certificado Catastral']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($iva10['Certificado Catastral']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Registro Público de Comercio</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Registro Público de Comercio']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Registro Público de Comercio']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Registro Público de Comercio']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Inscripción</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Inscripción']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Inscripción']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Inscripción']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">Legalizaciones</td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['Legalizaciones']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['Legalizaciones']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['Legalizaciones']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
    <td class="tg-0pky"></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">3) HORARIOS PROFESIONALES</td>
     <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['HORARIOS PROFESIONALES']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['HORARIOS PROFESIONALES']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['HORARIOS PROFESIONALES']);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="4">4) OTROS</td>
     <td class="tg-0pky"><?php echo poner_puntos($subtotal_exenta_gasto['OTROS']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_5_gasto['OTROS']);?></td>
    <td class="tg-0pky"><?php echo poner_puntos($subtotal_10_gasto['OTROS']);?></td>
  </tr>
  <tr>
    <td class="tg-dvpl" colspan="4">SUB TOTALES:</td>
    <td class="tg-0pky"style="text-align:right;"><?php echo poner_puntos($suma_subtotal_exenta);?></td>
    <td class="tg-0pky" style="text-align:right;"><?php echo poner_puntos($suma_subtotal_5);?></td>
    <td class="tg-0pky" style="text-align:right"><?php echo poner_puntos($suma_subtotal_10);?></td>
  </tr>
  <tr>
    <td class="tg-0pky" colspan="3"></td>
    <td class="tg-0pky" colspan="4">TOTAL: <spanstyle="text-align:right"><?php echo poner_puntos($subtotales_gastos)?></span></td>
  </tr>
  <tr>
    <td class="tg-0lax">Liquidación del IVA:</td>
    <td class="tg-0lax" colspan="2">(5%):<?php echo poner_puntos($total_iva5);?></td>
    <td class="tg-0lax">(10%):<?php echo poner_puntos($total_iva10);?></td>
    <td class="tg-0lax" colspan="3">TOTAL IVA: <?php echo poner_puntos($suma_iva5_10);?></td>
  </tr>
  <tr>
    <td class="tg-0lax" colspan="5">TOTAL REEMBOLSO DE IMPUESTOS / TASAS</td>
    <td class="tg-0lax" colspan="2" style="text-align:right"><?php echo poner_puntos($impuesto_tasas); ?></td>
  </tr>
  <tr>
    <td class="tg-0lax" colspan="5">TOTAL VENTAS / SERVICIOS</td>
    <td class="tg-0lax" colspan="2" style="text-align:right"><?php echo poner_puntos($subtotales_gastos); ?></td>
  </tr>
  <tr>
    <td class="tg-0lax" colspan="5">TOTAL</td>
    <td class="tg-0lax" colspan="2"></td>
  </tr>
  <tr>
    <td class="tg-0lax" colspan="5">TOTAL A PAGAR:</td>
    <td class="tg-0lax" colspan="2"style="text-align:right"><?php $suma_tasas_gastos=$impuesto_tasas+$subtotales_gastos;
    echo poner_puntos($suma_tasas_gastos);?></td>
  </tr>
</table>