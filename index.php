<?php
  include ("inc/funciones.php");
  session_start();
  if(isset($_SESSION['id_usuario'])){
    header('Location:inicio.php');
  }else if(isset($_COOKIE['3a60fbdR3c0Rd4R0ebf5'])){
    $_SESSION['id_usuario'] = $_COOKIE['3a60fbdR3c0Rd4R0ebf5'];
    header('Location:inicio.php');
  }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo datosConfig('nombre_sistema') ?></title>

        <!-- CSS -->
        <!--<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500">-->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
		  <link rel="stylesheet" href="css/form-elements.css">
        <link rel="stylesheet" href="css/login-style.css">

        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="images/favicon.png">
    </head>

    <body>
        <!-- Top content -->
        <div class="top-content">
            <div class="inner-bg">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-2 text">
							<!--<h2 style="color:white">
								<?php echo datosConfig('nombre_sistema') ?>
							</h2>
                            <div class="description">
                            	<h3>
	                            	<?php echo datosConfig('subtitulo_sistema') ?>
                            	</h3>
                            </div>-->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 form-box">
                        	<div class="form-top">
                        		<div class="form-top-left">
                        			<h3>Acceso al <?php echo datosConfig('nombre_sistema') ?></h3>
                            		<p>Ingrese su nombre de usuario y contraseña:</p>
                        		</div>
                        		<div class="form-top-right">
                        			<!--<i class="fa fa-lock"></i>-->
											<img src="<?php echo datosConfig('logo') ?>">
                        		</div>
                            </div>
                            <div class="form-bottom">
			                    <form role="form" action="" method="post" class="login-form">
			                    	<div class="form-group">
			                    		<label class="sr-only" for="form-username">Username</label>
			                        	<input type="text" name="form-username" placeholder="Usuario" class="form-username form-control" id="form-username" autofocus autocomplete="off">
			                        </div>
			                        <div class="form-group">
			                        	<label class="sr-only" for="form-password">Password</label>
			                        	<input type="password" name="form-password" placeholder="Contraseña" class="form-password form-control" id="form-password">
			                        </div>
											<div class="form-group">
											<label class="checkbox" style="margin-left:20px">
											  <input type="checkbox" value="rememberme" id="rememberme">Mantener sesión iniciada
											</label>
											</div>
			                        <button type="submit" class="btn">Iniciar sesión</button>
			                    </form>
		                    </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 social-login">
                        	<div id="mensaje"></div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>


        <!-- Javascript -->
        <script src="js/jquery-1.11.1.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.backstretch.min.js"></script>
        <script src="js/funciones.js"></script>
        <script type="text/javascript">
				jQuery(document).ready(function() {
					 $.backstretch("images/login.jpg");
					 $('.login-form input[type="text"], .login-form input[type="password"], .login-form textarea').on('focus', function() {
						$(this).removeClass('input-error');
					 });
					 $('.login-form').on('submit', function(e) {
						$(this).find('input[type="text"], input[type="password"], textarea').each(function(){
							if( $(this).val() == "" ) {
								e.preventDefault();
								$(this).addClass('input-error');
							}
							else {
								e.preventDefault();
								$(this).removeClass('input-error');
								  $.ajax({
									 async: false,
									 type: 'POST',
									 dataType: 'json',
									 data: {u: $('#form-username').val(), p: $('#form-password').val(), r: $('#rememberme').is(':checked')},
									 url: './login.php',
									 beforeSend: function (datos){
										$('#mensaje').html(alertDismissJS("Accediendo...","ok"));
									 },
									 success: function (datos) {
										if (!datos.redirect){
										$('#mensaje').html(datos.mensaje);
										}else{
										  location.assign(datos.redirect);
										}
									 },
									 error: function (xhr){
										$('#mensaje').html(alertDismissJS("No se pudo completar la operación. " + xhr.status + " " + xhr.statusText, 'error'));
									 }
								});
							}
						});
						
					 });
				});
		  </script>

    </body>

</html>