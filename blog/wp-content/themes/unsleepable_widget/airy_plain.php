<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<?php  
	// Change: Andreas Zeitler: airy_album.php einziehen
	include  ('./airy_album.php'); 	
	$wpurl = '../../../';
?>

</head>

<body>
<?php  

airy_plain_wrapper($_GET['album']);


?>
</body>