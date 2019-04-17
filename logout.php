<?php
	session_start();
	session_destroy();
	setcookie("3a60fbdR3c0Rd4R0ebf5","deleted", time() - 3600, "/");
	header('Location:./index.php');
?>