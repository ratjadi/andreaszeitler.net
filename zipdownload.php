<?php 

$directoryToZip=$_GET['airyal']; // This will zip all the file(s) in this present working directory 
//$directoryToZip='ziptest';

// Sollen Dateinnamen im Zip die Pfade beinhalten?
if ($_GET['incp']=='y')
	$incp = true;
else
	$incp = false;

$outputDir="/"; //Replace "/" with the name of the desired output directory. 
$path_array = explode("/", $directoryToZip);
$zipName = $path_array[count($path_array)-2];
$rand=md5(microtime().rand(0,999999)); 
$zipName=$zipName."_".$rand.".zip"; 

include_once("./include/functions.php"); 
include_once("./include/zipbuilder.php"); 
//include_once("./include/zip_withfile.php");
$createZipFile=new CreateZipFile(true); 

/* 
// Code to Zip a single file 
$createZipFile->addDirectory($outputDir); 
$fileContents=file_get_contents($fileToZip); 
$createZipFile->addFile($fileContents, $outputDir.$fileToZip); 
*/ 

//Code toZip a directory and all its files/subdirectories 
$createZipFile->zipDirectory($directoryToZip,$outputDir,$zipName); 
//$createZipFile->zipDirectory($directoryToZip,$outputDir); 


//$createZipFile->sendZip($zipName);
$createZipFile->forceDownload($zipName); 
unlink($zipName); 


//$fd=fopen($zipName, "wb"); 
//$out=fwrite($fd,$createZipFile->getZippedfile()); 
//fclose($fd); 

?>