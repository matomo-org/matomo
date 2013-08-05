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

use Piwik\Common;
use Piwik\Log;

/**
 * Class used to log an exception event.
 * Displays the exception with a user friendly error message, suggests to get support from piwik.org
 *
 * @package Piwik
 * @subpackage Log
 */
class Exception extends Log
{
    const ID = 'logger_exception';

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
        $screenFormatter = new ExceptionScreenFormatter();
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
     * Logs the given exception event
     *
     * @param \Exception $exception
     */
    public function logEvent(\Exception $exception)
    {
        $event = array();
        $event['errno'] = $exception->getCode();
        $event['message'] = $exception->getMessage();
        $event['errfile'] = $exception->getFile();
        $event['errline'] = $exception->getLine();
        $event['backtrace'] = $exception->getTraceAsString();

        parent::log($event, Log::CRIT, null);
    }
}
