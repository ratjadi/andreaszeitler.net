<?php 
	include "codeheader.php"; 
	include $incdir . "db_verbinden.php";
	include $incdir . "session.php";
	
	$user = '';
	$userlevel = 99; // keine Berechtigung
	$userfolder = "";
	if (NULL === SESSION::get('user')) {
		if (isset($post_vars['user'])) {
			if (login($post_vars['user'], $post_vars['pass'], $userlevel, $userfolder, $plain)) {
				// Erfolgreich eingeloggt
				SESSION::set('user', $post_vars['user']);	
				SESSION::set('userlevel', $userlevel);	
				SESSION::set('userfolder', $userfolder);	
				SESSION::set('plain', $plain);	
				$gherdazu = 'adabei';
				// 1x Umleitung, damit beim browser-back nicht die Meldung "bla erneut senden bla" kommt
				header("Location: $php_selbst"); 
			}
			else {
				// gescheiterter Einloggversuch
				SESSION::un_set('user');
				SESSION::un_set('userlevel');
				SESSION::un_set('userpageid');
				$gherdazu = 'tatigern';
			}
		}
		else {
			// Noch nicht eingeloggt (erster Aufruf der Seite)
			$gherdazu = 'sonet';
		}
	}
	else {
		// User ist in Session, eingeloggt
		$gherdazu = 'adabei';
	}

	if (!(NULL === SESSION::get('userlevel'))) {
		$userlevel = SESSION::get('userlevel');
	}
	if (!(NULL === SESSION::get('userfolder'))) {
		$userfolder = SESSION::get('userfolder');
	}
	if (!(NULL === SESSION::get('plain'))) {
		$plain = SESSION::get('plain');
	}
	
	$kundenverzeichnis = "clients/";
	
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

	
    
    <div id="left">
<p>hier kann linke nav stehen</p>
<p>hier kann linke nav stehen</p>
<p>hier kann linke nav stehen</p>
<p>hier kann linke nav stehen</p>
    </div>
    
    <div id="maincontent">
		<div id="albummenu">
			<ul>
<?php
				if ($gherdazu == 'adabei') {
					$downloadable = $get_vars['dl']; // Ansicht ohne lightbox verlangt
					$akt_album = $get_vars['airyal'];
					if ($downloadable=='y' || $plain=='Y')
						$menuAlbum = new airy_album_plain($kundenverzeichnis.$userfolder, 'fla_airy_files/');
					else
						$menuAlbum = new airy_album_clients($kundenverzeichnis.$userfolder, 'fla_airy_files/');
					
					$menuAlbum->airy_init();
					$menuAlbum->set_akt_album($akt_album);
					echo $menuAlbum->get_menu($get_vars);
					$menuAlbum->reset_album();
				echo <<<FLA
				<li> - </li>
				<li><a href="logout.php"><small>Logout</small></a></li>
FLA;
				}
?>							
			</ul>
		</div>    
		<?php 
			if ($gherdazu == 'adabei') {
				//module_main ($get_vars['menuitem']);
				//$akt_album = $get_vars['airyal'];
				/*if ($akt_album == "") 
					$fertigesAlbum = $menuAlbum;
				else
					$fertigesAlbum = new airy_album($akt_album, 'fla_airy_files/');*/
					
				//$menuAlbum->airy_init();
				echo $menuAlbum->get_html($get_vars);
			}
			else {
				if ($gherdazu == 'tatigern') $loginerr = true;
				else $loginerr = false;
				echo login_formular(SESSION::get('user'), $server_vars['PHP_SELF'], $loginerr); 
				echo <<<FLA
			    <script type="text/javascript">
				  window.onload = function () {
					    //Loginseite
					  	x = document.getElementsByTagName('input')[0];
						if (x) {x.focus();}	
				  }
				</script>
FLA;
			}
		?>
    </div>

	<div id="footer">
		<?php include "footer.php" ?>
    </div>


</div>

</body>
</html>

