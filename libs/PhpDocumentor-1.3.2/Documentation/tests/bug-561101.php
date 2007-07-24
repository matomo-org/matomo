<?php
// $Id: bug-561101.php,v 1.1 2005/10/17 18:36:51 jeichorn Exp $

/**
* Class ClubBase
*
* Basisklasse, die Debugging- und ErrorHandling/Logging-Funktionen liefert
* Alle komplexeren Klassen sollten von ClubBase abgeleitet werden.
*
* @link      http://www.swr3clubde
* @package   tests SWR3.online-Edition
* @version   $Revision: 1.1 $ ($Date: 2005/10/17 18:36:51 $),
* @copyright Copyright (c) 2001 SWR3.online. All rights reserved.
* @author    Karsten Kraus <kk@swr3.de>
* @access    private
*/

///////////////////////////// REQUIRES //////////////////////////////////////////////////////////////
/**
* @var const 	CLUB_CLASS_PATH		Der Pfad zu den Club-Klassen
*/
if(!defined('CLUB_CLASS_PATH')) @define('CLUB_CLASS_PATH', '/wwwhome/swr3club.de/php.inc/swr3club/');
/**
* In dieser Datei werden Pfade zu den verschiedenen Spezial-Klassen
* (storages, products, commands) definiert
* include		Function _require_once_
*/
@require_once(CLUB_CLASS_PATH . 'config/paths.conf.php');
/**
* In dieser Datei werden Konstanten, die fuer alle Klassen gelten definiert
* include		Function _require_once_
*/
@require_once(CLUB_CONFIG_PATH . 'common.conf.php');
/**
* ClubBase erbt von PEAR.php, deshalb binden wir sie hier ein
* include		Function _require_once_
*/
@require_once(CLUB_PEAR_PATH . 'PEAR.php');
/**
* Für das Logging verwenden wir erstmal die Standard PEAR::Log-Klasse
*/
@require_once(CLUB_PEAR_PATH . 'Log.php');
////////////////////////////////////////////////////////////////////////////////////////////////////

/**
* _ClubDebugOptions	globale Debugging-Einstellungen fuer alle Klassen
*
* Sichert die Werte, wenn ClubBase::setdebug() aufgerufen wird.
* Die hier gesetzten Werte gelten fuer ALLE Klassen, die keine eigenen
* Werte uerber $class->setdebug() gesetzt haben
*
* @access	private
* @var 		array	$ClubDebugOptions	array('level' => 0, 'hide' => true, 'file' = '')
* @see		ClubBase::set_debug, ClubBase::_DEBUG()
*/
$_ClubDebugOptions = array('level' => null, 'hide' => true, 'file' => '', 'print' => true, 'flush' => false);

/**
* DEBUG_XXX	Konstanten, die bestimmte Debuglevel definieren
*
* Diese Konstanten dienen einfach dazu, den Quellkode lesbarer zu machen.
* Ob das Debuglevel als Zahl angeben wird, oder mit einer Konstante, ist
* letztlich egal.
* Die Konstanten decken immer Bereiche ab (z.B.  DEBUG_INFO = 6, DEBUG_NOTICE = 9)
define('DEBUG_ERROR', 1);
*/
@define('DEBUG_ERROR', 3);

/**
* @const	DEBUG_INFO		wichtige Zusatz-Informationen
*/
@define('DEBUG_INFO', 5);
/**
* @const	DEBUG_NOTICE	Details, die nur selten angezeigt werden muessen
*/
define('DEBUG_NOTICE', 6);

@define('DEBUG_INTERNALS', 7);

/**
* @const	DEBUG_SQL		alle Ausgaben, die (lange) SQL-Statements ausgeben
*/
@define('DEBUG_SQL', 8);
/**
* @const 	DEBUG_CTRACE	 zeigt Aufrufe (Init der Klassen) an
*/
@define('DEBUG_CTRACE', 9);
/**
* @const	DEBUG_MTRACE	zeigt Aufrufe von Methoden/Funktionen an
*/
@define('DEBUG_MTRACE', 10);
/**
* @const	PHPDOCUMENTOR_DEBUG_ALL	Alles anzeigen
*/
define('DEBUG_ALL', 11);

// Schwerer Fehler
define('DEBUG_FATAL', 0);


// ----------------------------------------------------------------------------------------------------

class ClubBase extends PEAR {

    /**
    * $_bDebugLevel	Debug-Level
    *
    * Diese Variablen speichern die Debugging-Einstellungen
    * innerhalb des Objekts.
    * Sie ueberschreiben ggf. die Werte in _ClubDebugOptions
    *
    * @access	private
    * @var	integer	$_bDebugLevel
    * @final
    * @see	$_ClubDebugOptions, setdebug(), _PHPDOCUMENTOR_DEBUG()
    */
    var $_bDebugLevel = -1;

    /**
    * $__bHideDebug	Debugausgabe in HTMl-Kommentare setzen?
    *
    * @brother	_bDebugLevel
    * @access	private
    * @var		boolean	$_bHideDebug
    */
    var $_bHideDebug = true;

    /**
    * $_sLogFile	Logfile, in der Debugmeldungen geschrieben werden
    *
    * @brother	_bDebugLevel
    * @access	private
    * @var		string	$_sLogFile
    */
    var $_sLogFile = '';

    /**
    * $_bPrintDebug	Sollen Meldungen auch in HTM geschrieben werden, wenn sie in Datei geloggt werden?
    *
    * @brother	_bDebugLevel
    * @access	private
    * @var		boolean	$_bPrintDebug
    */
    var $_bPrintDebug = true;

    var $_bPrintFlush = false;


    /**
    * ClubBase()    Der Konstruktor
	*
	* Macht nicht viel mehr, als fuer alle abgeleiteten Klassen
	* eine Debug-Meldung abzusetzten und PEAR-Funktionen einzubinden
	*
	*
    * @access    public
    */
    function ClubBase() {
        $this->_PHPDOCUMENTOR_DEBUG("Initialisiere Klasse " . phpDocumentor_get_class($this), PHPDOCUMENTOR_DEBUG_CTRACE);
	$this->PEAR();
    }

    /**
    * getProperty()	Gibt den Wert einer Eigenschaft zurueck
    *
    * Es werden nur 'oeffentliche' Eigenschaften (ohne '_' davor) zurueckgegeben/bearbeitet
    * Wird ein optionales 'Target' angegeben wird versucht den Werte eines
    * Hashes auszulesen (z.B. $class->$target[$property]) bzw. zu schreiben
    *
    * @access       public
    * @param        string	$property		Name der Eigenschaft/Schluessel
    * @param		string	$target			Name des Targets
    * @return       mixed					Wert der Eigenschaft/Schluessels
	* @see	getProperty(), getAllProperties(), setProperty(), getPropType()
    */
    function getProperty($property, $target = '', $index = 0, $simplify = false) {
	$allvars = get_object_vars($this);
	$prop = (empty($target)) ? $property : $target;
	$key = (empty($target)) ? null : $property;
	if(!in_array($prop, @array_keys($allvars))) {
	    // Fehlerbehandlung
	    return $this->_ERROR("Eigenschaft $prop gibt's net");
	}
	if(eregi('^_', $prop) or (isset($key) and eregi('^_', $key))) {
	    // Fehlerbehandlung
    	    $this->_ERROR("Auf private Variablen darf nicht zugegriffen werden");
	}
	if(isset($key) and !in_array($key, array_keys($allvars[$prop]), true)) {
	    if(isset($allvars[$prop][$index])) {
		if(in_array($key, @array_keys($allvars[$prop][$index]))) {
		    return $allvars[$prop][$index][$key];
		}
	    }
	    // Fehlerbehandlung
	    return $this->_ERROR("Eigenschaft $prop hat den Schluessel $key nicht");
	}
	$val = (isset($key)) ? $allvars[$prop][$key] : $allvars[$prop];
	if($simplify) {
	    if(isset($val[0]) and count($val) < 2) $val = $val[0];
	}
	return $val;
    }

    /**
    * getAllProperties()	Gibt die Wert aller Eigenschaften einer Klasse zurueck
    *
    * Es werden nur 'oeffentliche' Eigenschaften (ohne '_' davor) zurueckgegeben/bearbeitet
    * Wird ein optionales 'Target' angegeben wird versucht den Werte eines
    * Hashes auszulesen (z.B. $class->$target[$property]) bzw. zu schreiben
    *
    * @access		public
    * @param		array	$exclude	diese Eigenschaften werden ignoriert
    * @return       array				Array mit den Namen der Eigenschaften als Schluesseln
	* @see	getProperty(), getAllProperties(), setProperty(), getPropType()
    */
    function getAllProperties($simplify = false, $exclude = array()) {
	if(isset($exclude) and !is_array($exclude)) $exclude = array($exclude);
	$allvars = get_object_vars($this);
	$props = array();
	    while(list($key, $val) = each($allvars)) {
		if(!isset($key) or eregi('^_', $key) or in_array($key, $exclude)) continue;
		if(is_array($val)) {
		if($simplify) {
		    if(isset($val[0]) and count($val) < 2) $val = $val[0];
		}
	    }
	    $props[$key] = $val;
	}
	return $props;
    }

    /**
    * setProperty()	Setzt den Wert einer Eigenschaft
    *
    * @brother		getProperty()
	* @access		public
    * @param		mixed	$value	Wert der der Eigenschaft zugewiesen werden soll
    * @return       mixed			true im Erfolgsfall, sonst Fehler
    */
    function setProperty($property, $value, $target = '', $index = 0, $force = false) {
	$oldval = $this->getProperty($property, $target, $index);
	// Fehlerbehandlung
	if(isset($oldval) and (gettype($value) != gettype($oldval)) and !$force) {
	// Fehlerbehandlung
	    return $this->_ERROR("Falscher Typ (" . gettype($value) . ") fuer '$property' (" . gettype($oldval) . ")!");
	} else {
	if(!empty($target)) {
	    $temp = &$this->$target;
	    if(isset($temp[$property]) or $force) {
	        $temp[$property] = $value;
	    } elseif((isset($temp[$index]) and isset($temp[$index][$property]))) {
		    $temp[$index][$property] = $value;
	    } else {
		return $this->_ERROR("Eigenschaft $property gibt es nicht");
	    }
	} else {
	    $this->$property = $value;
	}
	    return true;
	}
    }

	/**
	* getPropType	gibt den Datentyp eines Objekt-Eigenschaft zurueck.
	*
	* @access	public
	* @brother	getProperty()
	*
	* @return	string	Datentyp der Eigenschaft
	*/
    function getPropType($property, $target = null, $index = 0) {
	$val = $this->getProperty($property, $target, $index);
	// Fehlerbehandlung
	return gettype($val);
    }


    /**
    * set_debug()   Debug-Level fuer die Klasse setzen
	*
	* Wird diese Methode statisch (ClubBase::set_debug()) aufgerufen, dann wird die globale Variable
	* $_ClubDebugOptions gesetzt.
	* Diese gilt fuer ALLE KLASSEN, die keine eigenen Werte mit $obj->set_debug() gestzt haben.
	*
    * @access    public
	* @static
	*
    * @param     integer    $level	Level, bis zu dem Debugausgaben angezeigt werden
	* @param	 boolean	$hide	Sollen Debugausgaben im HTML in Kommentare gepackt werden?
	* @param	 string		$file	Soll in eine Datei geschrieben werden (null = nein)
	* @param	 boolean	$print  Sollen Kommentare in HTML geschrieben werden,
	*								auch wenn schon in Datei geloggt wird?
	*
	* @global	 array		Globale Debugging-Einstellungen
	* @see		 $_bDebugLevel, $_bHideDebug, $_sLogFile, $_bPrintDebug
	* @see		 $_ClubDebugOptions, ClubBase::set_debug(), ClubBase::_PHPDOCUMENTOR_DEBUG()
    */
    function setDebug($level, $hide = true, $file = '', $print = true, $flush = false) {
	if(!empty($file)) $file = (is_string($file)) ? $file : CLUB_LOG_FILE;
	if(!isset($this)) {
	    global $_ClubDebugOptions;
	    $_ClubDebugOptions = array(
		'level' => (integer)$level,
		'hide' => (boolean)$hide,
		'file' => (string)$file,
		'print' => (boolean)$print,
                'flush' => (boolean)$flush
	    );
	    return;
	}
	if($this->_bDebugLevel < 0) $this->_bDebugLevel = 0;
        $this->_bDebugLevel = empty($level) ? !(boolean)$this->_bDebugLevel : (integer)$level;
	$this->_bHideDebug = (boolean)$hide;
	$this->_sLogFile = (string)$file;
	$this->_bPrintDebug = (boolean)$print;
        $this->_bPrintFlush = (boolean)$flush;	
    }

    function printFlush() {
        $args = func_get_args();
        if(empty($args)) $args = array(' ');
        eval("printf(\"% 256s\", sprintf(\"" . implode("\", \"", $args) . "\"));");
        flush();
    }
    
    function printVar($var, $info = '') {
	if (ClubBase::isError(ClubBase::loadClass('var_dump', CLUB_CLASS_PATH))) return false;	
	if (!empty($info)) print "<H2>$info</H2>";
	Var_Dump::display($var);
        return true;
    }

    function loadClass($classname, $path = './', $ext = '.class.inc', $require = false) {
	ClubBase::_PHPDOCUMENTOR_DEBUG("ClubBase::loadclass($path, $classname, $ext)", PHPDOCUMENTOR_DEBUG_MTRACE);
	if(empty($classname) or empty($ext))  {
	    return ClubBase::_ERROR("Fehlende Daten: (Pfad: $path, Klasse: $classname, Ext.: $ext)");
	}
	if(!in_array(strtolower($classname), get_declared_classes())) {
	    ClubBase::_PHPDOCUMENTOR_DEBUG("Klasse '$classname' noch nicht deklariert, lade neu...", PHPDOCUMENTOR_DEBUG_NOTICE);
	    $classfile = $path . $classname . $ext;
	    if(!file_exists($classfile) or !is_readable($classfile)) {
	        //Fehlerbehandlung
		return ClubBase::_ERROR("Kann Klasse '$classfile' nicht laden");
	    } elseif($require) {
		if(!@require_once($classfile)) {
		    //Fehlerbehandlung
		    return ClubBase::_ERROR('Kann Command nicht includieren');
		}
	    } elseif(!@include_once($classfile)) {
		//Fehlerbehandlung
		ClubBase::_ERROR("Kann Klasse '$classfile' nicht includieren");
	    }
	} else {
	    ClubBase::_PHPDOCUMENTOR_DEBUG('Klasse schon deklariert.', PHPDOCUMENTOR_DEBUG_INTERNALS);
	}
	return true;
    }

    /**
    * _PHPDOCUMENTOR_DEBUG()   Debugmeldungen ausgeben
    *
    * Mit dieser Funktion koennen alle Klassen einheitlich Debug-Meldungnen ausgeben.
	*
    * @access    public
    * @static
	*
	 @todo		 die() bei Level -999 rausnehmen, wenn Error-Handling implmentiert
	*
    * @param     string		$message	Meldungstext
	* @param	 integer	$level		das Debuglevel der Meldung
	* @global	 array		Globale Debugging-Einstellungen
	* @see		 $_bDebugLevel, $_bHideDebug, $_sLogFile, $_bPrintDebug
	* @see		 $_ClubDebugOptions, ClubBase::set_debug(), ClubBase::_PHPDOCUMENTOR_DEBUG()
    */
    function _PHPDOCUMENTOR_DEBUG($message, $level = PHPDOCUMENTOR_DEBUG_INFO) {
	static $called = 0; ++$called;
	global $_ClubDebugOptions;
	$blevel = 0; $hide = true; $file = null; $print = false;
	if(isset($_ClubDebugOptions['level'])) {
		$blevel = $_ClubDebugOptions['level'];
		$hide = $_ClubDebugOptions['hide'];
		$file = $_ClubDebugOptions['file'];
		$print = $_ClubDebugOptions['print'];
                $flush = $_ClubDebugOptions['flush'];
		
	}
       	if(isset($this) and isset($this->_bDebugLevel) and $this->_bDebugLevel >= 0) {
			$blevel = $this->_bDebugLevel;
			$hide = $this->_bHideDebug;
			$file = $this->_sLogFile;
			$print = $this->_bPrintDebug;
                        $flush = $this->_bPrintFlush;			
	}
	$class = (isset($this)) ? phpDocumentor_get_class($this) ."($called) -" : '';
	if($level <= $blevel) {
	    $output = "$class $message ($level/$blevel)";
	    if($print) {
                $output = ($hide) ? "<!-- " . $output : "<HR size='1' noshade><PRE><B>" . $output;
		$output .= ($hide) ? " -->\n" : "</PRE><HR size='1' noshade><B>\n";
                ($flush) ? ClubBase::printFlush($output) : print $output;
	    }
	    if(!empty($file)) {
		$file = (!is_string($file)) ? CLUB_LOG_FILE : $file;
		$log = &Log::singleton('file', $file);
		$log->log($output, $level);
	    }
	}
	if($level === PHPDOCUMENTOR_DEBUG_FATAL) {
	    trigger_error($message, E_USER_ERROR);
	}
    }

    /**
    * _ERROR()   Fehler registrieren und ggf. zur Debug-Ausgabe weiterleiten
	*
	* Dies muss erst noch implementiert werden ;-))
	 @todo		implement this
	*
    * @access    public
    * @final
    * @static
    */
    function _ERROR ($message = 'unknown error', $code = PHPDOCUMENTOR_DEBUG_ERROR,
                     $mode = null, $options = null, $userinfo = null, $error_class = null)
    {
	$msg = ClubBase::isError($message) ? $message->getMessage() : $message;
        (isset($this)) ? $this->_PHPDOCUMENTOR_DEBUG($msg, PHPDOCUMENTOR_DEBUG_ERROR) : ClubBase::_PHPDOCUMENTOR_DEBUG($msg, $code);
        if(!isset($this) and empty($error_class)) $error_class = 'PEAR_Error';
        return (isset($this))
                    ? $this->raiseError($message, $code, $mode, $options, $userinfo)
                    : ClubBase::raiseError($message, $code, $mode, $options, $userinfo, $error_class);
    }
}


?>