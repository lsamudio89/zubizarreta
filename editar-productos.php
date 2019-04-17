<?php
	include ("inc/funciones.php");
	$pag = basename($_SERVER['PHP_SELF']);
	verificaLogin($pag);
	$id_usuario = $_SESSION['id_usuario'];
	$id_sucursal = datosUsuario($id_usuario)->id_sucursal;
?>
<!DOCTYPE html>
<html lang="en">
  <head lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png">
   <title><?php echo nombrePagina(basename($_SERVER['PHP_SELF']))." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	
    <script src="js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
	
	<script src="js/bootstrap.min.js"></script>
    
	<script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- Bootstrap table -->
	<link rel="stylesheet" href="bootstrap-table/bootstrap-table.css">
	<script src="bootstrap-table/bootstrap-table.js"></script>
	<script src="bootstrap-table/extensions/export/bootstrap-table-export.js"></script> <script src="js/tableExport.js"></script>
	<script src="bootstrap-table/locale/bootstrap-table-es-AR.js"></script>
	
	<script src="js/editable-table/mindmup-editabletable.js"></script>
    <script src="js/editable-table/numeric-input-example.js"></script>
	 
	
	
	<script src="jquery-ui/jquery-ui.min.js"></script>
	
	
	<!-- Custom style -->
    <link href="css/theme.css" rel="stylesheet">
	 
	 <style>
	   td:focus {
			-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;
			-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;
			box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;	
			outline: rgb(91, 157, 217) auto 3px;
		}
		#tabla input.error {
			-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px red;
			-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px red;
			box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px red;	
			outline: thin auto red;
		}
		input{
			-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;
			-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;
			box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px #7ab5d3;	
			outline: rgb(91, 157, 217) auto 3px;
			outline-offset: 0px;
			border: none;
		}
		#tabla td + td, #tabla th + th {
			text-align: right;
		}
		table.scrollable thead tr, table.scrollable tfoot tr  {
			position: relative;
			display: block
		}
		
		html>body table.scrollable tbody {
			display: block;
			height: 330px;
			overflow: auto;
			width: 100%
		}
		html>body table.scrollable td, html>body table.scrollable th {
			width: 200px
		}
		
		.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
			 line-height: 1.42857143;
			 border-top: 1px solid #ddd;
		}
		
		#tabla tr.header, #tabla tr:hover {
			background-color: #c8def2;
		}
		
		#contenido{
			overflow:auto;
		}
		
	 </style>
    <script type="text/javascript">
	
		$(document).ready(function () {
			
			var $table = $('#tabla');	
			//Altura de tabla automatica
			$(window).resize(function () {
				$("#contenido").css("height", $(window).height()-230);
			});
			
			$("#contenido").css("height", $(window).height()-230);
			
			//Sucursales
			$.ajax({
				dataType: 'json', async: false, cache: false, url: 'inc/listados.php', type: 'POST', data: {q: 'sucursales'},
				beforeSend: function(){
					$("#mensaje").html("<img src='images/progress_bar.gif'>");
				},
				success: function (json){
					$.each(json, function(key, value) {
						$('#sucursal').append('<option value="'+value.id_sucursal+'">'+value.sucursal+'</option>');
					});
					$('#sucursal').val('<?php echo $id_sucursal; ?>');
					$("#mensaje").html("");
				},
				error: function (xhr) {
					$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
				}
			});
			
				<?php
				$sucursal=$_GET['sucursal'];
				if($sucursal){
				echo "$('#sucursal').val($sucursal);";	
				}else{
					
				}
				
				?>
			
			$('#sucursal').change(function(){
				var id_sucursal = $("#sucursal").val();
				var buscar='<?php echo $buscar=$_GET['buscar'];?>';
				$.ajax({
					dataType: 'json', async: false, cache: false, url: 'inc/editar-productos-data.php', type: 'POST', data: {q: 'ver', id_sucursal: id_sucursal, search: buscar},
					beforeSend: function(){
						$("#mensaje").html("<img src='images/progress_bar.gif'>");
					},
					success: function (datos){
						$("#contenido_tabla").html("");
						$.each(datos, function(key, value) {
						$("#contenido_tabla").append("<tr><td data-editable='false' style='width:20px'>"+value.id_producto+"</td><td data-editable='false' style='text-align:left'>"+value.producto+"</td><td>"+value.stock_minimo+"</td><td>"+value.stock+"</td><td>"+value.costo+"</td><td data-editable='false'>"+value.total_costo+"</td><td>"+value.precio_vta_min+"</td><td data-editable='false'>"+value.total_vta_min+"</td><td>"+value.precio_vta_may+"</td><td data-editable='false'>"+value.total_vta_may+"</td></tr>");
						});
						
						$("#mensaje").html("");
					},
					error: function (xhr) {
						$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error'));
					}
				});
				
				$('#tabla').editableTableWidget().numericInputExample().find('td:first').focus();
				
				//Array con los nombres de las columnas iguales a la tabla de bd para hacer el update
				var arrayColumnas = ["id_producto","producto","stock_minimo","stock","costo","total_costo","precio_vta_min","total_vta_min","precio_vta_may","total_vta_may"];
				$('#tabla td').on('change', function(evt, valor) {
					var id_producto = $(this).siblings("td").html(); //Tomamos el valor de la primera celda de la fila editada
					var indice_col = $('#tabla th').eq($(this).index()).index(); //Tomamos el indice de la columna para asociar al array
					var columna = arrayColumnas[indice_col]; //Obtenemos el nombre del campo que coincida con la BD
					
					//Calculamos la ganancia
					//Dejamos para más tardes
					$.ajax({
						dataType: 'html',
						async: true,
						type: 'POST',
						url: 'inc/editar-productos-data.php',
						cache: false,
						data: {q: 'guardar', columna: columna, id_producto: id_producto, valor: valor, id_sucursal: $("#sucursal").val() },	
						beforeSend: function(){
							$("#mensaje").html("<img src='images/progress_bar.gif'>");
						},
						success: function (data, status, xhr) {
							$("#mensaje").html(data);
						},
						error: function (xhr) {
							$("#mensaje").html(alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error_span'));
						}
					});
				});
			
			});
			
			
			$('#sucursal').change();
			
			//Titulo de la tabla fija
			document.getElementById("contenido").addEventListener("scroll",function(){
				var translate = "translate(0,"+this.scrollTop+"px)";
				this.querySelector("thead").style.transform = translate;
				this.querySelector("thead").style.backgroundColor = 'white';
			});
			
			$("#buscar").focus();
			
      });
      /*
		function buscar(){
		  var filter, tr, td, i;
		  filter = $("#buscar").val().toUpperCase();
		  tr = $("#tabla tr");
		  for (i = 0; i < tr.length; i++) {
			 td = tr[i].getElementsByTagName("td")[1];
			 if (td) {
				if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
				  tr[i].style.display = "";
				} else {
				  tr[i].style.display = "none";
				}
			 }       
		  }
		}*/
			
    </script>
  </head>
  <body>
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
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
	<!-- Wrap all page content here -->
    <div id="wrap">
		<div class="container">
			<div class="page-header">
				<h2><?php echo nombrePagina(basename($_SERVER['PHP_SELF'])); ?></h2>
			</div>
			<div class="row">
				<div class="col-md-12">
					
					
					<div class="form-inline" role="form">
						<label>Sucursal:</label>&nbsp;&nbsp;
						<form method="GET">
							<select id="sucursal" name="sucursal" class="form-control input-sm">
							</select>
							&nbsp;&nbsp;&nbsp;&nbsp;
							
							<input autocomplete='off' value="<?php echo $buscar;?>" type="text" style="outline:inherit" class="form-control input-sm" name="buscar" id="buscar" placeholder="Buscar producto.." title="Escriba el nombre de un producto">
							
							&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="submit" class="form-control input-sm" value="Buscar" name="Buscar">
						<span id="mensaje"></span>	
						</form>
					</div>
					
					<br>
					<div id="contenido">
					  <table id="tabla" class="table table-striped table-condensed" >
							<thead><tr><th>ID</th><th style='text-align:center'>Producto</th><th>Stock Mín</th><th>Stock</th><th>Costo</th><th>Total Costo</th><th>Precio Minorista</th><th>Total Minorista</th><th>Precio Mayorista</th><th>Total Mayorista</th></tr></thead>
							<tbody id="contenido_tabla">
							  
							  
							  
							</tbody>
						<tfoot><tr><th><strong>TOTALES</strong></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead>
						 </table>
					</div>
				</div>		
			</div>
		</div> <!-- /container -->		
	</div> <!-- /wrap -->
	
	<?php echo piePagina(); ?>
    <script src="js/menuHover.js"></script>
  </body>
</html>