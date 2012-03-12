<?php

$server_vars = ($_SERVER) ? $_SERVER :  $HTTP_SERVER_VARS;

if ( eregi('127\.0\.0\.1',$server_vars['HTTP_HOST']) ||
	 eregi('localhost',$server_vars['HTTP_HOST'])
	) {
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = '';
	$dbname = 'andreaszeitler';
}
else {
	$dbhost = 'localhost';
	$dbuser = 'web473';
	$dbpass = 'banane';
	$dbname = 'usr_web473_5';
}

	global $db;
	
	$gherdazu = 'sonet';
	

	include $incdir . "datenbank.php";
	$db = new datenbank ($dbhost, $dbuser, $dbpass, $dbname); 


?>