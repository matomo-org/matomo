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
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Log;

/**
 * Class used to log a standard message event.
 *
 * @package Piwik
 * @subpackage Log
 */
class Message extends Log
{
    const ID = 'logger_message';

    /**
     * Constructor
     */
    function __construct()
    {
        $logToFileFilename = self::ID . ".htm";
        $logToDatabaseTableName = self::ID;
        $logToDatabaseColumnMapping = array(
            'message'   => 'message',
            'timestamp' => 'timestamp'
        );
        $screenFormatter = new MessageScreenFormatter();
        $fileFormatter = new FileFormatter();

        parent::__construct($logToFileFilename,
            $fileFormatter,
            $screenFormatter,
            $logToDatabaseTableName,
            $logToDatabaseColumnMapping);
    }

    /**
     * Logs the given message
     *
     * @param string $message
     */
    public function logEvent($message)
    {
        $event = array();
        $event['message'] = $message;
        parent::log($event, Log::INFO, null);
    }
}