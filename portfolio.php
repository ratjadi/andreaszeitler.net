<?php 
	include "codeheader.php"; 
	include $incdir . "htmlheader.php"; 

?>

<body id="page_bg">

<div id="container">

<?php
include "header.php";
echo <<<FLA
		<a href="index.php">
			<div id="homelink"></div>
		</a>
FLA;
?>		
		

    </div>

	
 <!--
     <div id="maincontent">
			<div id="journalentry"> 

				<div id="journalentrytitel"> 
				Portfolio
				</div> 
				
				<div id="journalentrycontent"> 
					<span class="journalentrycontent"> 
                        <div id="journalpic"> 
								<img src="images/inarbeit.jpg" border="0" /> 
						</div> 

						<h1 class="normal_content">wegen bauarbeiten zur zeit nicht erreichbar</h1> 

						                          
						 <br /> <br />
						 Ein neues Portfolio wird aber bald online sein, wirklich!

					</span> 
				</div> 
 
			</div> 
    </div>   
-->
										
										    <div id="maincontent">
										
												<div id="albummenu">
													<ul>
										<?php
														//
														// airy_album_portfolio listet alle unterverzeichnisse auf einer Seite auf,
														// und wird ohne get_menu() verwendet.
														//
														// airy_album_portfolio_extended bietet get_menu() an, und listet dann das unterverzeichnis auf
														//
														
														$akt_album = $get_vars['airyal'];
														$menuAlbum = new airy_album_portfolio_extended('media/lasobras/', 'fla_airy_files/');
														$menuAlbum->set_size_specs (800, 600, 3); // große Ttoleranz 3: verwende Originalbilder
														$menuAlbum->airy_init();
														$menuAlbum->set_akt_album($akt_album);  //diese 3 Zeilen sind für das airy_album_extended
														echo $menuAlbum->get_menu($get_vars);   // wobei die erste Verzeichnisebene als Menü dient
														$menuAlbum->reset_album();
										
										?>							
													</ul>
												</div>	
										
												<?php 
													$html = $menuAlbum->get_html($get_vars);
													echo $html; // str_replace(pack("C",0x00),"",$html);
			
												?>
        <script>

        	$(document).ready(function() { 
	        	Galleria.loadTheme('include/galleria/themes/classicfullscreen/galleria.classicfullscreen.js');
	        	$("#gallery-2 a").click(function () {
	        		bildnummer = $(this).attr('nummer');
					
					gallery_clone = $('#gallery-2').clone();
					gallery_clone.attr('id', 'fullscreen-gallery');
					gallery_clone.appendTo('body'); // ('<div id="fullscreen-gallery"></div>');
		            $("#fullscreen-gallery").galleria({
		                width: 'auto',
		                height: 700,
		                show: bildnummer
		            });
		            	            
		            return(false);
				});
			});
        </script>												
										    </div>
										
	<div id="footer">
		<?php include "footer.php" ?>
    </div>


</div>

</body>
</html>

