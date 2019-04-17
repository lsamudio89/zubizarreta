<?php

  include ("inc/funciones.php");

  //$pag = basename($_SERVER['PHP_SELF']);

  verificaLogin();

  setlocale(LC_ALL,"es_ES");

  $id_usuario = $_SESSION['id_usuario'];

  $rol = datosUsuario($id_usuario)->rol;

  $moneda = datosSucursal($id_usuario)->moneda;

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">

    <meta name="author" content="">

    <link rel="shortcut icon" href="images/favicon.png">



    <title><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])) ?></title>



    <!-- Bootstrap core CSS -->

    <link href="css/bootstrap.min.css" rel="stylesheet">

	



    <script src="js/jquery-1.11.1.min.js"></script>

    <script type="text/javascript" src="js/funciones.js"></script>

	

	<script src="js/bootstrap.min.js"></script>

    

	<!-- Bootstrap table -->

	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">

	<script src="bootstrap-table/bootstrap-table.js"></script>

	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>

	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>

	

	<!-- Custom style -->

    <link href="css/theme.css" rel="stylesheet">



	<style type="text/css">

		panel-body-ventas {

			padding: 5px

		}

	</style>

	

	<script type="text/javascript">

		

	$(document).ready(function () {

		

		var $table = $('#tabla');	



		$table.bootstrapTable({

			height: $(window).height()-318,

			pageSize: Math.floor(($(window).height()-318)/28)-4,

			sortOrder: 'desc',

			columns: [

				[

					{	field: 'id_producto', align: 'left', valign: 'middle', title: 'ID', sortable: true	}, 

					{	field: 'producto', align: 'left', valign: 'middle', title: 'Nombre / Descripción', sortable: true},

					{	field: 'luque', align: 'center', valign: 'middle', title: 'Luque', sortable: true	},

					{	field: 'casamatriz', align: 'center', valign: 'middle', title: 'Casa Matriz', sortable: true	},

					{	field: 'sanlorenzo', align: 'center', valign: 'middle', title: 'San Lorenzo', sortable: true	},

					{	field: 'alberdi', align: 'center', valign: 'middle', title: 'Alberdi', sortable: true	},

					{	field: 'deposito', align: 'center', valign: 'middle', title: 'Deposito', sortable: true	},

					{	field: 'total', align: 'center', valign: 'middle', title: 'Total', sortable: true	}

				]

			]

		});

		

		//Altura de tabla automatica

		$(window).resize(function () {

			$table.bootstrapTable('refreshOptions', { 

				height: $(window).height()-318,

				pageSize: Math.floor(($(window).height()-318)/28)-4

			});

		});

		

		//CLIMA

		 $.getJSON('http://api.openweathermap.org/data/2.5/weather?q=Asuncion,py&lang=sp&units=metric&APPID=1f05a9b147a23c5235ec1c6a0126a274&callback=?', function (json) {

			 var t1 = json.main.temp;

			 var t2 = json.weather[0].description.toUpperCase();

			 $("#weather").html("Clima Asunción: "+t1+' C° / '+t2);

		 });



	});

	

	//TOTAL DE PRODUCTOS VENDIDOS EN CANTIDAD

	function totalVentasCantidad(tiempo) {

		var moneda = "";

		var unidad = "<span style='font-size:18px'> productos</span>";		

		

		if (tiempo=="mes"){

			$("#dash1_titulo").attr("onclick","totalVentasCantidad('hoy')");

		}else{

			$("#dash1_titulo").attr("onclick","totalVentasCantidad('mes')");	

		}

		

		$.ajax({ 

			dataType: 'json', async: true, cache: false, url: 'inc/dashboard.php', type: 'POST', data: {q: 'total_ventas', tiempo: tiempo },

			beforeSend: function(){

				$("#dash1_valor").html("<img src='images/loading.gif'>");

			},

			success: function (datos){

				dash1_datos = datos;

				$("#dash1_titulo").html(datos[0].titulo);

				$("#dash1_valor").html(moneda+datos[0].valor_actual+unidad);

				$("#dash1_est").html(datos[0].estadistica);

				$("#dash1_dif").html(datos[0].diferencia);

			},

			error: function (xhr) {

				$("#dash1_valor").html("0");

				$("#mensaje").html(alertDismissJS("Error al obtener los datos: " + xhr.status + " " + xhr.responseText, 'error'));

			}

		});

	}

	

	//TOTAL DE PRODUCTOS VENDIDOS EN MONEDA

	function totalVentasMoneda(x){

		if (x==0){

			$("#dash1_moneda").attr("onclick","totalVentasMoneda(1)");	

			var moneda = "";

			var unidad = "<span style='font-size:18px'> productos</span>";		

		}else{

			$("#dash1_moneda").attr("onclick","totalVentasMoneda(0)");	

			var moneda = "<?php echo $moneda?> ";

			var unidad = "";

		}

		$("#dash1_titulo").html(dash1_datos[x].titulo);

		$("#dash1_valor").html(moneda+dash1_datos[x].valor_actual+unidad);

		$("#dash1_est").html(dash1_datos[x].estadistica);

		$("#dash1_dif").html(dash1_datos[x].diferencia);

	}

	

	//TOTAL GANANCIAS

	function totalGanancias(tiempo) {

		if (tiempo=="mes"){

			$("#dash2_titulo").attr("onclick","totalGanancias('hoy')");

		}else{

			$("#dash2_titulo").attr("onclick","totalGanancias('mes')");	

		}

		$.ajax({ 

			dataType: 'json', async: true, cache: false, url: 'inc/dashboard.php', type: 'POST', data: {q: 'ganancias', tiempo: tiempo },

			beforeSend: function(){

				$("#dash2_valor").html("<img src='images/loading.gif'>");

			},

			success: function (datos){

				dash2_datos = datos;

				$("#dash2_titulo").html(datos[0].titulo);

				$("#dash2_valor").html("<?php echo $moneda?> "+datos[0].valor_actual);

				$("#dash2_est").html(datos[0].estadistica);

				$("#dash2_dif").html(datos[0].diferencia);

			},

			error: function (xhr) {

				$("#dash2_valor").html("0");

				$("#mensaje").html(alertDismissJS("Error al obtener los datos: " + xhr.status + " " + xhr.responseText, 'error'));

			}

		});

	}

	

	//TOTAL GASTOS

	function totalGastos(tiempo) {

		if (tiempo=="mes"){

			$("#dash3_titulo").attr("onclick","totalGastos('hoy')");

		}else{

			$("#dash3_titulo").attr("onclick","totalGastos('mes')");	

		}

		$.ajax({ 

			dataType: 'json', async: true, cache: false, url: 'inc/dashboard.php', type: 'POST', data: {q: 'gastos', tiempo: tiempo },

			beforeSend: function(){

				$("#dash3_valor").html("<img src='images/loading.gif'>");

			},

			success: function (datos){

				dash3_datos = datos;

				$("#dash3_titulo").html(datos[0].titulo);

				$("#dash3_valor").html("<?php echo $moneda?> "+datos[0].valor_actual);

				$("#dash3_est").html(datos[0].estadistica);

				$("#dash3_dif").html(datos[0].diferencia);

			},

			error: function (xhr) {

				$("#dash3_valor").html("0");

				$("#mensaje").html(alertDismissJS("Error al obtener los datos: " + xhr.status + " " + xhr.responseText, 'error'));

			}

		});

	}



	totalVentasCantidad('mes');

	totalGanancias('mes');

	totalGastos('mes');



	

	//HORA

	function startTime() {

		var today=new Date();

		var h=today.getHours();

		var m=today.getMinutes();

		var s=today.getSeconds();

		m = checkTime(m);

		s = checkTime(s);

		$('#hora').html(h+":"+m+":<span style='font-size:17px'>"+s+"</span>");

		//$('#hora').html(h+":"+m);

		var t = setTimeout(function(){startTime()},1000);

	}



	function checkTime(i) {

		if (i<10) {i = "0" + i};  // add zero in front of numbers < 10

		return i;

	}

	

	function intercambioValores(ocultar, ver){

		 $("#"+ocultar.id).css("display","none");

		 $("#"+ver).css("display","block");

	}

	

	</script>

	

  </head>







<body onload="startTime()">

	<!-- Fixed navbar -->

	<div class="navbar navbar-default navbar-fixed-top" role="navigation">

		<div class="container">

			<div class="navbar-header">

				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">

				<span class="sr-only">Toggle navigation</span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>

				</button>

				<a class="navbar-brand" href="index.php"><img src="images/logo_48px.png">&nbsp;&nbsp;</a>

			</div>

			<div class="navbar-collapse collapse">

				<?php echo menu($_SESSION['id_usuario']); ?>

			</div>

			<!--/.nav-collapse -->

		</div>

	</div>

	<div id="wrap">

		<div class="container">

			<div class="page-header">

				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>

			</div>

			<div class="row">

				<!-- begin col-3 -->

				<div class="col-md-3 col-sm-6">

					<div class="widget widget-stats bg-green">

						<div class="stats-icon"><span class="glyphicon glyphicon-shopping-cart" id="dash1_moneda" style="cursor:pointer" onclick="totalVentasMoneda(1)" title="Click aquí para ver datos en cantidad o en moneda"></span></div>

						<div class="stats-info">

							<h4 id="dash1_titulo" style="cursor:pointer" onclick="totalVentasCantidad('hoy')" title="Click aquí para ver datos del día o del mes"></h4>

							<p id="dash1_valor">Gs. 0</p>

						</div>

						<div class="stats-link">

							<p id="dash1_dif" onclick="intercambioValores(this,'dash1_est')"></p>

							<p id="dash1_est" style="display:none" onclick="intercambioValores(this,'dash1_dif')"></p>

						</div>

					</div>

				</div>

				<!-- end col-3 -->

				<!-- begin col-3 -->

				<div class="col-md-3 col-sm-6">

					<div class="widget widget-stats bg-blue">

						<div class="stats-icon"><span class="glyphicon glyphicon-piggy-bank"></span></div>

						<div class="stats-info">

							<h4 id="dash2_titulo" style="cursor:pointer" onclick="totalGanancias('hoy')" title="Click aquí para ver datos del día o del mes"></h4>

							<p id="dash2_valor">Gs. 0</p>

						</div>

						<div class="stats-link">

							<p id="dash2_dif" onclick="intercambioValores(this,'dash2_est')"></p>

							<p id="dash2_est" style="display:none" onclick="intercambioValores(this,'dash2_dif')"></p>

						</div>

					</div>

				</div>

				<!-- end col-3 -->

				<!-- begin col-3 -->

				<div class="col-md-3 col-sm-6">

					<div class="widget widget-stats bg-purple">

						<div class="stats-icon"><span class="glyphicon glyphicon-list-alt"></span></div>

						<div class="stats-info">

							<h4 id="dash3_titulo" style="cursor:pointer" onclick="totalGastos('hoy')" title="Click aquí para ver datos del día o del mes"></h4>

							<p id="dash3_valor">Gs. 0</p>

						</div>

						<div class="stats-link">

							<p id="dash3_dif" onclick="intercambioValores(this,'dash3_est')"></p>

							<p id="dash3_est" style="display:none" onclick="intercambioValores(this,'dash3_dif')"></p>

						</div>

					</div>

				</div>

				<!-- end col-3 -->

				<!-- begin col-3 -->

				<div class="col-md-3 col-sm-6">

					<div class="widget widget-stats bg-red">

						<div class="stats-icon"><span class="glyphicon glyphicon-time"></span></div>

						<div class="stats-info">

							<h4><?php echo fechaEspanol('full');?></h4>

							<p id="hora"></p>

						</div>

						<div class="stats-link">

							<p id="weather" style="font-size:9pt"></p>

						</div>

					</div>

				</div>

				<!-- end col-3 -->

			</div>

			<span id="mensaje"></span>

			<br />

			<div class="row">

<!-- 				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" >

					<div id="toolbar">

						<h3 style="margin:0;padding:0">Productos por sucursal</h3>

					</div>

					<table id="tabla" data-url="inc/dashboard.php?q=productos_sucursal" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true"></table>

				</div> -->

			</div>

		</div>

		<!-- /container -->

	</div>

	<?php echo piePagina(); ?>

	<!-- Bootstrap core JavaScript

		================================================== -->

	<!-- Placed at the end of the document so the pages load faster -->

	<script src="js/menuHover.js"></script>

	<!--<script src="charts/js/highcharts.js"></script>

		<script src="charts/js/highcharts-3d.js"></script>

		<script src="charts/js/modules/data.js"></script>-->

</body>



</html>