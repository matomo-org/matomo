<?php

namespace Piwik\Log;

use Piwik\Common;
use Piwik\Db;
use Piwik\Log;
use Piwik\Piwik;

/**
 * Writes log to database.
 */
class DatabaseBackend extends Backend
{
    public function __invoke($level, $tag, $datetime, $message, Log $logger)
    {
        $message = $this->getMessageFormattedDatabase($level, $tag, $datetime, $message, $logger);
        if (empty($message)) {
            return;
        }

        $sql = "INSERT INTO " . Common::prefixTable('logger_message')
            . " (tag, timestamp, level, message)"
            . " VALUES (?, ?, ?, ?)";

        Db::query($sql, array($tag, $datetime, self::getStringLevel($level), (string)$message));
    }

    private function getMessageFormattedDatabase($level, $tag, $datetime, $message, $logger)
    {
        if (is_string($message)) {
            $message = $this->formatMessage($level, $tag, $datetime, $message);
        } else {
            /**
             * Triggered when trying to log an object to a database table. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * **Example**
             *
             *     public function formatDatabaseMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = $message->formatForDatabase();
             *         }
             *     }
             *
             * @param mixed &$message The object that is being logged. Event handlers should
             *                        check if the object is of a certain type and if it is,
             *                        set `$message` to the string that should be logged.
             * @param int $level The log level used with this log entry.
             * @param string $tag The current plugin that started logging (or if no plugin,
             *                    the current class).
             * @param string $datetime Datetime of the logging call.
             * @param Log $logger The Log singleton.
             */
            Piwik::postEvent(Log::FORMAT_DATABASE_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }
        $message = trim($message);

        return $message;
    }
}
