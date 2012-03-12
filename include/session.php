<?php 
// globale Variable zum Speichern der Datenbankverbindung
//global $_db_connection;
global $_db_connection;
$_db_connection = $db->get_conn();
// Initialisieren der Session
function sess_open($sess_path, $sess_name)
{
   global $_db_connection;
 
   if (!isset($_db_connection))
   {
      die ("Konnte DB-Verbindung nicht aufbauen");
   }
   //$db_res = mysql_select_db("lexoppia",$_db_connection);
   if (false === $db_res)
   {
      die ("Datenbankfehler: ".mysql_error());
   }
   return true;
}
// Abschliessende Aufgaben ausfuehren
function sess_close()
{
   global $_db_connection;
   @mysql_close($_db_connection);
   return true;
}
// Auslesen der Session oder Anlegen der Session
function sess_read($sess_id)
{
   global $_db_connection;
   // Versuchen die Daten auszulesen
   $result = mysql_query("SELECT Data FROM cms_sessions
                     WHERE SessionID = '$sess_id';",
   $_db_connection);
   if (false === $result)
   {
      die ("Datenbankfehler: ".mysql_error());
   }
   $current_time = time();
   // Konnten Daten gefunden werden?
   if (0 === mysql_num_rows($result))
   { // Nein, keine Daten => Session ist neu
      // Session neu anlegen
      $db_res = mysql_query("INSERT INTO cms_sessions
                           (SessionID, TimeTouched)
                           VALUES ('$sess_id', $current_time);",
      $_db_connection);
      if (false === $db_res)
      {
         die ("Datenbankfehler: ".mysql_error());
      }
      return ''; // Leerstring zurückgeben
   }
   else
   { // Session existiert =>Daten aufbereiten & zurueckgeben
      $zeile = mysql_fetch_assoc($result);
      $sess_data = $zeile['Data'];
      $db_res = mysql_query("UPDATE cms_sessions
                           SET TimeTouched = $current_time
                           WHERE SessionID = '$sess_id';",
      $_db_connection);
      if (false === $db_res)
      {
         die ("Datenbankfehler: ".mysql_error());
      }
      return $sess_data;
   }
}
// Funktion zum Speichern der Daten
function sess_write($sess_id, $data)
{
   global $_db_connection;
   $current_time = time();
//var_dump($_db_connection);
   // Update ausfuehren => Daten in die Tabelle schreiben
   $db_res = mysql_query("UPDATE cms_sessions
                           SET Data = '$data',
                           TimeTouched = $current_time
                           WHERE SessionID = '$sess_id';", $_db_connection);
   if (false === $db_res)
   {
      die ("Datenbankfehler: ".mysql_error());
   }
   return true;
}
// Funktion zum Loeschen einer Session
function sess_destroy($sess_id)
{
   global $_db_connection;
   // Eintrag aus der Datenbank entfernen
   $db_res = mysql_query("DELETE FROM cms_sessions
                           WHERE SessionID = '$sess_id';",
                                 $_db_connection);
   if (false === $db_res)
   {
      die ("Datenbankfehler: ".mysql_error());
   }
   return true;
}
// Funktion fuer die Garbage Collection
function sess_gc($sess_maxlifetime)
{
   global $_db_connection;
   $current_time = time();
   $db_res = mysql_query("DELETE FROM cms_sessions
                           WHERE (TimeTouched + $sess_maxlifetime)
                                       < $current_time;");
   if (false === $db_res)
   {
      die ("Datenbankfehler: ".mysql_error());
   }
   return true;
}
session_set_save_handler("sess_open", "sess_close",
                         "sess_read", "sess_write",
                          "sess_destroy", "sess_gc");
//session_start();

class SESSION 
{ 
   public function __construct () 
   { 
   self :: commence (); 
   //echo 1;
   } 

   private function commence () 
   { 
    if ( !isset( $_SESSION ['ready'] ) ) 
     { 
      session_start (); 
      $_SESSION ['ready'] = TRUE; 
	  //echo 2;	Wird zuerst beim rausfliegen aufgerufen
     } 
   } 

   static public function set ( $fld , $val ) 
   { 
   self :: commence (); 
   $_SESSION [ $fld]  = $val; 
   //echo 3;
   } 
   static public function un_set ( $fld )  
   { 
   self :: commence (); 
   unset( $_SESSION [$fld] ); 
   //echo 4;
   } 
   static public function destroy () 
   { 
   self :: commence (); 
   unset ( $_SESSION ); 
   session_destroy (); 
   //echo 5;
   } 
   static public function get ( $fld ) 
   { 
   self :: commence (); 
   if (isset($_SESSION [$fld])) {
   		//echo 6;
		return $_SESSION [$fld]; 
   } else {
		//echo 7; Wird danach beim rausfliegen aufgerufen
		return NULL;
   }
   } 
   static public function is_set ( $fld ) { 
   self :: commence (); 
   //echo 8;
   return isset( $_SESSION [$fld] ); 
   } 
} 

?>