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
namespace Piwik\Log;
use Piwik\Log;
use Zend_Log_Writer_Stream;

/**
 * Class used to log an error event.
 *
 * @package Piwik
 * @subpackage Log
 */
class Error extends Log
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
        $screenFormatter = new ErrorScreenFormatter();
        $fileFormatter = new FileFormatter();
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
        $writerScreen = new \Zend_Log_Writer_Stream('php://stderr');
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

        parent::log($event, Log::ERR, null);
    }
}

