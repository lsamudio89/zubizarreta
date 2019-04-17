<?php
  include ("inc/funciones.php");
  //$pag = basename($_SERVER['PHP_SELF']);
  verificaLogin();
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

    <title><?php echo 'Mi Cuenta'." - ".datosSucursal($id_usuario)->nombre_empresa; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    

    <!-- Custom styles for this template -->
    <link href="css/theme.css" rel="stylesheet">
  
    <script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
    
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
          <a class="navbar-brand" href="index.php"><?php echo datosSucursal($id_usuario)->nombre_empresa; ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <?php echo menu($_SESSION['usuario']); ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>
    <div id="wrap">
      <div class="container">
        <div class="page-header">
          <h2>Mi Cuenta</h2>
        </div>
 
          
      </div> <!-- /container -->
    </div>
    <?php echo piePagina(); ?>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/menuHover.js"></script>
  </body>
</html>