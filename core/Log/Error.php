<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Class used to log an error event.
 *
 * @package Piwik
 * @subpackage Piwik_Log
 */
class Piwik_Log_Error extends Piwik_Log
{
    const ID = 'logger_error';

    /**
     * Constructor
     */
    function __construct()
    {
        $logToFileFilename = self::ID;
        $logToDatabaseTableName = self::ID;
        $logToDatabaseColumnMapping = array(
            'timestamp' => 'timestamp',
            'message'   => 'message',
            'errno'     => 'errno',
            'errline'   => 'errline',
            'errfile'   => 'errfile',
            'backtrace' => 'backtrace'
        );
        $screenFormatter = new Piwik_Log_Error_Formatter_ScreenFormatter();
        $fileFormatter = new Piwik_Log_Formatter_FileFormatter();
        parent::__construct($logToFileFilename,
            $fileFormatter,
            $screenFormatter,
            $logToDatabaseTableName,
            $logToDatabaseColumnMapping);
    }

    /**
     * Adds the writer
     */
    function addWriteToScreen()
    {
        parent::addWriteToScreen();
        $writerScreen = new Zend_Log_Writer_Stream('php://stderr');
        $writerScreen->setFormatter($this->screenFormatter);
        $this->addWriter($writerScreen);
    }

    /**
     * Logs the given error event
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param string $backtrace
     */
    public function logEvent($errno, $errstr, $errfile, $errline, $backtrace)
    {
        $event = array();
        $event['errno'] = $errno;
        $event['message'] = $errstr;
        $event['errfile'] = $errfile;
        $event['errline'] = $errline;
        $event['backtrace'] = $backtrace;

        parent::log($event, Piwik_Log::ERR, null);
    }
}

/**
 * Format an error event to be displayed on the screen.
 *
 * @package Piwik
 * @subpackage Piwik_Log
 */
class Piwik_Log_Error_Formatter_ScreenFormatter extends Piwik_Log_Formatter_ScreenFormatter
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string  formatted line to write to the log
     */
    public function format($event)
    {
        $event = parent::formatEvent($event);

        $errno = $event['errno'];
        $errstr = $event['message'];
        $errfile = $event['errfile'];
        $errline = $event['errline'];
        $backtrace = $event['backtrace'];

        $strReturned = '';
        $errno = $errno & error_reporting();

        // problem when using error_reporting with the @ silent fail operator
        // it gives an errno 0, and in this case the objective is to NOT display anything on the screen!
        // is there any other case where the errno is zero at this point?
        if ($errno == 0) return '';
        $strReturned .= "\n<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'>
	    <strong>There is an error. Please report the message (Piwik " . (class_exists('Piwik_Version') ? Piwik_Version::VERSION : '') . ")
	    and full backtrace in the <a href='?module=Proxy&action=redirect&url=http://forum.piwik.org' target='_blank'>Piwik forums</a> (please do a Search first as it might have been reported already!).<br /><br/>
	    ";
        switch ($errno) {
            case E_ERROR:
                $strReturned .= "Error";
                break;
            case E_WARNING:
                $strReturned .= "Warning";
                break;
            case E_PARSE:
                $strReturned .= "Parse Error";
                break;
            case E_NOTICE:
                $strReturned .= "Notice";
                break;
            case E_CORE_ERROR:
                $strReturned .= "Core Error";
                break;
            case E_CORE_WARNING:
                $strReturned .= "Core Warning";
                break;
            case E_COMPILE_ERROR:
                $strReturned .= "Compile Error";
                break;
            case E_COMPILE_WARNING:
                $strReturned .= "Compile Warning";
                break;
            case E_USER_ERROR:
                $strReturned .= "User Error";
                break;
            case E_USER_WARNING:
                $strReturned .= "User Warning";
                break;
            case E_USER_NOTICE:
                $strReturned .= "User Notice";
                break;
            case E_STRICT:
                $strReturned .= "Strict Notice";
                break;
            case E_RECOVERABLE_ERROR:
                $strReturned .= "Recoverable Error";
                break;
            case E_DEPRECATED:
                $strReturned .= "Deprecated";
                break;
            case E_USER_DEPRECATED:
                $strReturned .= "User Deprecated";
                break;
            default:
                $strReturned .= "Unknown error ($errno)";
                break;
        }
        $strReturned .= ":</strong> <i>$errstr</i> in <b>$errfile</b> on line <b>$errline</b>\n";
        $strReturned .= "<br /><br />Backtrace --&gt;<div style=\"font-family:Courier;font-size:10pt\">";
        $strReturned .= str_replace(array("\n", '#'), array("<br />\n", "<br />\n#"), $backtrace);
        $strReturned .= "</div><br />";
        $strReturned .= "\n </pre></div><br />";

        return parent::format($strReturned);
    }
}
