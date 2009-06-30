<?php
/**
 * *** BEGIN LICENSE BLOCK *****
 *  
 * This file is part of FirePHP (http://www.firephp.org/).
 * 
 * Software License Agreement (New BSD License)
 * 
 * Copyright (c) 2006-2009, Christoph Dorn
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of Christoph Dorn nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * ***** END LICENSE BLOCK *****
 * 
 * @copyright   Copyright (C) 2007-2009 Christoph Dorn
 * @author      Christoph Dorn <christoph@christophdorn.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     FirePHP
 */
 
 
/**
 * Sends the given data to the FirePHP Firefox Extension.
 * The data can be displayed in the Firebug Console or in the
 * "Server" request tab.
 * 
 * For more information see: http://www.firephp.org/
 * 
 * @copyright   Copyright (C) 2007-2009 Christoph Dorn
 * @author      Christoph Dorn <christoph@christophdorn.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     FirePHP
 */
class FirePHP {
  
  /**
   * FirePHP version
   *
   * @var string
   */
  const VERSION = '0.3';
  
  /**
   * Firebug LOG level
   *
   * Logs a message to firebug console.
   * 
   * @var string
   */
  const LOG = 'LOG';
  
  /**
   * Firebug INFO level
   *
   * Logs a message to firebug console and displays an info icon before the message.
   * 
   * @var string
   */
  const INFO = 'INFO';
  
  /**
   * Firebug WARN level
   *
   * Logs a message to firebug console, displays an warning icon before the message and colors the line turquoise.
   * 
   * @var string
   */
  const WARN = 'WARN';
  
  /**
   * Firebug ERROR level
   *
   * Logs a message to firebug console, displays an error icon before the message and colors the line yellow. Also increments the firebug error count.
   * 
   * @var string
   */
  const ERROR = 'ERROR';
  
  /**
   * Dumps a variable to firebug's server panel
   *
   * @var string
   */
  const DUMP = 'DUMP';
  
  /**
   * Displays a stack trace in firebug console
   *
   * @var string
   */
  const TRACE = 'TRACE';
  
  /**
   * Displays an exception in firebug console
   * 
   * Increments the firebug error count.
   *
   * @var string
   */
  const EXCEPTION = 'EXCEPTION';
  
  /**
   * Displays an table in firebug console
   *
   * @var string
   */
  const TABLE = 'TABLE';
  
  /**
   * Starts a group in firebug console
   * 
   * @var string
   */
  const GROUP_START = 'GROUP_START';
  
  /**
   * Ends a group in firebug console
   * 
   * @var string
   */
  const GROUP_END = 'GROUP_END';
  
  /**
   * Singleton instance of FirePHP
   *
   * @var FirePHP
   */
  protected static $instance = null;
  
  /**
   * Flag whether we are logging from within the exception handler
   * 
   * @var boolean
   */
  protected $inExceptionHandler = false;
  
  /**
   * Flag whether to throw PHP errors that have been converted to ErrorExceptions
   * 
   * @var boolean
   */
  protected $throwErrorExceptions = true;
  
  /**
   * Flag whether to convert PHP assertion errors to Exceptions
   * 
   * @var boolean
   */
  protected $convertAssertionErrorsToExceptions = true;
  
  /**
   * Flag whether to throw PHP assertion errors that have been converted to Exceptions
   * 
   * @var boolean
   */
  protected $throwAssertionExceptions = false;
  
  /**
   * Wildfire protocol message index
   *
   * @var int
   */
  protected $messageIndex = 1;
    
  /**
   * Options for the library
   * 
   * @var array
   */
  protected $options = array('maxObjectDepth' => 10,
                             'maxArrayDepth' => 20,
                             'useNativeJsonEncode' => true,
                             'includeLineNumbers' => true);

  /**
   * Filters used to exclude object members when encoding
   * 
   * @var array
   */
  protected $objectFilters = array();
  
  /**
   * A stack of objects used to detect recursion during object encoding
   * 
   * @var object
   */
  protected $objectStack = array();
  
  /**
   * Flag to enable/disable logging
   * 
   * @var boolean
   */
  protected $enabled = true;

  /**
   * The object constructor
   */
  function __construct() {
  }

  /**
   * When the object gets serialized only include specific object members.
   * 
   * @return array
   */  
  public function __sleep() {
    return array('options','objectFilters','enabled');
  }
    
  /**
   * Gets singleton instance of FirePHP
   *
   * @param boolean $AutoCreate
   * @return FirePHP
   */
  public static function getInstance($AutoCreate=false) {
    if($AutoCreate===true && !self::$instance) {
      self::init();
    }
    return self::$instance;
  }
   
  /**
   * Creates FirePHP object and stores it for singleton access
   *
   * @return FirePHP
   */
  public static function init() {
    return self::$instance = new self();
  }
  
  /**
   * Enable and disable logging to Firebug
   * 
   * @param boolean $Enabled TRUE to enable, FALSE to disable
   * @return void
   */
  public function setEnabled($Enabled) {
    $this->enabled = $Enabled;
  }
  
  /**
   * Check if logging is enabled
   * 
   * @return boolean TRUE if enabled
   */
  public function getEnabled() {
    return $this->enabled;
  }
  
  /**
   * Specify a filter to be used when encoding an object
   * 
   * Filters are used to exclude object members.
   * 
   * @param string $Class The class name of the object
   * @param array $Filter An array of members to exclude
   * @return void
   */
  public function setObjectFilter($Class, $Filter) {
    $this->objectFilters[strtolower($Class)] = $Filter;
  }
  
  /**
   * Set some options for the library
   * 
   * Options:
   *  - maxObjectDepth: The maximum depth to traverse objects (default: 10)
   *  - maxArrayDepth: The maximum depth to traverse arrays (default: 20)
   *  - useNativeJsonEncode: If true will use json_encode() (default: true)
   *  - includeLineNumbers: If true will include line numbers and filenames (default: true)
   * 
   * @param array $Options The options to be set
   * @return void
   */
  public function setOptions($Options) {
    $this->options = array_merge($this->options,$Options);
  }
  
  /**
   * Get options from the library
   *
   * @return array The currently set options
   */
  public function getOptions() {
    return $this->options;
  }
  
  /**
   * Register FirePHP as your error handler
   * 
   * Will throw exceptions for each php error.
   * 
   * @return mixed Returns a string containing the previously defined error handler (if any)
   */
  public function registerErrorHandler($throwErrorExceptions=true)
  {
    //NOTE: The following errors will not be caught by this error handler:
    //      E_ERROR, E_PARSE, E_CORE_ERROR,
    //      E_CORE_WARNING, E_COMPILE_ERROR,
    //      E_COMPILE_WARNING, E_STRICT
    
    $this->throwErrorExceptions = $throwErrorExceptions;
    
    return set_error_handler(array($this,'errorHandler'));     
  }

  /**
   * FirePHP's error handler
   * 
   * Throws exception for each php error that will occur.
   *
   * @param int $errno
   * @param string $errstr
   * @param string $errfile
   * @param int $errline
   * @param array $errcontext
   */
  public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
  {
    // Don't throw exception if error reporting is switched off
    if (error_reporting() == 0) {
      return;
    }
    // Only throw exceptions for errors we are asking for
    if (error_reporting() & $errno) {

      $exception = new ErrorException($errstr, 0, $errno, $errfile, $errline);
      if($this->throwErrorExceptions) {
        throw $exception;
      } else {
        $this->fb($exception);
      }
    }
  }
  
  /**
   * Register FirePHP as your exception handler
   * 
   * @return mixed Returns the name of the previously defined exception handler,
   *               or NULL on error.
   *               If no previous handler was defined, NULL is also returned.
   */
  public function registerExceptionHandler()
  {
    return set_exception_handler(array($this,'exceptionHandler'));     
  }
  
  /**
   * FirePHP's exception handler
   * 
   * Logs all exceptions to your firebug console and then stops the script.
   *
   * @param Exception $Exception
   * @throws Exception
   */
  function exceptionHandler($Exception) {
    
    $this->inExceptionHandler = true;

    header('HTTP/1.1 500 Internal Server Error');

    $this->fb($Exception);
    
    $this->inExceptionHandler = false;
  }
  
  /**
   * Register FirePHP driver as your assert callback
   * 
   * @param boolean $convertAssertionErrorsToExceptions
   * @param boolean $throwAssertionExceptions
   * @return mixed Returns the original setting or FALSE on errors
   */
  public function registerAssertionHandler($convertAssertionErrorsToExceptions=true, $throwAssertionExceptions=false)
  {
    $this->convertAssertionErrorsToExceptions = $convertAssertionErrorsToExceptions;
    $this->throwAssertionExceptions = $throwAssertionExceptions;
    
    if($throwAssertionExceptions && !$convertAssertionErrorsToExceptions) {
      throw $this->newException('Cannot throw assertion exceptions as assertion errors are not being converted to exceptions!');
    }
    
    return assert_options(ASSERT_CALLBACK, array($this, 'assertionHandler'));
  }
  
  /**
   * FirePHP's assertion handler
   *
   * Logs all assertions to your firebug console and then stops the script.
   *
   * @param string $file File source of assertion
   * @param int    $line Line source of assertion
   * @param mixed  $code Assertion code
   */
  public function assertionHandler($file, $line, $code)
  {

    if($this->convertAssertionErrorsToExceptions) {
      
      $exception = new ErrorException('Assertion Failed - Code[ '.$code.' ]', 0, null, $file, $line);

      if($this->throwAssertionExceptions) {
        throw $exception;
      } else {
        $this->fb($exception);
      }
    
    } else {
    
      $this->fb($code, 'Assertion Failed', FirePHP::ERROR, array('File'=>$file,'Line'=>$line));
    
    }
  }  
  
  /**
   * Set custom processor url for FirePHP
   *
   * @param string $URL
   */    
  public function setProcessorUrl($URL)
  {
    $this->setHeader('X-FirePHP-ProcessorURL', $URL);
  }

  /**
   * Set custom renderer url for FirePHP
   *
   * @param string $URL
   */
  public function setRendererUrl($URL)
  {
    $this->setHeader('X-FirePHP-RendererURL', $URL);
  }
  
  /**
   * Start a group for following messages.
   * 
   * Options:
   *   Collapsed: [true|false]
   *   Color:     [#RRGGBB|ColorName]
   *
   * @param string $Name
   * @param array $Options OPTIONAL Instructions on how to log the group
   * @return true
   * @throws Exception
   */
  public function group($Name, $Options=null) {
    
    if(!$Name) {
      throw $this->newException('You must specify a label for the group!');
    }
    
    if($Options) {
      if(!is_array($Options)) {
        throw $this->newException('Options must be defined as an array!');
      }
      if(array_key_exists('Collapsed', $Options)) {
        $Options['Collapsed'] = ($Options['Collapsed'])?'true':'false';
      }
    }
    
    return $this->fb(null, $Name, FirePHP::GROUP_START, $Options);
  }
  
  /**
   * Ends a group you have started before
   *
   * @return true
   * @throws Exception
   */
  public function groupEnd() {
    return $this->fb(null, null, FirePHP::GROUP_END);
  }

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::LOG
   * @param mixes $Object
   * @param string $Label
   * @return true
   * @throws Exception
   */
  public function log($Object, $Label=null) {
    return $this->fb($Object, $Label, FirePHP::LOG);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::INFO
   * @param mixes $Object
   * @param string $Label
   * @return true
   * @throws Exception
   */
  public function info($Object, $Label=null) {
    return $this->fb($Object, $Label, FirePHP::INFO);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::WARN
   * @param mixes $Object
   * @param string $Label
   * @return true
   * @throws Exception
   */
  public function warn($Object, $Label=null) {
    return $this->fb($Object, $Label, FirePHP::WARN);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::ERROR
   * @param mixes $Object
   * @param string $Label
   * @return true
   * @throws Exception
   */
  public function error($Object, $Label=null) {
    return $this->fb($Object, $Label, FirePHP::ERROR);
  } 

  /**
   * Dumps key and variable to firebug server panel
   *
   * @see FirePHP::DUMP
   * @param string $Key
   * @param mixed $Variable
   * @return true
   * @throws Exception
   */
  public function dump($Key, $Variable) {
    return $this->fb($Variable, $Key, FirePHP::DUMP);
  }
  
  /**
   * Log a trace in the firebug console
   *
   * @see FirePHP::TRACE
   * @param string $Label
   * @return true
   * @throws Exception
   */
  public function trace($Label) {
    return $this->fb($Label, FirePHP::TRACE);
  } 

  /**
   * Log a table in the firebug console
   *
   * @see FirePHP::TABLE
   * @param string $Label
   * @param string $Table
   * @return true
   * @throws Exception
   */
  public function table($Label, $Table) {
    return $this->fb($Table, $Label, FirePHP::TABLE);
  }
  
  /**
   * Check if FirePHP is installed on client
   *
   * @return boolean
   */
  public function detectClientExtension() {
    /* Check if FirePHP is installed on client */
    if(!@preg_match_all('/\sFirePHP\/([\.|\d]*)\s?/si',$this->getUserAgent(),$m) ||
       !version_compare($m[1][0],'0.0.6','>=')) {
      return false;
    }
    return true;    
  }
 
  /**
   * Log varible to Firebug
   * 
   * @see http://www.firephp.org/Wiki/Reference/Fb
   * @param mixed $Object The variable to be logged
   * @return true Return TRUE if message was added to headers, FALSE otherwise
   * @throws Exception
   */
  public function fb($Object) {
  
    if(!$this->enabled) {
      return false;
    }
  
    if (headers_sent($filename, $linenum)) {
      // If we are logging from within the exception handler we cannot throw another exception
      if($this->inExceptionHandler) {
        // Simply echo the error out to the page
        echo '<div style="border: 2px solid red; font-family: Arial; font-size: 12px; background-color: lightgray; padding: 5px;"><span style="color: red; font-weight: bold;">FirePHP ERROR:</span> Headers already sent in <b>'.$filename.'</b> on line <b>'.$linenum.'</b>. Cannot send log data to FirePHP. You must have Output Buffering enabled via ob_start() or output_buffering ini directive.</div>';
      } else {
        throw $this->newException('Headers already sent in '.$filename.' on line '.$linenum.'. Cannot send log data to FirePHP. You must have Output Buffering enabled via ob_start() or output_buffering ini directive.');
      }
    }
  
    $Type = null;
    $Label = null;
    $Options = array();
  
    if(func_num_args()==1) {
    } else
    if(func_num_args()==2) {
      switch(func_get_arg(1)) {
        case self::LOG:
        case self::INFO:
        case self::WARN:
        case self::ERROR:
        case self::DUMP:
        case self::TRACE:
        case self::EXCEPTION:
        case self::TABLE:
        case self::GROUP_START:
        case self::GROUP_END:
          $Type = func_get_arg(1);
          break;
        default:
          $Label = func_get_arg(1);
          break;
      }
    } else
    if(func_num_args()==3) {
      $Type = func_get_arg(2);
      $Label = func_get_arg(1);
    } else
    if(func_num_args()==4) {
      $Type = func_get_arg(2);
      $Label = func_get_arg(1);
      $Options = func_get_arg(3);
    } else {
      throw $this->newException('Wrong number of arguments to fb() function!');
    }
  
  
    if(!$this->detectClientExtension()) {
      return false;
    }
  
    $meta = array();
    $skipFinalObjectEncode = false;
  
    if($Object instanceof Exception) {

      $meta['file'] = $this->_escapeTraceFile($Object->getFile());
      $meta['line'] = $Object->getLine();
      
      $trace = $Object->getTrace();
      if($Object instanceof ErrorException
         && isset($trace[0]['function'])
         && $trace[0]['function']=='errorHandler'
         && isset($trace[0]['class'])
         && $trace[0]['class']=='FirePHP') {
           
        $severity = false;
        switch($Object->getSeverity()) {
          case E_WARNING: $severity = 'E_WARNING'; break;
          case E_NOTICE: $severity = 'E_NOTICE'; break;
          case E_USER_ERROR: $severity = 'E_USER_ERROR'; break;
          case E_USER_WARNING: $severity = 'E_USER_WARNING'; break;
          case E_USER_NOTICE: $severity = 'E_USER_NOTICE'; break;
          case E_STRICT: $severity = 'E_STRICT'; break;
          case E_RECOVERABLE_ERROR: $severity = 'E_RECOVERABLE_ERROR'; break;
          case E_DEPRECATED: $severity = 'E_DEPRECATED'; break;
          case E_USER_DEPRECATED: $severity = 'E_USER_DEPRECATED'; break;
        }
           
        $Object = array('Class'=>get_class($Object),
                        'Message'=>$severity.': '.$Object->getMessage(),
                        'File'=>$this->_escapeTraceFile($Object->getFile()),
                        'Line'=>$Object->getLine(),
                        'Type'=>'trigger',
                        'Trace'=>$this->_escapeTrace(array_splice($trace,2)));
        $skipFinalObjectEncode = true;
      } else {
        $Object = array('Class'=>get_class($Object),
                        'Message'=>$Object->getMessage(),
                        'File'=>$this->_escapeTraceFile($Object->getFile()),
                        'Line'=>$Object->getLine(),
                        'Type'=>'throw',
                        'Trace'=>$this->_escapeTrace($trace));
        $skipFinalObjectEncode = true;
      }
      $Type = self::EXCEPTION;
      
    } else
    if($Type==self::TRACE) {
      
      $trace = debug_backtrace();
      if(!$trace) return false;
      for( $i=0 ; $i<sizeof($trace) ; $i++ ) {

        if(isset($trace[$i]['class'])
           && isset($trace[$i]['file'])
           && ($trace[$i]['class']=='FirePHP'
               || $trace[$i]['class']=='FB')
           && (substr($this->_standardizePath($trace[$i]['file']),-18,18)=='FirePHPCore/fb.php'
               || substr($this->_standardizePath($trace[$i]['file']),-29,29)=='FirePHPCore/FirePHP.class.php')) {
          /* Skip - FB::trace(), FB::send(), $firephp->trace(), $firephp->fb() */
        } else
        if(isset($trace[$i]['class'])
           && isset($trace[$i+1]['file'])
           && $trace[$i]['class']=='FirePHP'
           && substr($this->_standardizePath($trace[$i+1]['file']),-18,18)=='FirePHPCore/fb.php') {
          /* Skip fb() */
        } else
        if($trace[$i]['function']=='fb'
           || $trace[$i]['function']=='trace'
           || $trace[$i]['function']=='send') {
          $Object = array('Class'=>isset($trace[$i]['class'])?$trace[$i]['class']:'',
                          'Type'=>isset($trace[$i]['type'])?$trace[$i]['type']:'',
                          'Function'=>isset($trace[$i]['function'])?$trace[$i]['function']:'',
                          'Message'=>$trace[$i]['args'][0],
                          'File'=>isset($trace[$i]['file'])?$this->_escapeTraceFile($trace[$i]['file']):'',
                          'Line'=>isset($trace[$i]['line'])?$trace[$i]['line']:'',
                          'Args'=>isset($trace[$i]['args'])?$this->encodeObject($trace[$i]['args']):'',
                          'Trace'=>$this->_escapeTrace(array_splice($trace,$i+1)));

          $skipFinalObjectEncode = true;
          $meta['file'] = isset($trace[$i]['file'])?$this->_escapeTraceFile($trace[$i]['file']):'';
          $meta['line'] = isset($trace[$i]['line'])?$trace[$i]['line']:'';
          break;
        }
      }

    } else
    if($Type==self::TABLE) {
      
      if(isset($Object[0]) && is_string($Object[0])) {
        $Object[1] = $this->encodeTable($Object[1]);
      } else {
        $Object = $this->encodeTable($Object);
      }

      $skipFinalObjectEncode = true;
      
    } else
    if($Type==self::GROUP_START) {
      
      if(!$Label) {
        throw $this->newException('You must specify a label for the group!');
      }
      
    } else {
      if($Type===null) {
        $Type = self::LOG;
      }
    }
    
    if($this->options['includeLineNumbers']) {
      if(!isset($meta['file']) || !isset($meta['line'])) {

        $trace = debug_backtrace();
        for( $i=0 ; $trace && $i<sizeof($trace) ; $i++ ) {
  
          if(isset($trace[$i]['class'])
             && isset($trace[$i]['file'])
             && ($trace[$i]['class']=='FirePHP'
                 || $trace[$i]['class']=='FB')
             && (substr($this->_standardizePath($trace[$i]['file']),-18,18)=='FirePHPCore/fb.php'
                 || substr($this->_standardizePath($trace[$i]['file']),-29,29)=='FirePHPCore/FirePHP.class.php')) {
            /* Skip - FB::trace(), FB::send(), $firephp->trace(), $firephp->fb() */
          } else
          if(isset($trace[$i]['class'])
             && isset($trace[$i+1]['file'])
             && $trace[$i]['class']=='FirePHP'
             && substr($this->_standardizePath($trace[$i+1]['file']),-18,18)=='FirePHPCore/fb.php') {
            /* Skip fb() */
          } else
          if(isset($trace[$i]['file'])
             && substr($this->_standardizePath($trace[$i]['file']),-18,18)=='FirePHPCore/fb.php') {
            /* Skip FB::fb() */
          } else {
            $meta['file'] = isset($trace[$i]['file'])?$this->_escapeTraceFile($trace[$i]['file']):'';
            $meta['line'] = isset($trace[$i]['line'])?$trace[$i]['line']:'';
            break;
          }
        }      
      
      }
    } else {
      unset($meta['file']);
      unset($meta['line']);
    }

  	$this->setHeader('X-Wf-Protocol-1','http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
  	$this->setHeader('X-Wf-1-Plugin-1','http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/'.self::VERSION);
 
    $structure_index = 1;
    if($Type==self::DUMP) {
      $structure_index = 2;
    	$this->setHeader('X-Wf-1-Structure-2','http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1');
    } else {
    	$this->setHeader('X-Wf-1-Structure-1','http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
    }
  
    if($Type==self::DUMP) {
    	$msg = '{"'.$Label.'":'.$this->jsonEncode($Object, $skipFinalObjectEncode).'}';
    } else {
      $msg_meta = $Options;
      $msg_meta['Type'] = $Type;
      if($Label!==null) {
        $msg_meta['Label'] = $Label;
      }
      if(isset($meta['file']) && !isset($msg_meta['File'])) {
        $msg_meta['File'] = $meta['file'];
      }
      if(isset($meta['line']) && !isset($msg_meta['Line'])) {
        $msg_meta['Line'] = $meta['line'];
      }
    	$msg = '['.$this->jsonEncode($msg_meta).','.$this->jsonEncode($Object, $skipFinalObjectEncode).']';
    }
    
    $parts = explode("\n",chunk_split($msg, 5000, "\n"));

    for( $i=0 ; $i<count($parts) ; $i++) {
        
        $part = $parts[$i];
        if ($part) {
            
            if(count($parts)>2) {
              // Message needs to be split into multiple parts
              $this->setHeader('X-Wf-1-'.$structure_index.'-'.'1-'.$this->messageIndex,
                               (($i==0)?strlen($msg):'')
                               . '|' . $part . '|'
                               . (($i<count($parts)-2)?'\\':''));
            } else {
              $this->setHeader('X-Wf-1-'.$structure_index.'-'.'1-'.$this->messageIndex,
                               strlen($part) . '|' . $part . '|');
            }
            
            $this->messageIndex++;
            
            if ($this->messageIndex > 99999) {
                throw $this->newException('Maximum number (99,999) of messages reached!');             
            }
        }
    }

  	$this->setHeader('X-Wf-1-Index',$this->messageIndex-1);

    return true;
  }
  
  /**
   * Standardizes path for windows systems.
   *
   * @param string $Path
   * @return string
   */
  protected function _standardizePath($Path) {
    return preg_replace('/\\\\+/','/',$Path);    
  }
  
  /**
   * Escape trace path for windows systems
   *
   * @param array $Trace
   * @return array
   */
  protected function _escapeTrace($Trace) {
    if(!$Trace) return $Trace;
    for( $i=0 ; $i<sizeof($Trace) ; $i++ ) {
      if(isset($Trace[$i]['file'])) {
        $Trace[$i]['file'] = $this->_escapeTraceFile($Trace[$i]['file']);
      }
      if(isset($Trace[$i]['args'])) {
        $Trace[$i]['args'] = $this->encodeObject($Trace[$i]['args']);
      }
    }
    return $Trace;    
  }
  
  /**
   * Escape file information of trace for windows systems
   *
   * @param string $File
   * @return string
   */
  protected function _escapeTraceFile($File) {
    /* Check if we have a windows filepath */
    if(strpos($File,'\\')) {
      /* First strip down to single \ */
      
      $file = preg_replace('/\\\\+/','\\',$File);
      
      return $file;
    }
    return $File;
  }

  /**
   * Send header
   *
   * @param string $Name
   * @param string_type $Value
   */
  protected function setHeader($Name, $Value) {
    return header($Name.': '.$Value);
  }

  /**
   * Get user agent
   *
   * @return string|false
   */
  protected function getUserAgent() {
    if(!isset($_SERVER['HTTP_USER_AGENT'])) return false;
    return $_SERVER['HTTP_USER_AGENT'];
  }

  /**
   * Returns a new exception
   *
   * @param string $Message
   * @return Exception
   */
  protected function newException($Message) {
    return new Exception($Message);
  }
  
  /**
   * Encode an object into a JSON string
   * 
   * Uses PHP's jeson_encode() if available
   * 
   * @param object $Object The object to be encoded
   * @return string The JSON string
   */
  public function jsonEncode($Object, $skipObjectEncode=false)
  {
    if(!$skipObjectEncode) {
      $Object = $this->encodeObject($Object);
    }
    
    if(function_exists('json_encode')
       && $this->options['useNativeJsonEncode']!=false) {

      return json_encode($Object);
    } else {
      return $this->json_encode($Object);
    }
  }

  /**
   * Encodes a table by encoding each row and column with encodeObject()
   * 
   * @param array $Table The table to be encoded
   * @return array
   */  
  protected function encodeTable($Table) {
    
    if(!$Table) return $Table;
    
    $new_table = array();
    foreach($Table as $row) {
  
      if(is_array($row)) {
        $new_row = array();
        
        foreach($row as $item) {
          $new_row[] = $this->encodeObject($item);
        }
        
        $new_table[] = $new_row;
      }
    }
    
    return $new_table;
  }

  /**
   * Encodes an object including members with
   * protected and private visibility
   * 
   * @param Object $Object The object to be encoded
   * @param int $Depth The current traversal depth
   * @return array All members of the object
   */
  protected function encodeObject($Object, $ObjectDepth = 1, $ArrayDepth = 1)
  {
    $return = array();

    if (is_resource($Object)) {

      return '** '.(string)$Object.' **';

    } else    
    if (is_object($Object)) {

        if ($ObjectDepth > $this->options['maxObjectDepth']) {
          return '** Max Object Depth ('.$this->options['maxObjectDepth'].') **';
        }
        
        foreach ($this->objectStack as $refVal) {
            if ($refVal === $Object) {
                return '** Recursion ('.get_class($Object).') **';
            }
        }
        array_push($this->objectStack, $Object);
                
        $return['__className'] = $class = get_class($Object);
        $class_lower = strtolower($class);

        $reflectionClass = new ReflectionClass($class);  
        $properties = array();
        foreach( $reflectionClass->getProperties() as $property) {
          $properties[$property->getName()] = $property;
        }
            
        $members = (array)$Object;
            
        foreach( $properties as $raw_name => $property ) {
          
          $name = $raw_name;
          if($property->isStatic()) {
            $name = 'static:'.$name;
          }
          if($property->isPublic()) {
            $name = 'public:'.$name;
          } else
          if($property->isPrivate()) {
            $name = 'private:'.$name;
            $raw_name = "\0".$class."\0".$raw_name;
          } else
          if($property->isProtected()) {
            $name = 'protected:'.$name;
            $raw_name = "\0".'*'."\0".$raw_name;
          }
          
          if(!(isset($this->objectFilters[$class_lower])
               && is_array($this->objectFilters[$class_lower])
               && in_array($raw_name,$this->objectFilters[$class_lower]))) {

            if(array_key_exists($raw_name,$members)
               && !$property->isStatic()) {
              
              $return[$name] = $this->encodeObject($members[$raw_name], $ObjectDepth + 1, 1);      
            
            } else {
              if(method_exists($property,'setAccessible')) {
                $property->setAccessible(true);
                $return[$name] = $this->encodeObject($property->getValue($Object), $ObjectDepth + 1, 1);
              } else
              if($property->isPublic()) {
                $return[$name] = $this->encodeObject($property->getValue($Object), $ObjectDepth + 1, 1);
              } else {
                $return[$name] = '** Need PHP 5.3 to get value **';
              }
            }
          } else {
            $return[$name] = '** Excluded by Filter **';
          }
        }
        
        // Include all members that are not defined in the class
        // but exist in the object
        foreach( $members as $raw_name => $value ) {
          
          $name = $raw_name;
          
          if ($name{0} == "\0") {
            $parts = explode("\0", $name);
            $name = $parts[2];
          }
          
          if(!isset($properties[$name])) {
            $name = 'undeclared:'.$name;
              
            if(!(isset($this->objectFilters[$class_lower])
                 && is_array($this->objectFilters[$class_lower])
                 && in_array($raw_name,$this->objectFilters[$class_lower]))) {
              
              $return[$name] = $this->encodeObject($value, $ObjectDepth + 1, 1);
            } else {
              $return[$name] = '** Excluded by Filter **';
            }
          }
        }
        
        array_pop($this->objectStack);
        
    } elseif (is_array($Object)) {

        if ($ArrayDepth > $this->options['maxArrayDepth']) {
          return '** Max Array Depth ('.$this->options['maxArrayDepth'].') **';
        }
      
        foreach ($Object as $key => $val) {
          
          // Encoding the $GLOBALS PHP array causes an infinite loop
          // if the recursion is not reset here as it contains
          // a reference to itself. This is the only way I have come up
          // with to stop infinite recursion in this case.
          if($key=='GLOBALS'
             && is_array($val)
             && array_key_exists('GLOBALS',$val)) {
            $val['GLOBALS'] = '** Recursion (GLOBALS) **';
          }
          
          $return[$key] = $this->encodeObject($val, 1, $ArrayDepth + 1);
        }
    } else {
      if(self::is_utf8($Object)) {
        return $Object;
      } else {
        return utf8_encode($Object);
      }
    }
    return $return;
  }

  /**
   * Returns true if $string is valid UTF-8 and false otherwise.
   *
   * @param mixed $str String to be tested
   * @return boolean
   */
  protected static function is_utf8($str) {
    $c=0; $b=0;
    $bits=0;
    $len=strlen($str);
    for($i=0; $i<$len; $i++){
        $c=ord($str[$i]);
        if($c > 128){
            if(($c >= 254)) return false;
            elseif($c >= 252) $bits=6;
            elseif($c >= 248) $bits=5;
            elseif($c >= 240) $bits=4;
            elseif($c >= 224) $bits=3;
            elseif($c >= 192) $bits=2;
            else return false;
            if(($i+$bits) > $len) return false;
            while($bits > 1){
                $i++;
                $b=ord($str[$i]);
                if($b < 128 || $b > 191) return false;
                $bits--;
            }
        }
    }
    return true;
  } 

  /**
   * Converts to and from JSON format.
   *
   * JSON (JavaScript Object Notation) is a lightweight data-interchange
   * format. It is easy for humans to read and write. It is easy for machines
   * to parse and generate. It is based on a subset of the JavaScript
   * Programming Language, Standard ECMA-262 3rd Edition - December 1999.
   * This feature can also be found in  Python. JSON is a text format that is
   * completely language independent but uses conventions that are familiar
   * to programmers of the C-family of languages, including C, C++, C#, Java,
   * JavaScript, Perl, TCL, and many others. These properties make JSON an
   * ideal data-interchange language.
   *
   * This package provides a simple encoder and decoder for JSON notation. It
   * is intended for use with client-side Javascript applications that make
   * use of HTTPRequest to perform server communication functions - data can
   * be encoded into JSON notation for use in a client-side javascript, or
   * decoded from incoming Javascript requests. JSON format is native to
   * Javascript, and can be directly eval()'ed with no further parsing
   * overhead
   *
   * All strings should be in ASCII or UTF-8 format!
   *
   * LICENSE: Redistribution and use in source and binary forms, with or
   * without modification, are permitted provided that the following
   * conditions are met: Redistributions of source code must retain the
   * above copyright notice, this list of conditions and the following
   * disclaimer. Redistributions in binary form must reproduce the above
   * copyright notice, this list of conditions and the following disclaimer
   * in the documentation and/or other materials provided with the
   * distribution.
   *
   * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
   * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
   * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
   * NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
   * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
   * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
   * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
   * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
   * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
   * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
   * DAMAGE.
   *
   * @category
   * @package     Services_JSON
   * @author      Michal Migurski <mike-json@teczno.com>
   * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
   * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
   * @author      Christoph Dorn <christoph@christophdorn.com>
   * @copyright   2005 Michal Migurski
   * @version     CVS: $Id: JSON.php,v 1.31 2006/06/28 05:54:17 migurski Exp $
   * @license     http://www.opensource.org/licenses/bsd-license.php
   * @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
   */
   
     
  /**
   * Keep a list of objects as we descend into the array so we can detect recursion.
   */
  private $json_objectStack = array();


 /**
  * convert a string from one UTF-8 char to one UTF-16 char
  *
  * Normally should be handled by mb_convert_encoding, but
  * provides a slower PHP-only method for installations
  * that lack the multibye string extension.
  *
  * @param    string  $utf8   UTF-8 character
  * @return   string  UTF-16 character
  * @access   private
  */
  private function json_utf82utf16($utf8)
  {
      // oh please oh please oh please oh please oh please
      if(function_exists('mb_convert_encoding')) {
          return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
      }

      switch(strlen($utf8)) {
          case 1:
              // this case should never be reached, because we are in ASCII range
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return $utf8;

          case 2:
              // return a UTF-16 character from a 2-byte UTF-8 char
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return chr(0x07 & (ord($utf8{0}) >> 2))
                   . chr((0xC0 & (ord($utf8{0}) << 6))
                       | (0x3F & ord($utf8{1})));

          case 3:
              // return a UTF-16 character from a 3-byte UTF-8 char
              // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
              return chr((0xF0 & (ord($utf8{0}) << 4))
                       | (0x0F & (ord($utf8{1}) >> 2)))
                   . chr((0xC0 & (ord($utf8{1}) << 6))
                       | (0x7F & ord($utf8{2})));
      }

      // ignoring UTF-32 for now, sorry
      return '';
  }

 /**
  * encodes an arbitrary variable into JSON format
  *
  * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
  *                           see argument 1 to Services_JSON() above for array-parsing behavior.
  *                           if var is a strng, note that encode() always expects it
  *                           to be in ASCII or UTF-8 format!
  *
  * @return   mixed   JSON string representation of input var or an error if a problem occurs
  * @access   public
  */
  private function json_encode($var)
  {
    
    if(is_object($var)) {
      if(in_array($var,$this->json_objectStack)) {
        return '"** Recursion **"';
      }
    }
          
      switch (gettype($var)) {
          case 'boolean':
              return $var ? 'true' : 'false';

          case 'NULL':
              return 'null';

          case 'integer':
              return (int) $var;

          case 'double':
          case 'float':
              return (float) $var;

          case 'string':
              // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
              $ascii = '';
              $strlen_var = strlen($var);

             /*
              * Iterate over every character in the string,
              * escaping with a slash or encoding to UTF-8 where necessary
              */
              for ($c = 0; $c < $strlen_var; ++$c) {

                  $ord_var_c = ord($var{$c});

                  switch (true) {
                      case $ord_var_c == 0x08:
                          $ascii .= '\b';
                          break;
                      case $ord_var_c == 0x09:
                          $ascii .= '\t';
                          break;
                      case $ord_var_c == 0x0A:
                          $ascii .= '\n';
                          break;
                      case $ord_var_c == 0x0C:
                          $ascii .= '\f';
                          break;
                      case $ord_var_c == 0x0D:
                          $ascii .= '\r';
                          break;

                      case $ord_var_c == 0x22:
                      case $ord_var_c == 0x2F:
                      case $ord_var_c == 0x5C:
                          // double quote, slash, slosh
                          $ascii .= '\\'.$var{$c};
                          break;

                      case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                          // characters U-00000000 - U-0000007F (same as ASCII)
                          $ascii .= $var{$c};
                          break;

                      case (($ord_var_c & 0xE0) == 0xC0):
                          // characters U-00000080 - U-000007FF, mask 110XXXXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                          $c += 1;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xF0) == 0xE0):
                          // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}));
                          $c += 2;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xF8) == 0xF0):
                          // characters U-00010000 - U-001FFFFF, mask 11110XXX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}));
                          $c += 3;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xFC) == 0xF8):
                          // characters U-00200000 - U-03FFFFFF, mask 111110XX
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}),
                                       ord($var{$c + 4}));
                          $c += 4;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;

                      case (($ord_var_c & 0xFE) == 0xFC):
                          // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                          $char = pack('C*', $ord_var_c,
                                       ord($var{$c + 1}),
                                       ord($var{$c + 2}),
                                       ord($var{$c + 3}),
                                       ord($var{$c + 4}),
                                       ord($var{$c + 5}));
                          $c += 5;
                          $utf16 = $this->json_utf82utf16($char);
                          $ascii .= sprintf('\u%04s', bin2hex($utf16));
                          break;
                  }
              }

              return '"'.$ascii.'"';

          case 'array':
             /*
              * As per JSON spec if any array key is not an integer
              * we must treat the the whole array as an object. We
              * also try to catch a sparsely populated associative
              * array with numeric keys here because some JS engines
              * will create an array with empty indexes up to
              * max_index which can cause memory issues and because
              * the keys, which may be relevant, will be remapped
              * otherwise.
              *
              * As per the ECMA and JSON specification an object may
              * have any string as a property. Unfortunately due to
              * a hole in the ECMA specification if the key is a
              * ECMA reserved word or starts with a digit the
              * parameter is only accessible using ECMAScript's
              * bracket notation.
              */

              // treat as a JSON object
              if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                  
                  $this->json_objectStack[] = $var;

                  $properties = array_map(array($this, 'json_name_value'),
                                          array_keys($var),
                                          array_values($var));

                  array_pop($this->json_objectStack);

                  foreach($properties as $property) {
                      if($property instanceof Exception) {
                          return $property;
                      }
                  }

                  return '{' . join(',', $properties) . '}';
              }

              $this->json_objectStack[] = $var;

              // treat it like a regular array
              $elements = array_map(array($this, 'json_encode'), $var);

              array_pop($this->json_objectStack);

              foreach($elements as $element) {
                  if($element instanceof Exception) {
                      return $element;
                  }
              }

              return '[' . join(',', $elements) . ']';

          case 'object':
              $vars = self::encodeObject($var);

              $this->json_objectStack[] = $var;

              $properties = array_map(array($this, 'json_name_value'),
                                      array_keys($vars),
                                      array_values($vars));

              array_pop($this->json_objectStack);
              
              foreach($properties as $property) {
                  if($property instanceof Exception) {
                      return $property;
                  }
              }
                     
              return '{' . join(',', $properties) . '}';

          default:
              return null;
      }
  }

 /**
  * array-walking function for use in generating JSON-formatted name-value pairs
  *
  * @param    string  $name   name of key to use
  * @param    mixed   $value  reference to an array element to be encoded
  *
  * @return   string  JSON-formatted name-value pair, like '"name":value'
  * @access   private
  */
  private function json_name_value($name, $value)
  {
      // Encoding the $GLOBALS PHP array causes an infinite loop
      // if the recursion is not reset here as it contains
      // a reference to itself. This is the only way I have come up
      // with to stop infinite recursion in this case.
      if($name=='GLOBALS'
         && is_array($value)
         && array_key_exists('GLOBALS',$value)) {
        $value['GLOBALS'] = '** Recursion **';
      }
    
      $encoded_value = $this->json_encode($value);

      if($encoded_value instanceof Exception) {
          return $encoded_value;
      }

      return $this->json_encode(strval($name)) . ':' . $encoded_value;
  }
}
