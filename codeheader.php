<?php 
	$fla_rootdir = "";
	$incdir = $fla_rootdir . 'include/';
	$bildverzeichnis = $fla_rootdir . 'portfolio/';
	//$bildverzeichnis = $fla_rootdir . 'media/lasobras/';

	$server_vars = ($_SERVER) ? $_SERVER :  $HTTP_SERVER_VARS;
	$get_vars = ($_GET) ? $_GET :  $HTTP_GET_VARS;
	$post_vars = ($_POST) ? $_POST :  $HTTP_POST_VARS;
	$php_selbst = @$server_vars['PHP_SELF'];
	//$php_basis = $php_selbst . '?' . $server_vars['QUERY_STRING'];


	include $incdir . "functions.php"; 
	include $incdir . "airy_album.php"; 

?>
