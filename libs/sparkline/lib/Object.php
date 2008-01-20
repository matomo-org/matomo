<?php
/*
 * Sparkline PHP Graphing Library
 * Copyright 2004 James Byers <jbyers@users.sf.net>
 * http://sparkline.org
 *
 * Sparkline is distributed under a BSD License.  See LICENSE for details.
 *
 * $Id: Object.php,v 1.8 2005/06/02 21:01:42 jbyers Exp $
 *
 */

define('DEBUG_NONE',     0); // nothing
define('DEBUG_ERROR',    1); // major errors
define('DEBUG_WARNING',  2); // warnings
define('DEBUG_STATS',    4); // dataset, rendering statistics
define('DEBUG_CALLS',    8); // major function calls
define('DEBUG_SET',     16); // all Set methods
define('DEBUG_DRAW',    32); // all Draw methods
define('DEBUG_ALL',   2047); // everything

function error_handler($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
  case E_ERROR:
    $message = "ERROR:    ";
    break;
  case E_WARNING:
    $message = "WARNING:  ";
    break;
  case E_PARSE:
    $message = "PARSE:    ";
    break;
  case E_NOTICE:
    $message = "NOTICE:   ";		
    break;
  case E_USER_ERROR:
    $message = "UERROR:   ";
    break;
  case E_USER_WARNING:
    $message = "UWARNING: ";
    break;
  case E_USER_NOTICE:
    $message = "UNOTICE:  ";		
    break;
  default:
    $message = "UNKNOWN:  ";
    break;
  } // switch
  
  $message .= "$errstr in $errfile at line $errline\n";
  
  if (($errno != E_NOTICE) &&     // suppress notices
      (error_reporting() != 0)) { // respect supressed errors (@)
    log_write($message, 'PHP');
  }
} // function error_handler

function log_write($string, $type = '', $date = false) {
  global $LOGFILE;

  if (isset($LOGFILE)) {
    if ($date == false) {
      $date = time();
    }
    
    $message = date('d/m/Y:H:i:s', $date) . " $type: $string \n";
    error_log($message, 3, $LOGFILE);
  }
} // function log_write

class Object {

  var $isError;
  var $logFile;
  var $errorList;
  var $debugList;
  var $debugLevel;
  var $startTime;

  ////////////////////////////////////////////////////////////////////////////
  // constructor
  //
  function Object($catch_errors = true) {
    $this->isError         = false;
    $this->logFile         = null;
    $this->logDate         = '';
    $this->errorList       = array();
    $this->debugList       = array();
    $this->debugLevel      = DEBUG_NONE;
    $this->startTime       = $this->microTimer();

    //    if ($catch_errors) {
      set_error_handler('error_handler');
      //}
  } // function Object

  ////////////////////////////////////////////////////////////////////////////
  // utility
  //
  function microTimer() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec); 
  } // function microTimer

  ////////////////////////////////////////////////////////////////////////////
  // error handling
  //
  function SetDebugLevel($level, $file = null) {
    global $LOGFILE;

    if ($level >= DEBUG_NONE &&
        $level <= DEBUG_ALL) {
      $this->debugLevel = $level;
    }

    if ($file != null) {
      if ((!file_exists($file) && !touch($file)) ||
          !is_writable($file)) {
        die("error log file '$file' is not writable to the web server user");
      } else {
        $this->logFile = $file;
        $LOGFILE       = $file;
      }
    }
  } // function SetDebugLevel
  
  function Debug($string, $level = DEBUG_WARNING) {
    $this->debugList[] = $string;
    if ($this->debugLevel & $level &&
        $this->logFile != null) {
      log_write($string, 'DEBUG');
    }
  } // function Debug

  function Error($string) {
    $this->isError = true;
    $this->errorList[] = $string;
    if ($this->debugLevel & DEBUG_ERROR &&
        $this->logFile != null) {
      log_write($string, 'ERROR');
    }
  } // function Error

  function GetDebug() {
    return $this->debugList;
  } // function GetDebug

  function GetError() {
    return $this->errorList;
  } // function GetError

  function IsError() {
    return $this->isError;
  } // function IsError

} // class Object

?>
