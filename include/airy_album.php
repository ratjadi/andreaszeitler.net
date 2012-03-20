<?php
require_once("xmlparser.php");
function _debug ($str) {
	//echo ($str) . '<br />';
}

function _debug_1 ($str) {
	//echo ($str) . '<br />';
	}

// -----------------------------------------------------------------------------------
//
//	Airyalbum v0.02
//	by Andreas Zeitler http://www.andreaszeitler.net
//
//	Änderung: 17.05.2010 22:01
//  akt-obj wird gespeichert, wenn akt_album gesetzt wird
//
//	Änderung: 09.06.2010 23:04
//  req_width und req_height bestimmen die Grösse des angezeiten Bildes
//  size_tolerance die erlaubte Abweichung als Bruchzahl (z.b. 1.2 für 20%),
//  d.h. befindet sich das Originalbild innerhalb der Toleranz, wird kein Bild generiert
//  Ggf. wird das Bild von einer größeren Vorlage generiert
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
//
// set_akt_album ($akt_album='') setzt das aktuelle Album als Pfad, z.b. 'portfolio/Hochzeit/'
// set_akt_obj ($akt_album)      setzt einen Zeiger auf das aktuelle Album-Objekt (ein Objekt vom typ airy_album_verzeichnis)
//                               entsprechend dem Pfad in $akt_album
//
//  reset_album ()               setzt alle zeiger der item_list arrays zurück, damit get_next_element() wieder von vorne
//                               beginnen kann
// -----------------------------------------------------------------------------------

class airy_album_verzeichnis {
	protected  $php_basis;
	protected  $basis_verzeichnis;
	protected  $airy_verzeichnis;
	protected  $th_breite=150, $th_hoehe=150, $th_crop=1.2, $th_qual=170, $th_prefix='_th_';
	protected  $req_width=800, $req_height=600, $size_tolerance=1.5;
	protected  $album_titel, $album_teaser, $album_beschreibung;
	protected  $html;
	protected  $item_liste=array(); // Verzeichnisse oder Thumbnails, je nach typ des aktuellen Objekts
	protected  $first_element=true;
	protected  $werbinich=NULL;
	protected  $ueber_verzeichnis=NULL;	
	protected  $akt_album='';
	protected  $akt_obj;
	protected  $level=0;
	protected  $show_root=false;
	protected  $param_list;
	protected  $vorschaubild;
	private $trennzeichen='/';

	function __construct ($pfad_in, $airy_verzeichnis='airy_files',$level=0) {
		$server_vars = ($_SERVER) ? $_SERVER :  $HTTP_SERVER_VARS;
		
		$this->php_basis = @$server_vars['PHP_SELF'];
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
			$this->akt_obj = current($this->item_liste);
		}
		else {
			$this->akt_album = $akt_album;
			$this->akt_obj = $this->set_akt_obj ($akt_album);
			//echo $akt_album . '<br />';
			//var_dump ($this->akt_obj);
		}
	}
	
	
	
	private function set_akt_obj ($akt_album) {
		if ($this->werbinich() == 'thumbnails') {
			//echo $this->basis_verzeichnis . '<br />';
			if ($this->basis_verzeichnis == $akt_album)
				return $this;	
			else
				return NULL;
		}
		elseif ($this->werbinich() == 'verzeichnis') {
			//echo $this->basis_verzeichnis . '<br />';
			if ($this->basis_verzeichnis == $akt_album)
				return $this;
				
			foreach ($this->item_liste as $obj) { 
				//echo 'Vergleich: ' . $obj->get_basis_verzeichnis() . " -> " . $akt_album . ' : ' . (strstr($akt_album,$obj->get_basis_verzeichnis())==true?'true':'false') . '<br />';
				if ( strstr($akt_album,$obj->get_basis_verzeichnis()) ) {
					if ( !(NULL === ($ret = $obj->set_akt_obj($akt_album)) ) ) {
						reset($this->item_liste);
						return $ret;
					}
				}
			}
			reset($this->item_liste);
			return NULL;
		}
	}
	
	public function reset_album () {
		if ($this->werbinich() == 'thumbnails') {
			$this->first_element = true;
			reset($this->item_liste);
		}
		else {
			foreach ($this->item_liste as $obj) {
				$obj->reset_album();
			}
			$this->first_element = true;
			reset($this->item_liste);
			return;
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
	
	public function set_size_specs ($breite, $hoehe, $toleranz) {
		$this->req_width = $breite;
		$this->req_height = $hoehe;		
		$this->size_tolerance = $toleranz;
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
				$element['album_menu_name'] = $this->album_menu_name;
				$element['album_beschreibung'] = $this->album_beschreibung;
				$element['werbinich'] = $this->werbinich();
				$element['vorschaubild'] = $this->vorschaubild;
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
					$element['bild_titel'] = $bildinfo['bild_titel'];
					$element['bild_beschreibung'] = $bildinfo['bild_beschreibung'];
					$element['basis_verzeichnis'] = $this->basis_verzeichnis;
					$element['airy_verzeichnis'] = $this->airy_verzeichnis;	
					$element['thumbpfad_ganz'] = $this->airy_verzeichnis . $this->absPath($this->basis_verzeichnis);
					$path_array = explode($this->trennzeichen, $this->basis_verzeichnis);
					$element['verzeichnis'] = $path_array[count($path_array)-2];

					//echo $bildinfo['gen_bild'] . '<br />';
					if ($bildinfo['gen_bild'] != "n.a.") // ein generiertes Bild existiert
						$element['bild'] = $element['thumbpfad_ganz'] . $bildinfo['gen_bild'];
					else 
						$element['bild'] = $element['basis_verzeichnis'] . $bildname;

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
_debug_1 ($element['basis_verzeichnis']);
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
					$airy_aktuelles_verzeichnis->set_size_specs($this->req_width, $this->req_height, $this->size_tolerance);
					$airy_aktuelles_verzeichnis->set_thumbnail_specs($this->th_breite, $this->th_hoehe, $this->th_crop, $this->th_qual, $this->th_prefix);

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
		$this->album_menu_name = '';
		if (file_exists($this->basis_verzeichnis . 'airy_info.ini')) {
			$ini_array = parse_ini_file($this->basis_verzeichnis . 'airy_info.ini');
			$this->album_titel = htmlentities($ini_array["titel"]);
			$this->album_teaser =  htmlentities($ini_array["teaser"]);
			$this->album_beschreibung = htmlentities($ini_array["beschreibung"]);
			$this->album_menu_name =  htmlentities($ini_array["menu_name"]);
		}
		elseif (file_exists($this->basis_verzeichnis . 'airy_info.xml')) {
			$xml_obj = new xmlToArrayParser(file_get_contents($this->basis_verzeichnis . 'airy_info.xml'));
			$xml_array = $xml_obj->array;
			$this->album_titel = nl2br(htmlentities($xml_array["top"]["titel"],ENT_COMPAT,"UTF-8"));
			$this->album_teaser = !empty($xml_array["top"]["teaser"]) ? nl2br(($xml_array["top"]["teaser"])) : '';
			$this->album_beschreibung = !empty($xml_array["top"]["beschreibung"]) ? nl2br(($xml_array["top"]["beschreibung"])) : '';
			$this->album_menu_name = nl2br(htmlentities($xml_array["top"]["menu_name"],ENT_COMPAT,"UTF-8"));
		}
		else {
			$path_array = explode($this->trennzeichen, $this->basis_verzeichnis);
			$this->album_titel = $path_array[count($path_array)-2];
			$this->album_menu_name = $this->album_titel;
		}

	}

	private function erzeuge_thumbnail ($verzeichnis_eintrag, $erzwingen=false) {
		$bildpfad_ganz = $this->basis_verzeichnis . $verzeichnis_eintrag;
		$thumbpfad_ganz = $this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis) . $this->th_prefix . $verzeichnis_eintrag;
		$genbild_prefix = "{$this->req_width}x{$this->req_height}_";
		$genbildpfad_ganz = $this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis) . $genbild_prefix . $verzeichnis_eintrag;

		if ( eregi("\.(jpe?g)$",$bildpfad_ganz)) {

			if (substr($verzeichnis_eintrag , 0, 5) == "prev_") {
				$this->ueber_verzeichnis->vorschaubild = $this->erzeuge_vorschaubild($verzeichnis_eintrag, 300, 200);
			}
			
			// Es werden später noch mehr Infos (Comment und so) gespeichert
			$this->item_liste[$verzeichnis_eintrag] = array("thumbnail" => $this->th_prefix . $verzeichnis_eintrag); 
			
		    $titel = "";
			$beschreibung = "";
			$size = getimagesize ( $bildpfad_ganz, $info); 		
			if(is_array($info)) {  
				$iptc = iptcparse($info["APP13"]);
				$titel = $iptc['2#005'][0];
				$beschreibung = $iptc['2#120'][0];
			}
			
			$exif = @exif_read_data($bildpfad_ganz,ANY_TAG,true);
			$this->item_liste[$verzeichnis_eintrag]["bild_titel"] = "";
			$this->item_liste[$verzeichnis_eintrag]["bild_beschreibung"] = "";
			// Aus irgendeinem verdammten Grund steht im Title vor jedem Zeichen ein 0x00, dass dann im Browser
			// zu einem BOM (byte order marking) wird.
			//$titel = str_replace(pack("C",0x00),"",$exif["IFD0"]["Title"]);
			//$beschreibung = $exif["IFD0"]["ImageDescription"];

			if ($beschreibung != "" && $beschreibung != "OLYMPUS DIGITAL CAMERA") {
				if ($titel == "" ) {
					$this->item_liste[$verzeichnis_eintrag]["bild_titel"] = htmlentities( $beschreibung );
				}
				else {
					$this->item_liste[$verzeichnis_eintrag]["bild_titel"] = htmlentities( ($titel) );
					$this->item_liste[$verzeichnis_eintrag]["bild_beschreibung"] = htmlentities( $beschreibung );
				}
			}
			else {
				if ($titel != "")
					$this->item_liste[$verzeichnis_eintrag]["bild_titel"] = htmlentities( ($titel) );
				else
					$this->item_liste[$verzeichnis_eintrag]["bild_titel"] = htmlentities( ($exif["COMMENT"]["0"]) );
			}

			/*foreach ($exif as $key => $section) {
			    foreach ($section as $name => $val) {
			        echo "$key.$name: $val<br />\n";
			    }
			}*/
			
			//output_iptc_data($bildpfad_ganz);
		}
	

		list ($breite, $hoehe) = getimagesize ($bildpfad_ganz);

		$this->item_liste[$verzeichnis_eintrag]["gen_bild"] = "n.a.";

		// Wenn das Originalbild grösser ist als die geforderte Dimensionen (inkl. Toleranz)
		// dann wird ein neues Bild generiert
		
		//echo "$breite x $hoehe: {$this->req_width} x {$this->req_height}: {$this->size_tolerance}: $genbildpfad_ganz" . '<br />';
		
		$use_generated_image = ($breite > $this->req_width * $this->size_tolerance
			|| $hoehe > $this->req_height * $this->size_tolerance) ;
		
		if ($use_generated_image
			&& ($erzwingen || !file_exists($genbildpfad_ganz)) ) {
	
			/*if ($hoehe > $this->req_height * $this->size_tolerance) {
				$gen_height = $this->req_height;
				$factor = $this->req_height / $hoehe;
				$gen_width = $breite * $factor;
			}
			if ($breite > $this->req_width * $this->size_tolerance) {
				$gen_width = $this->req_width;
				$factor = $this->req_width / $breite;
				$gen_height = $hoehe * $factor;
			}*/
			
			//$image = new Imagick('image.jpg');	
			//echo $image;
			$radius = round($this->req_width / 2400.0, 1);	 // ergibt z.b. 0.3 für 800 Pixel	

			exec('/usr/bin/convert'
					.' -geometry '."{$this->req_width}x{$this->req_height}"
					.' -quality '.'80'
					//.' -adaptive-sharpen '."{radius}x0.2"
					.' "'.$bildpfad_ganz.'"'.' "'.$genbildpfad_ganz.'"');
		}
		
		if ($use_generated_image && file_exists($genbildpfad_ganz)) {
			$this->item_liste[$verzeichnis_eintrag]["gen_bild"] = $genbild_prefix . $verzeichnis_eintrag;
		}
			
		if ( eregi("\.(jpe?g)$",$bildpfad_ganz) && ($erzwingen || !file_exists($thumbpfad_ganz)) ) {
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
	* Erzeuge ein 300x200 Vorschaubild.
	* Gibt den ganzen Pfad auf das erzeugte Bild zurück
	*/	
	private function erzeuge_vorschaubild ($verzeichnis_eintrag, $req_width, $req_height) {
		$bildpfad_ganz = $this->basis_verzeichnis . $verzeichnis_eintrag;
		$genbild_prefix = "{$req_width}x{$req_height}_";
		$genbildpfad_ganz = $this->airy_verzeichnis.$this->absPath($this->basis_verzeichnis) . $genbild_prefix . $verzeichnis_eintrag;

		list ($breite, $hoehe) = getimagesize ($bildpfad_ganz);

		$use_generated_image = ($breite > $req_width * 1.1
			|| $hoehe > $req_height * 1.1) ;
		
		if ($use_generated_image) {
			if (!file_exists($genbildpfad_ganz)) {
				exec('/usr/bin/convert'
						.' -geometry '."{$req_width}x{$req_height}"
						.' -quality '.'80'
						//.' -adaptive-sharpen '."{radius}x0.2"
						.' "'.$bildpfad_ganz.'"'.' "'.$genbildpfad_ganz.'"');
			}
			return ($genbildpfad_ganz);
		}
		else
			return "";
		
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
//	class airy_album_portfolio_extended
//	
//	Beerbt airy_album_verzeichnis
//  und erzeugt den html output
//  Für das Portfolio, zeigt es mit
//  get_menu($param_list=array()) die top-level Alben an (z.b. Hochzeit, Interieur, PR + Image, ...)
//  get_html ($param_list=array()) die Alben untereinander, Albumtitel ist Überschrift, Teaser und Beschreibung,
//                                 aller Bilder aller Unteralben werden gezeigt (alle aufgeklappt)
//
//  HINWEIS: nach get_menu muss vor get_html ein reset_album() gemacht werden, denn der Zeiger steht sonst am Ende.
// -----------------------------------------------------------------------------------

class airy_album_portfolio_extended extends airy_album_verzeichnis {

	public function get_menu ($param_list=array()) {
		$html = '';

		while (!(NULL === ($el = $this->get_next_element()))) {
			//echo $el['basis_verzeichnis'] . '<br />';
			switch($el['typ']) {
				
				case 'bild':
						break;
				
				case 'verzeichnis':
						$klasse = "level{$el['level']}";

						if ($klasse=="level1") {
							$href = "";
	
							if ($el['basis_verzeichnis'] == $this->akt_album) 
								$klasse .= ' current';
	
							if ($el['werbinich'] == 'verzeichnis') {
								$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
							}
	
							$html .= "<li><a {$href}>{$el['album_menu_name']}</a></li>";
						}
						break;
						
				default:
						break;
			}

		}
		return $html;
	}

	public function get_html ($param_list=array()) {
		$html = '';
		$group = 1; // für die lightbox
		$bildcount = 0;
		$first = true;
	//	$html .= print_r($this->album_liste);

		$html .= "<div id=\"gallery-2\">";
		
		//$this->akt_obj->first_element = false; // Trick, um das root nicht zu zeigen, wir starten ja relativ am akt_obj
		// die Verwendung von akt_obj anstatt this bewirkt, dass nur das aktuelle Album angezeigt wird 
		// (vorher mit akt_album gesetzt), aber nicht alle übergeordneten Verzeichnisse
		if ($this->akt_obj)
			while (!(NULL === ($el = $this->akt_obj->get_next_element()))) { 
	
				switch($el['typ']) {
					
					case 'bild':
					
							// Alle Bilder aller Alben werden angezeigt (aufgeklappt)
							//if ($el['basis_verzeichnis'] == $this->akt_album) {
								$sel = "unmarkiert";
								
								$bildpfad_ganz = $el['bild'];
								$thumbpfad_ganz = $el['thumbpfad_ganz']. $el["thumbnail"];
								$html .=<<<EOT
							<dt class='gallery-icon'> 
							<dl class='gallery-item'> 
								<a href="$bildpfad_ganz" title="{$el["bild_titel"]}" alt="{$el["bild_beschreibung"]}" beschreibung="{$el["bild_beschreibung"]}" nummer="$bildcount"><img class="$sel" src="$thumbpfad_ganz" boder="0"></a>
								</dl>
	
							</dt>
EOT;
							$bildcount++;
							//}
							break;
					
					case 'verzeichnis':

							if (!$first) {
								
								// Die Beschreibung vom vorigen Verzeichnis (zuerst Bilder, dann Beschreibung!)
								$html .= "<div id=\"airy_album_spacer\">&nbsp;</div>\n";	
								$html .= $beschreibung;	
								
							}
							$first = false;
							$klasse = "level{$el['level']}";
	
							// Teaser und Beschreibung merken, deren Formatierung über css erfolgt
							$teaser = '';					
							$beschreibung = '';
	
							if ('' != $el['album_teaser']) {
								$teaser = "<div id=\"airy_album_teaser\" class=\"{$klasse}\">" . $el['album_teaser'] . "</div>";
							}
							if ('' != $el['album_beschreibung']) {
								$beschreibung = "<div id=\"airy_album_beschreibung\" class=\"{$klasse}\">" . $el['album_beschreibung'] . "</div>";
							}
	
							// Jedes Album wird mit dem Titel überschriftet, nicht klickbar (-> ist ja schon aufgeklappt)
							$html .= "<div id=\"airy_album_header\" class=\"{$klasse}\">{$el['obj']->album_titel}</div>";
							$html .= $teaser;
							break;
							$group++;
					default:
							break;
				}
	
			}
			
		// Die Beschreibung vom letzten Verzeichnis und ein Abstand zum unteren Rand
		$html .= "<div id=\"airy_album_spacer\">&nbsp;</div>\n";	
		$html .= $beschreibung;
		$html .= "<div id=\"airy_album_spacer\">&nbsp;</div>\n";	

		$html .= "</div>";
		return $html;
	}
}

// -----------------------------------------------------------------------------------
//
//	class airy_album_portfolio
//	
//	Beerbt airy_album_verzeichnis
//  und erzeugt den html output
//  Für das Portfolio, zeigt es mit
//  get_html ($param_list=array()) die Alben untereinander, Albumtitel ist Überschrift, Teaser und Beschreibung,
//                                 aller Bilder aller Unteralben werden gezeigt (alle aufgeklappt)
//
// -----------------------------------------------------------------------------------

class airy_album_portfolio extends airy_album_verzeichnis {	


	public function get_html ($param_list=array()) {
		$html = '';
		$group = 1; // für die lightbox
		$first=true; // für Abstand vor Verzeichnis (aber nicht ganz oben) HACK!

		$teaser = "";
		$beschreibung = "";
		$html .= "<div id=\"gallery-2\">";
		
		while (!(NULL === ($el = $this->get_next_element()))) {

			switch($el['typ']) {
				
				case 'bild':
				
						// Alle Bilder aller Alben werden angezeigt (aufgeklappt)
							$sel = "unmarkiert";
							if ($el["bild_titel"] != "")
								$sel = "markiert";
							
							$bildpfad_ganz = $el['bild'];
							$thumbpfad_ganz = $el['thumbpfad_ganz']. $el["thumbnail"];
							$html .=<<<EOT
						<dt class='gallery-icon'> 
						<dl class='gallery-item'> 
							<a rel="lightbox[$group]" href="$bildpfad_ganz" title="{$el["bild_titel"]}"><img class="$sel" src="$thumbpfad_ganz" boder="0"></a>
							</dl>
	
						</dt>
EOT;
						//}
						break;
				
				case 'verzeichnis':
						if (!$first) {
							$html .= "<div id=\"airy_album_spacer\">&nbsp;</div>\n";	
						}
						$first = false;
						$klasse = "level{$el['level']}";
	
						// Teaser merken, deren Formatierung über css erfolgt
						$teaser = '';		
						$beschreibung = '';		
	
						if ('' != $el['album_teaser']) {
							$teaser = "<div id=\"airy_album_teaser\" class=\"{$klasse}\">" . $el['album_teaser'] . "</div>";
						}
						if ('' != $el['album_beschreibung']) {
							$beschreibung = "<div id=\"airy_album_beschreibung\" class=\"{$klasse}\">" . $el['album_beschreibung'] . "</div>";
						}
	
						// Jedes Album wird mit dem Titel überschriftet, nicht klickbar (-> ist ja schon aufgeklappt)
						$html .= "<div id=\"airy_album_header\" class=\"{$klasse}\">{$el['obj']->album_titel}</div>";
						$html .= $teaser;
						$html .= $beschreibung;
						break;
						$group++;
				default:
						break;
			}
		}
		
		$html .= "</div>";
		return $html;
	}
}

// -----------------------------------------------------------------------------------
//
//	class airy_album_clients
//	
//	Beerbt airy_album_verzeichnis
//  und erzeugt den html output
//  Für den login Bereich für Kunden, zeigt es mit
//  get_menu($param_list=array()) die top-level Alben an (z.b. Hochzeit, Portraits...)
//  get_html ($param_list=array()) die Alben als Links, und dann das geklickte als Galerie
//  
//  In der Galerie-Ansicht wird ein download-Link angezeigt, und airy_album_plain verwendet
//
//  HINWEIS: nach get_menu muss vor get_html ein reset_album() gemacht werden, denn der Zeiger steht sonst am Ende.
// -----------------------------------------------------------------------------------

class airy_album_clients extends airy_album_verzeichnis {	

	public function get_menu ($param_list=array()) {
		$html = '';

		while (!(NULL === ($el = $this->get_next_element()))) {
			//echo $el['basis_verzeichnis'] . '<br />';
			switch($el['typ']) {
				
				case 'bild':
						break;
				
				case 'verzeichnis':
						$klasse = "level{$el['level']}";

						if ($klasse=="level1") {
							$href = "";
	
							if ($el['basis_verzeichnis'] == $this->akt_album) 
								$klasse .= ' current';
	
							//if ($el['werbinich'] == 'verzeichnis') {
								$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
							//}
	
							$html .= "<li><a {$href}>{$el['album_menu_name']}</a></li>";
						}
						break;
						
/*				case 'root':
						echo $el['basis_verzeichnis'];
						break;*/
						
				default:
						break;
			}

		}
		return $html;
	}

	public function get_html ($param_list=array()) {
		$html = '';
		$group = 1; // für die lightbox

		$html .= "<div id=\"gallery-2\">";
		
		//$this->akt_obj->first_element = false; // Trick, um das root nicht zu zeigen, wir starten ja relativ am akt_obj
		
		// die Verwendung von akt_obj anstatt this bewirkt, dass nur das aktuelle Album angezeigt wird 
		// (vorher mit akt_album gesetzt), aber nicht alle übergeordneten Verzeichnisse
		if ($this->akt_obj)
			while (!(NULL === ($el = $this->akt_obj->get_next_element()))) {
	
				switch($el['typ']) {
	
					case 'bild':
							// Die Bilder des aktuellen Albums werden angezeigtecho
							if ($el['basis_verzeichnis'] == $this->akt_album) {
								$sel = "unmarkiert";
									
								$bildpfad_ganz = $el['bild'];
								$thumbpfad_ganz = $el['thumbpfad_ganz']. $el["thumbnail"];
								$html .=<<<EOT
							<dt class='gallery-icon'> 
							<dl class='gallery-item'> 
								<a rel="lightbox[$group]" href="$bildpfad_ganz" title="{$el["bild_titel"]}"><img class="$sel" src="$thumbpfad_ganz" boder="0"></a>
								</dl>
	
							</dt>
EOT;
							}
							break;
					
					case 'verzeichnis':
							//echo $el['basis_verzeichnis'] . '<br />';
							$klasse = "level{$el['level']}";
	
							$teaser = '';					
							$beschreibung = '';
	
							// Teaser und Beschreibung merken, deren Formatierung über css erfolgt
							if ('' != $el['album_teaser']) {
								$teaser = "<div id=\"airy_album_teaser\" class=\"{$klasse}\">" . $el['album_teaser'] . "</div>";
							}
							if ('' != $el['album_beschreibung']) {
								$beschreibung = "<div id=\"airy_album_beschreibung\" class=\"{$klasse}\">" . $el['album_beschreibung'] . "</div>";
							}
	 
							if ($el['basis_verzeichnis'] == $this->akt_album) {
								// Das aktuelle Album wird ohne Link als header angezeigt
								$html .= "<div id=\"airy_album_header\" class=\"{$klasse}\">{$el['obj']->album_titel}</div>";
								
								if ($el['werbinich'] == 'thumbnails') {
									// Wenn es ein Bildverzeichnis ist (d.h. es enthält thumbs und keine Verzeichnisse),
									// dann werden die Zip-Downloadoption und die einfache Ansicht als Link zur Verfügung gestellt
									$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
									$href = "href=\"" . url_plus_param ('dl', 'y', $this->php_basis, $param_list) . "\"";
									
									$html .= "<small class=\"metadata\"><a target=\"_blank\" {$href}>Einfache Bilderansicht - hier clicken!...</a></small><br />";
									$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"zipdownload.php?airyal={$el['basis_verzeichnis']}&incp=n\">Ganzes Album als zip download - hier clicken!...</a></small>";
								}
								else {
									// Wenn es ein Verzeichnis ist, nur die Zip-Downloadoption
									$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"zipdownload.php?airyal={$el['basis_verzeichnis']}&incp=y\">Ganzes Album als zip download - hier clicken!...</a></small>";
									$html .= "<br /><br />";
								}
								
							}
							else {
								// Verzeichnisse werden als Links dargestellt -> sind klickbar
								$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
								$html .= "<div id=\"airy_album_header\"><a {$href}>{$el['obj']->album_titel}</a></div>";
							}
	
							$html .= $teaser;
							$html .= $beschreibung;
							break;
							$group++;
					default:
							break;
				}
	
			}


		$html .= "</div>";
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

class airy_album_plain extends airy_album_clients {	

	public function get_menu ($param_list=array()) {
		$html = '';

		while (!(NULL === ($el = $this->get_next_element()))) {
			//echo $el['basis_verzeichnis'] . '<br />';
			switch($el['typ']) {
				
				case 'bild':
						break;
				
				case 'verzeichnis':
						$klasse = "level{$el['level']}";

						if ($klasse=="level1") {
							$href = "";
	
							if ($el['basis_verzeichnis'] == $this->akt_album) 
								$klasse .= ' current';
	
							//if ($el['werbinich'] == 'verzeichnis') {
								$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
							//}
	
							$html .= "<li><a {$href}>{$el['album_menu_name']}</a></li>";
						}
						break;
						
/*				case 'root':
						echo $el['basis_verzeichnis'];
						break;*/
						
				default:
						break;
			}

		}
		return $html;
	}
	
	public function get_html ($param_list=array()) {
		$html = '';
		$group = rand(1, 10000); // für die lightbox
	
		$html .= "<div id=\"gallery-3\">";

		if ($this->akt_obj)
			while (!(NULL === ($el = $this->akt_obj->get_next_element()))) {

			switch($el['typ']) {

				case 'root':
						break;

			    case 'bild': 
				    	if ($el['basis_verzeichnis'] == $this->akt_album) {
				    		$originalbild = $el['basis_verzeichnis'] . $el["bildname"];
							$bildpfad_ganz = $el['bild'];
							$thumbpfad_ganz = $el['thumbpfad_ganz']. $el["thumbnail"];
							$html .= <<<EOT
<a href="{$originalbild}" target="_blank"><img class="unmarkiert" src="$bildpfad_ganz"}" boder="0"></a>

EOT;
						}
						break;
					case 'verzeichnis':
							//echo $el['basis_verzeichnis'] . '<br />';
							$klasse = "level{$el['level']}";
	
							$teaser = '';					
							$beschreibung = '';
	
							// Teaser und Beschreibung merken, deren Formatierung über css erfolgt
							if ('' != $el['album_teaser']) {
								$teaser = "<div id=\"airy_album_teaser\" class=\"{$klasse}\">" . $el['album_teaser'] . "</div>";
							}
							if ('' != $el['album_beschreibung']) {
								$beschreibung = "<div id=\"airy_album_beschreibung\" class=\"{$klasse}\">" . $el['album_beschreibung'] . "</div>";
							}
	 
							if ($el['basis_verzeichnis'] == $this->akt_album) {
								// Das aktuelle Album wird ohne Link als header angezeigt
								$html .= "<div id=\"airy_album_header\" class=\"{$klasse}\">{$el['obj']->album_titel}</div>";
								
								if ($el['werbinich'] == 'thumbnails') {
									// Wenn es ein Bildverzeichnis ist (d.h. es enthält thumbs und keine Verzeichnisse),
									// dann werden die Zip-Downloadoption und die einfache Ansicht als Link zur Verfügung gestellt
									$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
									$href = "href=\"" . url_plus_param ('dl', 'y', $this->php_basis, $param_list) . "\"";
									
									$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"zipdownload.php?airyal={$el['basis_verzeichnis']}&incp=n\">Ganzes Album als zip download - hier clicken!...</a></small>";
								}
								else {
									// Wenn es ein Verzeichnis ist, nur die Zip-Downloadoption
									$html .= "<small class=\"metadata\"><a target=\"_blank\" href=\"zipdownload.php?airyal={$el['basis_verzeichnis']}&incp=y\">Ganzes Album als zip download - hier clicken!...</a></small>";
									$html .= "<br /><br />";
								}
								
							}
							else {
								// Verzeichnisse werden als Links dargestellt -> sind klickbar
								$href = "href=\"" . url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list) . "\"";
								$html .= "<div id=\"airy_album_header\"><a {$href}>{$el['obj']->album_titel}</a></div>";
							}
	
							$html .= $teaser;
							$html .= $beschreibung;
							$group++;
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
//	class airy_album_showcase
//	
//	Beerbt airy_album_verzeichnis
//  und erzeugt den html output
//  Für das Portfolio, zeigt es mit
//  get_menu($param_list=array()) die top-level Alben an (z.b. Hochzeit, Interieur, PR + Image, ...)
//  get_html ($param_list=array()) die Alben untereinander, Albumtitel ist Überschrift, Teaser und Beschreibung,
//                                 aller Bilder aller Unteralben werden gezeigt (alle aufgeklappt)
//
//  HINWEIS: nach get_menu muss vor get_html ein reset_album() gemacht werden, denn der Zeiger steht sonst am Ende.
// -----------------------------------------------------------------------------------

class airy_album_showcase extends airy_album_portfolio_extended {

	public function get_menu ($param_list=array()) {
		$html = '';
		while (!(NULL === ($el = $this->get_next_element()))) {
			//echo $el['basis_verzeichnis'] . '<br />';
			//echo "<pre>".print_r($el,true)."</pre>";

			switch($el['typ']) {
				
				case 'bild':
						break;
				
				case 'verzeichnis':
						$klasse = "level{$el['level']}";

						if ($klasse=="level1") {
							$href = "";
	
							if ($el['basis_verzeichnis'] == $this->akt_album) 
								$klasse .= ' current';
	
							if ($el['werbinich'] == 'verzeichnis') {
								$href = url_plus_param ('airyal', $el['basis_verzeichnis'], $this->php_basis, $param_list);
							}
	
							//$html .= "<li><a {$href}>{$el['album_menu_name']}</a></li>";
							
							$html .= " 
									<a href=\"".$href."\">
										<div id=\"mainselector\">
											<div id=\"journalentrytitel\">".$el['album_titel']."</div>
											<h1>".$el['album_teaser']."</h1>
											<img src=\"".$el['vorschaubild']."\" />
											".$el['album_beschreibung']."
										</div>
									</a>";
						}
						break;
						
				default:
						break;
			}

		}
		return $html;
	}


}

function output_iptc_data( $image_path ) {    
    $size = getimagesize ( $image_path, $info);        
     if(is_array($info)) {    

        $iptc = iptcparse($info["APP13"]);
        foreach (array_keys($iptc) as $s) {              
            $c = count ($iptc[$s]);
            for ($i=0; $i <$c; $i++) 
            {
                echo $s.' = '.$iptc[$s][$i].'<br />';
            }
        }                  
    }             
}
//EOF