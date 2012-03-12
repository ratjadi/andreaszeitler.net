<?php
	include "codeheader.php"; 
	include $incdir . "db_verbinden.php";
	include $incdir . "session.php";

	SESSION::destroy();

	header("Location: ./index.php");
	exit();

?>