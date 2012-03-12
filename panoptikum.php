<?php 
	include "codeheader.php"; 
	include $incdir . "htmlheader.php"; 
?>


<body id="page_bg">

<div id="container">

<?php
error_reporting(E_ALL);
include "header.php";
echo <<<FLA
		<a href="index.php">
			<div id="homelink"></div>
		</a>
FLA;
?>		
		

    </div>

										
										    <div id="maincontent">
											
											<div id="mainselector-wrapper">
										<?php
											//
											// airy_album_portfolio listet alle unterverzeichnisse auf einer Seite auf,
											// und wird ohne get_menu() verwendet.
											//
											// airy_album_portfolio_extended bietet get_menu() an, und listet dann das unterverzeichnis auf
											//
											
											$menuAlbum = new airy_album_showcase('media/showcase/', 'fla_airy_files/');
											$menuAlbum->set_size_specs (800, 600, 3); // große Ttoleranz 3: verwende Originalbilder
											$menuAlbum->airy_init();
											$akt_album = $get_vars['airyal'];
											$menuAlbum->set_akt_album($akt_album);  

											if (!isset($get_vars['airyal'])) {
										?>
												<div id="albummenu">
													<ul>
										<?php
													echo $menuAlbum->get_menu($get_vars);   
													$menuAlbum->reset_album();
										
										?>							
													</ul>
												</div>	
										
										<?php 
											}	
											else
											{
													$html = $menuAlbum->get_html($get_vars);
													echo $html;
											}
										?>
										    </div>
										</div>
										
	<div id="footer">
		<?php include "footer.php" ?>
    </div>


</div>

</body>
</html>

