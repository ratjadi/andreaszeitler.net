<?php





function module_main ($menuitem) {
	global $incdir;
	global $bildverzeichnis;
	global $get_vars;


	switch($menuitem) {
		default:
		case 'Hochzeit':
				$akt_album = $get_vars['airyal'];
				$fertigesAlbum = new airy_album($bildverzeichnis, 'fla_airy_files/');
				$fertigesAlbum->airy_init();
				$fertigesAlbum->set_akt_album($akt_album);
				echo $fertigesAlbum->get_html($get_vars);
				break;
		
		case 'Interieur':
				$akt_album = $get_vars['airyal'];
				$fertigesAlbum = new airy_album($bildverzeichnis, 'fla_airy_files/');
				$fertigesAlbum->airy_init();
				$fertigesAlbum->set_akt_album($akt_album);
				echo $fertigesAlbum->get_html($get_vars);
				break;
				
/*		case 'portrait':

				echo <<<FLA
			<div id="journalentry"> 
	
				<div id="journalentrydate"> 
				01.01.2009
				</div> 

				<div id="journalentrytitel"> 
				I would rather suggest one liners.</div> 
				
				<div id="journalentrycontent"> 
					<span class="journalentrycontent"> 
						<h1 class="normal_content">The date just looks better!</h1> 
                        <div id="journalpic"> 
								<img src="images/about.jpg" border="0" alt="yeah yeah self portrait" /> 
						</div> 
						To make a long story short: for me photography is the ultimate means to express what would otherwise remain inside myself. I understand it as a process where one uses the medium to compress a certain bit of reality into the frame. This is a very strong reduction and although it might seem so - and this makes it so compelling - the result is not a record of reality, it is a new thing. No other medium allows this kind of workflow, and I think therefore photography just meets my very nature best. <br /><br /> Before I arrived here, I was a software engineer. Propably this helped me doing this site and also makes understanding aspects  of digital photography a bit easier. <br /><br />What else to say? I also like to write occasionally, which led me to get also made me thinking about the parallels between photography and writing. And of course I like to listen to good music, for which there is too less time left. Btw, if you wanna hear some real cool yet unknown piece, go <a href="http://www.myspace.com/oriolvandela" target="_blank">here</a> and check out oriol 66.                           
						 <br /> <br /> <br /> 
						<h1 class="normal_content">Contact Information</h1>  
						Please drop me an <a href="mailto:office@flamelingo.net">email</a>.
					</span> 
				</div> 
 
			</div> 
FLA;

				break;
*/		
	}


	return; 
}



/* Fügt zur aktuellen url den parameter dazu, überschreibt ihn falls er schon drinnen ist,
   und gib auch eine liste aller Parameter zur Weiterverarbeitung zurück.

   Bsp: Es werden die 2 Paramter an die bisherigen GET variablen angehängt

	$mylist = $_GET; // wichtig: eine Kopie von $_GET übergeben, da das ein IO-Paramter ist!
	$myurl  = $_SERVER['PHP_SELF'];
	$newurl = url_plus_param('user','franz',$myurl, $mylist);
	$newurl = url_plus_param('pass','mausi',$myurl, $mylist);
*/
function url_plus_param ($param_name, $param_wert, $url, &$param_list) {
	
	$php_basis = $url . '?';

	$param_list[$param_name] = $param_wert;
	
	foreach ($param_list as $varname => $varvalue) {
		$php_basis .= $varname . '=' . $varvalue . '&';
	}
	
	return $php_basis;
}

function normalize_path ($path){
 
    // DIRECTORY_SEPARATOR is a system variable
    // which contains the right slash for the current 
    // system (windows = \ or linux = /)
 
    $s = '/';
    $path = preg_replace('/[\/\\\]/', $s, $path);

	if ($path[strlen($path)-1]!=$s)
    	$path .= $s;

    return $path;
}

function login_formular ($user, $target_script='index.php', $login_error=false) {

	$error_text = ($login_error ? "<p>Benutzername oder Passwort falsch!</p>" : "");
	
	$formular=<<<EOT
$error_text
	<div id="loginarea">
	  <form action="{$target_script}" method="post" name="login" id="login">
	    
	      <p><input type="text" name="user" id="user" size="20" value="{$user}" /><label for="user">User</label></p>
	      <p><input type="password" name="pass" id="pass" size="20" value="" /><label for="pass">Passwort</label></p>
	    </p>
	    <p>
	      <input type="image" name="submit" id="submit" src="images/but_login.gif" value="Login"/>
	    </p>
	  </form>
	</div>
EOT;

	return $formular;
}


function login ($user, $pass, &$level, &$folder, &$plain) {
	global $db;
	global $dbhost, $dbuser, $dbpass, $dbname;

	if (!isset($db)) {
		$db = new datenbank ($dbhost, $dbuser, $dbpass, $dbname);
	}

	$ret = false;

	if ($result = $db->sql("SELECT username, level, folder, plain FROM cms_members WHERE username='{$user}' AND password=MD5('{$pass}');")) {
		if (count($result)) {
			$level = $result[0]['level'];
			$folder =  $result[0]['folder'];
			$plain =  $result[0]['plain'];
			$ret = true;
		}
		else
			$level = 99; // keine Berechtigung
	}

	return $ret;
} 


?>