<?php


class datenbank
{
   protected $conn;

	protected $host;
	protected $user;
	protected $pass;

   protected $erg;
   protected $last_result;

	function __construct ($host,$user,$passwort, $db=NULL) {
		$this->host = $host;
		$this->user = $user;
		$this->pass = $passwort;

		if (!($db===NULL)) {
			$this->verbinden();
			$this->select_db($db);
		}
	}

	function __destruct () {
		@session_write_close();
		mysql_close($this->conn);
	}
  
	public function get_conn () {
		return $this->conn;
	}

  public function verbinden()
  {
	
    if(!($this->conn = mysql_connect($this->host, $this->user, $this->pass)))
    {
      echo "Die Verbindung zur Datenbank konnte nicht hergestellt werden.";
    } 
    return $this->conn;
  }
  
  function select_db($db)
  {
	
    if (false === mysql_select_db($db, $this->conn))
    {
      echo "Beim Ausw&auml;hlen der Datenbank ist ein Fehler aufgetreten.";
    }
  }
  
  function sql($sql)
  {
	$this->last_result = array();
	
    $sqlBefehl = "";
    $ArrayMuster = array('SELECT', 'INSERT', 'UPDATE', 'DELETE');
	while(list($key, $val) = each($ArrayMuster))
	{
	  $muster = "*" . $val . "*";
	  if (true == preg_match($muster, $sql)) {
		$sqlBefehl = $val;
	  }
	}

    if (!$this->erg = mysql_query($sql, $this->conn))
    {
	  switch($sqlBefehl) {
	    case 'SELECT': 
        		echo "Zur Zeit k&ouml;nnen keine Eintr&auml;ge abgerufen werden.<br>\n";
				break;
		case 'INSERT': 
        		echo "Zur Zeit k&ouml;nnen keine Eintr&auml;ge eingetragen werden.<br>\n";
				break;
		case 'UPDATE': 
        		echo "Zur Zeit k&ouml;nnen keine Eintr&auml;ge aktualisiert werden.<br>\n";
				break;
		case 'DELETE': 
        		echo "Zur Zeit k&ouml;nnen keine Eintr&auml;ge gel&ouml;scht werden.<br>\n";
				break;
	  }
    } else 
	{
	  switch($sqlBefehl) {
	    case 'SELECT': 
				$num_rows = 0;
	  			while ( $row = mysql_fetch_array($this->erg) ) {
	    		  $this->last_result[$num_rows] = $row;
				  $num_rows++;
	  			}
				break;
		case 'INSERT': 
				$this->last_result = @ mysql_affected_rows();
				break;
		case 'UPDATE': 
				$this->last_result = @ mysql_affected_rows();
				break;
		case 'DELETE': 
				$this->last_result = @ mysql_affected_rows();
				break;
	  }
	}

    return $this->last_result;
  }

	public function last_id () {
		return mysql_insert_id();
	}
	
	public function info () {
		return mysql_info();	
	}
} // class datenbank

?>