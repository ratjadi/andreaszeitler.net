<?php
function _debug ($str) {//echo ($str) . '<br />';
}

define (AIRY_VERZEICHNIS, 'wp-content/az_airy_files/');

function airy_wrapper ($album) {
	$fertigesAlbum = new airy_album($album, AIRY_VERZEICHNIS);
	$fertigesAlbum->airy_init();

	echo $fertigesAlbum->get_html();
}

function airy_plain_wrapper ($album) {
	$fertigesAlbum = new airy_album_plain($album, AIRY_VERZEICHNIS);
	$fertigesAlbum->airy_init();

	echo $fertigesAlbum->get_html();
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

// -----------------------------------------------------------------------------------
//
//	Airyalbum v0.01
//	by Andreas Zeitler http://www.andreaszeitler.net
//	Last Modification: 08.11.2009 19:13
//
//
// -----------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------
//
//	class airy_album_verzeichnis
//
//  Beschreibung:
//  =============
//  Ein spezifiziertes Verzeichnis wird nach jpgs durchsucht, thumbs generiert, und
//  die Pfade zu den Bildern und thumbs werden in $bild_liste gespeichert.
//	
//	Konstruktor:
//  ============
//	$pfad_in				Basisverzeichnis der Bilder (z.b. 'images/')
//  $airy_verzeichnis		Basisverzeichnis für Airy Datein (z.b. 'airy_files')
//							Hier wird die Struktur des Bilderverz. abgebildet und
//							die Thumbnails gespeichert
//  $level                  Verzeichnisebene (spez. für rekursive Objekte)
//  $param_list             Liste mit weiterzureichenden url parametern
//                          -> im Normalfall $_GET hier übergeben, dann werden die
//                          vorigen get variablen bei den album links auch mit übergeben
//  
//  Interface:
//  ==========
//  set_basis_verzeichnis ($pfad_in) Basisverzeichnis der Bilder (z.b. 'images/')
//  get_basis_verzeichnis () 
//  set_ueber_verzeichnis ($obj)     Bestimme übergeordnetes Verz.Obj. in einer Hierarchie
//									 In der Rekurstion: set_ueber_verzeichnis ($this)
//  airy_init ()                     Starte Verarbeitung des Verzeichnisses
//									 Thumbnails werden ggf. generiert
//  set_thumbnail_specs ($breite, $hoehe, $kante_crop, $qual, $prefix)   -> nachher erzeuge_album(true) aufrufen!
//  set_airy_verzeichnis ($airy_verzeichnis)							 -> nachher airy_init() aufrufen!
//  get_airy_verzeichnis () 
//  erzeuge_album ($erzwingen=false)                Mit $erzwingen werden auf jeden Fall thumbs neu generiert 
//													Sollte man nach set_thumbnail_specs machen
//  show_root ($showroot)	TRUE: Das rootverzeichnis wird von get_next_element()
//							      als erstes Element geliefert
//							FALSE: Das erste Element von get_next_element() ist
//								   das erste Element im rootverzeichnis (bild oder wiederum verz.)
//  get_level ()        Liefert die Hierarchiestufe. Das Root Objekt hat 0 etc.
// 
//  get_bild_liste ()   Liefert die Bildliste zurück 
//						z.b. bildliste['bild1.jpg'] = {'thumbnail' => '_th_bild1.jpg'}
//
//  get_next_element () Liefert das nächste Element
//
//  werbinich()         liefert 'thumbnails' für Bilderobjekt, d.h. item_liste sind Bilder
//						        'verzeichnis' für Verzeichnisobjekt, d.h. item_liste sind Unterverzeichnisse
// -----------------------------------------------------------------------------------

class airy_album_verzeichnis {
	protected  $php_basis;
	protected  $basis_verzeichnis;
	protected  $airy_verzeichnis;
	protected  $th_breite=150, $th_hoehe=150, $th_crop=1.2, $th_qual=170, $th_prefix='_th_';
	protected  $album_titel, $album_teaser, $album_beschreibung;
	protected  $html;
	protected  $item_liste=array(); // Verzeichnisse oder Thumbnails, je nach typ des aktuellen Objekts
	protected  $first_element=true;
	protected  $werbinich=NULL;
	protected  $ueber_verzeichnis=NULL;	
	protected  $akt_album='';
	protected  $level=0;
	protected  $show_root=false;
	protected  $param_list;
	private $trennzeichen='/';

	function __construct ($pfad_in, $airy_verzeichnis='airy_files', $level=0, $param_list=array()) {
		$server_vars = ($_SERVER) ? $_SERVER :  $HTTP_SERVER_VARS;
		
		$this->wpurl = get_bloginfo("wpurl") . '/'; //"http://andreaszeitler.net/blog/";
		$this->php_basis = @$server_vars['PHP_SELF'];
		
		$this->php_basis = url_plus_param ('airyal', '', $this->php_basis, $this->param_list);

		//echo $this->php_basis;

		$this->html = '';
		$this->bild_liste = array();
		$this->basis_verzeichnis = normalize_path($pfad_in);

		$this->set_airy_verzeichnis($airy_verzeichnis);
		$this->set_identitaet();

		$this->level = $level;

_debug($this->level);
	}

	function __destruct () {
		//echo "<br />{$this->basis_verzeichnis} erledigt<br />";
		//print_r ($this->bild_liste);
		//echo $this->album_titel;
		//echo $this->album_beschreibung;
	}

	public function set_ueber_verzeichnis ($obj) {
		$this->ueber_verzeichnis = $obj;
	}


	public function werbinich () {
		if ($this->werbinich === NULL)
			$this->set_identitaet();

		return $this->werbinich;
	}

	private function set_identitaet () {
		if ($this->hat_bilder($this->basis_verzeichnis))
			$this->werbinich = 'thumbnails';
		else
			$this->werbinich = 'verzeichnis';
	}

	public function set_basis_verzeichnis ($pfad_in) {
		$this->basis_verzeichnis = $pfad_in;
	}

	public function get_basis_verzeichnis () {
		return $this->basis_verzeichnis;
	}

	public function set_akt_album ($akt_album='') {
		if ($akt_album=='') {
		
			// Wähle das erste Album/Verzeichnis als aktuelles aus
			if ($this->akt_album == "") {	
				reset($this->item_liste);
				$this->akt_album = $this->basis_verzeichnis . key($this->item_liste) . $this->trennzeichen;
				//echo 'Aktablbum: ' . $this->akt_album . '<br />';
			}
		}
		else {
			$this->akt_album = $akt_album;
		}
	}

	public function show_root ($show_root) {
		if (is_bool($show_root))
			$this->show_root = $show_root;
	}

	public function get_level () {
		return $this->level;
	}

	public function airy_init () {

		$this->set_album_beschreibung();

		if ($this->werbinich() == 'thumbnails') 
			$this->erzeuge_album();
		else
			$this->lese_unter_verzeichnisse();

		if (!(NULL === $this->item_liste) &&
			count($this->item_liste) > 0) {	
			ksort($this->item_liste);
			return $this;
		}
		else
		{
			unset($this);
			return NULL;
		}
	}

	public function set_thumbnail_specs ($breite, $hoehe, $kante_crop, $qual, $prefix) {
		$this->th_breite = $breite;
		$this->th_hoehe = $hoehe;		
		$this->th_crop = $kante_crop;
		$this->th_qual = $qual;
		$this->th_prefix = $prefix;
	}

	public function get_item_liste () {
		return $this->item_liste;
	}

	public function get_next_element () {	
		if ($this->first_element) {	
			$this->first_element = false;
			
			if (!($this->level == 0 && !$this->show_root)) {
				$element = array();
				$element['typ'] = 'verzeichnis';
				$element['level'] = $this->level;
				$element['obj'] = &$this;
				$path_array = explode($this->trennzeichen, $this->basis_verzeichnis);
				$element['verzeichnis'] = $path_array[count($path_array)-2];
				$element['basis_verzeichnis'] = $this->basis_verzeichnis;
				$element['airy_verzeichnis'] = $this->airy_verzeichnis;
				$element['album_titel'] = $this->album_titel;
				$element['album_teaser'] = $this->album_teaser;
				$element['album_beschreibung'] = $this->album_beschreibung;
				$element['werbinich'] = $this->werbinich();
			}
			else {
				$element = array();
				$element['typ'] = 'root';
				$element['basis_verzeichnis'] = $this->basis_verzeichnis;
			}
_debug ('Verzeichnisinfo: '.$element['verzeichnis']);
		}
		else {

			if ($this->werbinich() == 'thumbnails') {
		
				if (!(FALSE === (list($bildname, $bildinfo) = each($this->item_liste)))) {
					$element = array();
					$element['typ'] = 'bild';
					$element['obj'] = &$this;
					$element['bildname'] = $bildname;
					$element['thumbnail'] = $bildinfo['thumbnail'];
					$element['bild_beschreibung'] = $bildinfo['bild_beschreibung'];
					$element['basis_verzeichnis'] = $this->basis_verzeichnis;
					$element['airy_verzeichnis'] = $this->airy_verzeichnis;	
					$element['thumbpfad_ganz'] = $this->airy_verzeichnis . $this->absPath($this->basis_verzeichnis);
					$path_array = explode($this->trennzeichen, $this->basis_verzeichnis);
					$element['verzeichnis'] = $path_array[count($path_array)-2];
_debug ('Thumbnail: '.$element['bildname']);
				}
				else {
_debug ('Thumbnail aus in Ordner: '.$this->basis_verzeichnis);
					$element = NULL;
				}
			}
			else { // verzeichnis
				
				if (!(FALSE === (list($verzeichnis, $obj) = each($this->item_liste)))) {
					// Bleibe auf dem aktuellen Verzeichnis, solange noch ein Unterelement kommt
					// Wenn kein Element mehr von der nächsten Ebene kommt, 
					// setze den Zeiger weiter
//_debug ('get_next_element in Verzeichnis: '.$verzeichnis);
					if (!(NULL === ($element = $obj->get_next_element()))) {
						if (FALSE === prev($this->item_liste))	
							end($this->item_liste);
					}
					else {
_debug ('End in Verzeichnis: '.$verzeichnis);
						$element = array();
						$element['typ'] = 'terminator';
					}
				}
				else {
_debug ('Kein weiterer Ordner aus in Ordner: '.$this->basis_verzeichnis);
					$element = NULL;
				}				
			}
		}

		return $element;
	}


	public function set_airy_verzeichnis ($airy_verzeichnis) {
		if ($airy_verzeichnis == '')
			$this->airy_verzeichnis = $this->basis_verzeichnis . 'airy_files/';
		else
			$this->airy_verzeichnis = $airy_verzeichnis;

		if (!file_exists($this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis))) {
			self::mkRecursiveDir($this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis));
		}

		
	}

	public function get_airy_verzeichnis () {
		return $this->airy_verzeichnis;
	}

	private function lese_unter_verzeichnisse () {
		// Öffne Bidler Verzeichnis
        if (is_dir($this->basis_verzeichnis))
			$verzeichnis_handle = dir($this->basis_verzeichnis);

		if ($verzeichnis_handle) {

			// Lese alle Verzeichnisse und verarbeite sie 
			while (false !== ($verzeichnis_eintrag = $verzeichnis_handle->read())) {

				// Erzeuge für jedes Verzeichnis entweder ein airy_album_thumbnails -> thumbnails generieren
				// oder ein airy_album_verzeichnisse_rekursiv -> weiter graben
				$voller_pfad = $this->basis_verzeichnis . $verzeichnis_eintrag . $this->trennzeichen;

				if (is_dir($voller_pfad) && $verzeichnis_eintrag != '..' &&  $verzeichnis_eintrag != '.'
					&& $voller_pfad != $this->airy_verzeichnis) {

					$airy_aktuelles_verzeichnis = new airy_album_verzeichnis($voller_pfad, $this->airy_verzeichnis, $this->level+1);
					$airy_aktuelles_verzeichnis->set_ueber_verzeichnis($this);

					if (!(NULL === $airy_aktuelles_verzeichnis->airy_init())) {
					// Speichere das neue Objekt nur, wenn das betreffende Verzeichnis nicht leer ist
					// Wenn airy_init NULL liefert, ist es leer!
						$this->item_liste[$verzeichnis_eintrag] = $airy_aktuelles_verzeichnis;
					}
					$airy_aktuelles_verzeichnis = NULL;		

				}
			}
		}

		$verzeichnis_handle = NULL;
	}


	public function erzeuge_album ($erzwingen=false) {
		
		// Öffne Bidler Verzeichnis
        if (is_dir($this->basis_verzeichnis))
			$verzeichnis_handle = dir($this->basis_verzeichnis);

		if ($verzeichnis_handle) {
			// Lese alle Dateien und generiere ein Thumbnail
			while (false !== ($verzeichnis_eintrag = $verzeichnis_handle->read())) {
				
				// TODO Hier dann noch prüfen, ob es ein jpeg ist !
				if (!is_dir($verzeichnis_eintrag) && eregi("\.(jpe?g)$",$verzeichnis_eintrag)) {			   
					$this->erzeuge_thumbnail($verzeichnis_eintrag, $erzwingen);
				}
			}
		}

		$verzeichnis_handle = NULL;
	}


	private function set_album_beschreibung () {

		// Ermittle den Albumnamen -> entweder der Verzeichnisname oder aus Datei airy_info.ini	
		$this->album_teaser = '';
		$this->album_beschreibung = '';
		if (file_exists($this->basis_verzeichnis . 'airy_info.ini')) {
			$ini_array = parse_ini_file($this->basis_verzeichnis . 'airy_info.ini');
			$this->album_titel = $ini_array["titel"];
			$this->album_teaser =  $ini_array["teaser"];
			$this->album_beschreibung = $ini_array["beschreibung"];
		}
		else {
			$path_array = explode($this->trennzeichen, $this->basis_verzeichnis);
			$this->album_titel = $path_array[count($path_array)-2];
		}

	}

	private function erzeuge_thumbnail ($verzeichnis_eintrag, $erzwingen=false) {
		$bildpfad_ganz = $this->basis_verzeichnis . $verzeichnis_eintrag;
		$thumbpfad_ganz = $this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis) . $this->th_prefix . $verzeichnis_eintrag;

		if ( eregi("\.(jpe?g)$",$bildpfad_ganz)) {

			// Es werden später noch mehr Infos (Comment und so) gespeichert
			$this->item_liste[$verzeichnis_eintrag] = array("thumbnail" => $this->th_prefix . $verzeichnis_eintrag); 
			
			$exif = @exif_read_data($bildpfad_ganz,NULL,true);
			$this->item_liste[$verzeichnis_eintrag]["bild_beschreibung"] = "";

			if ($exif["IFD0"]["ImageDescription"] != "" && $exif["IFD0"]["ImageDescription"] != "OLYMPUS DIGITAL CAMERA")
				$this->item_liste[$verzeichnis_eintrag]["bild_beschreibung"] = htmlentities( $exif["IFD0"]["ImageDescription"] );
			
			if ($exif["COMMENT"]["0"] != "")
				$this->item_liste[$verzeichnis_eintrag]["bild_beschreibung"] = htmlentities ( $exif["COMMENT"]["0"] );

			//echo $exif["IFD0"]["ImageDescription"];
			/*foreach ($exif as $key => $section) {
			    foreach ($section as $name => $val) {
			        echo "$key.$name: $val<br />\n";
			    }
			}*/
		}

		if ( eregi("\.(jpe?g)$",$bildpfad_ganz) && ($erzwingen || !file_exists($thumbpfad_ganz)) ) {
			list ($breite, $hoehe) = getimagesize ($bildpfad_ganz);
			$bild = imagecreatefromjpeg ($bildpfad_ganz); // Foto von der Festplatte als Bild-Resource einlesen
			$thumbnail = imagecreatetruecolor($this->th_breite, $this->th_hoehe); // schwarzes Bild mit vorgegebener Größe anlegen
			
			// Ermittle auszuschneidende Seitenlänge (die kürzere) unter Berücksichtugung des crop Faktors
			// und den Offset zum Ausschneiden, d.h. schneide aus der Bildmitte ein Quadrat mit Kantenlänge = 
			// kurze Seite * cropfaktor
			$sample_breite = (int)($breite / $this->th_crop);
			$sample_hoehe = (int)($hoehe / $this->th_crop);
			if ($sample_breite > $sample_hoehe) $sample_breite = $sample_hoehe;
			else								$sample_hoehe = $sample_breite;
			$x = (int)(($breite - $sample_breite) / 2);
			$y = (int)(($hoehe -  $sample_hoehe) / 2);

			// Schneide thumbnail aus: imagecopyresampled() liefert bessere Qualität als imagecopyresized()
			imagecopyresampled ($thumbnail, $bild,  0, 0, $x, $y, $this->th_breite, $this->th_hoehe, $sample_breite, $sample_hoehe); 
			
			// Bild-Resource in JPEG-Datei schreiben.
			imagejpeg($thumbnail, $thumbpfad_ganz, $this->th_qual); 

			$bild = NULL;
			$thumbnail = NULL;

		}
	}


	/**
	* Create a directory recursively (like `mkdir -p $dir').
	*/	
	public function mkRecursiveDir($dir)
	{
		$path_array = explode($this->trennzeichen, $dir);

		$path = '';
		foreach($path_array as $dir) {
			if ($dir == '') { continue; }
			$path .= $dir.$this->trennzeichen;	

	 		if (file_exists($path)) { continue; }

			if ((! is_file($path) || ! file_exists($path))
				&& is_writable(dirname($path)))
			{

				mkdir($path, 0777);
			}
		}
	} // End mkRecursiveDir()


	public function absPath($dir)
	{
		$path_array = explode($this->trennzeichen, $dir);
	
		$path = '';
		foreach($path_array as $dir) {
			if ($dir == '' || $dir == '.' || $dir == '..' || $dir == '...') { continue; }
			$path .= $dir.$this->trennzeichen;
		}
		return $path;
	}

	private function hat_bilder ($verzeichnis_eintrag) {
		$ret = false;
        if (is_dir($verzeichnis_eintrag))
			$verzeichnis_handle = dir($verzeichnis_eintrag);

		if ($verzeichnis_handle) {
			while (false !== ($verzeichnis_eintrag = $verzeichnis_handle->read()) && !$ret) {
				if (eregi("\.(jpe?g)$",$verzeichnis_eintrag)) {
					$ret = true;
				}
			}
		}
	

		$verzeichnis_handle = NULL;
		return $ret;
	}

} // class airy_album_verzeichnis



// -----------------------------------------------------------------------------------
//
//	class airy_album
//	
//	Beerbt airy_album_verzeichnis
//  und erzeugt den html output
//
// -----------------------------------------------------------------------------------

class airy_album extends airy_album_verzeichnis {	


	public function get_html () {
		$html = '';
		$group = rand(1, 10000); // für die lightbox
	//	$html .= print_r($this->album_liste);

	//	return $html;
	
	$templatepath = get_bloginfo("template_url") . '/';
	 
	$html .= "<div id=\"gallery-1\">";
	

		while (!(NULL === ($el = $this->get_next_element()))) {

			switch($el['typ']) {

				case 'root':
						if (is_user_logged_in())
							$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"{$this->wpurl}516/?album={$el['basis_verzeichnis']}\">download pictures here...</a></small>";
							//$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"{$templatepath}airy_plain.php?album={$el['basis_verzeichnis']}\">Show images only</a></small>";
						//else
							// Vorläufig lassen wir login oberhalb der gallery weg
							//$html .= "<small class=\"metadata\"><a href=\"{$this->wpurl}wp-admin/\">Login to see downloadlink here</a></small>";
						break;

			    case 'bild': 
						$sel = "unmarkiert";
						if ($el["bild_beschreibung"] != "")
							$sel = "markiert";
							
						$bildpfad_ganz = $this->wpurl . $el['basis_verzeichnis'] . $el['bildname'];
						$thumbpfad_ganz = $this->wpurl . $el['thumbpfad_ganz']. $el["thumbnail"];
						$html .=<<<EOT
						<dt class='gallery-icon'> 
						<dl class='gallery-item'> 
<a rel="lightbox[$group]" href="$bildpfad_ganz" ><img class="$sel" src="$thumbpfad_ganz" alt="{$el["bild_beschreibung"]}" boder="0"></a>
</dl>

</dt>

EOT;
						break;
				case 'verzeichnis':
						break;
						
				default:
						break;
			}

		}
		$html .= "<br style='clear: both;' /> </div>";
		return $html;
	}
}

// -----------------------------------------------------------------------------------
//
//	class airy_album_plain
//	
//	Ein Album zum direkten Download von Bildern
//
// -----------------------------------------------------------------------------------

class airy_album_plain extends airy_album_verzeichnis {	


	public function get_html () {
		$html = '';
		$group = rand(1, 10000); // für die lightbox
	
		$templatepath = get_bloginfo("template_url") . '/';
		$html .= "<div id=\"gallery-2\">";


		while (!(NULL === ($el = $this->get_next_element()))) {

			switch($el['typ']) {

				case 'root':
						break;

			    case 'bild': 
						$bildpfad_ganz = $this->wpurl . $el['basis_verzeichnis'] . $el['bildname'];
						$thumbpfad_ganz = $this->wpurl . $el['thumbpfad_ganz']. $el["thumbnail"];
						$html .=<<<EOT
						<dt class='gallery-icon'> 
						<dl class='gallery-item'> 
<a href="$bildpfad_ganz" ><img class="unmarkiert" src="$bildpfad_ganz"}" boder="0"></a>
</dl>
</dt>
EOT;
						break;
				case 'verzeichnis':
						break;
						
				default:
						break;
			}

		}
		$html .= "<br style='clear: both;' /> </div>";
		return $html;
	}
}

?>