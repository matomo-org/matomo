<?

  /**
  * Config file for MLib
  * 
  * File includes all of the static variables that controls 
  * the default behavior of MLib.
  *
  * @author Joe Stump <joe@joestump.net>
  * @version 0.1
  * @see MLib
  * @name Config
  * @package tests
  * @access public
  */

  /**
  * MLib include path
  * 
  * @author Joe Stump <joe@joestump.net>
  * The absolute path to the directory where MLib resides.
  */
 
define('MLIB_INCLUDE_PATH','/home/jstump/public_html/v3/includes');

  /**
  * Default log file
  * 
  * The log file where you wish all class errors to be
written to. 
  *  Must be writable by the webserver.
  *
  * @author Joe Stump <joe@joestump.net>
  * @see MLIB_USE_SYSLOG
  */
  define('MLIB_LOG_FILE','/tmp/mlib.log');

  /**
  * Use syslog
  * 
  * If set to true MLib will send errors to syslog instead of
  * the file defined in MLIB_LOG_FILE 
  *
  * @author Joe Stump <joe@joestump.net>
  * @see MLIB_LOG_FILE
  */
  define('MLIB_USE_SYSLOG',false);

  /**
  * Syslog priority
  * 
  * The PHP function syslog takes a priority as a parameter.
If you
  * do not know what this means do NOT change this variable.
  *
  * @author Joe Stump <joe@joestump.net>
  * @see MLIB_USE_SYSLOG
  * @link http://www.php.net/manual/en/function.syslog.php
  */
  define('MLIB_SYSLOG_PRIORITY',LOG_WARNING);

  /** 
  * Template path
  *
  * MLib comes with a template class that lets you separate your
  * code from your HTML. This is the path where the template
files
  * reside.
  *
  * @author Joe Stump <joe@joestump.net>
  * @see Template, MLIB_INCLUDE_PATH
  */
  define('MLIB_TEMPLATE_PATH',MLIB_INCLUDE_PATH.'/templates');

  /** 
  * Global debugging
  *
  * By turning on global debugging you enable debugging in ALL
  * classes derived from MLib on ALL pages. BE CAREFUL SETTING
  * THIS TO TRUE!!
  *
  * @author Joe Stump <joe@joestump.net>
  * @see MLib, MLIB::$debug, MLib::MLib()
  */
  define('MLIB_GLOBAL_DEBUG',false);

  /**
  * Global DSN to be used by classes
  *
  * The DSN to be used. Please see the PEAR documentation
for more
  * information.
  *
  * @global array $_MLIB_GLOBAL_DSN 
  * @author Joe Stump <joe@joestump.net>
  * @see TemplateDB
  * @link http://pear.php.net
  */
  $_MLIB_GLOBAL_DSN = array('db_type'=> 'mysql',
                            'username' => 'nobody',
                            'password' => '',
                            'database' => 'miester',
                            'server' => 'localhost');

  /**
  * MLib include file
  * @see MLib
  */
  require_once(MLIB_INCLUDE_PATH.'/MLib.php');

  /**
  * Debug include file
  * @see Debug
  */
  require_once(MLIB_INCLUDE_PATH.'/Debug.php');

  /**
  * DSN include file
  * @see DSN
  */
  require_once(MLIB_INCLUDE_PATH.'/DSN.php');

  /**
  * Table include file
  * @see Table
  */
  require_once(MLIB_INCLUDE_PATH.'/Table.php');

  /**
  * Template include file
  * @see Template
  */
  require_once(MLIB_INCLUDE_PATH.'/Template.php');

  /**
  * TemplateFile include file
  * @see TemplateFile
  */
  require_once(MLIB_INCLUDE_PATH.'/Template/TemplateFile.php');

  /**
  * TemplateDB include file
  * @see TemplateDB
  */
  require_once(MLIB_INCLUDE_PATH.'/Template/TemplateDB.php');

  /**
  * TemplateVar include file
  * @see TemplateVar
  */
  require_once(MLIB_INCLUDE_PATH.'/Template/TemplateVar.php');


?>
