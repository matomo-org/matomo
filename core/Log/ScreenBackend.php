<?php

namespace Piwik\Log;

use Piwik\Common;
use Piwik\Log;
use Piwik\Piwik;

/**
 * Writes log to screen.
 */
class ScreenBackend extends Backend
{
    public function __invoke($level, $tag, $datetime, $message, Log $logger)
    {
        $message = $this->getMessageFormattedScreen($level, $tag, $datetime, $message, $logger);
        if (empty($message)) {
            return;
        }

        echo $message;
    }

    public function getMessageFormattedScreen($level, $tag, $datetime, $message, Log $logger)
    {
        static $currentRequestKey;

        if (empty($currentRequestKey)) {
            $currentRequestKey = substr(Common::generateUniqId(), 0, 5);
        }

        if (is_string($message)) {
            if (!defined('PIWIK_TEST_MODE')) {
                $message = '[' . $currentRequestKey . '] ' . $message;
            }
            $message = $this->formatMessage($level, $tag, $datetime, $message);

            if (!Common::isPhpCliMode()) {
                $message = Common::sanitizeInputValue($message);
                $message = '<pre>' . $message . '</pre>';
            }
        } else {
            /**
             * Triggered when trying to log an object to the screen. Plugins can use
             * this event to convert objects to strings before they are logged.
             *
             * The result of this callback can be HTML so no sanitization is done on the result.
             * This means **YOU MUST SANITIZE THE MESSAGE YOURSELF** if you use this event.
             *
             * **Example**
             *
             *     public function formatScreenMessage(&$message, $level, $tag, $datetime, $logger) {
             *         if ($message instanceof MyCustomDebugInfo) {
             *             $message = Common::sanitizeInputValue($message->formatForScreen());
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
            Piwik::postEvent(Log::FORMAT_SCREEN_MESSAGE_EVENT, array(&$message, $level, $tag, $datetime, $logger));
        }
        $message = trim($message);

        return $message . "\n";
    }
}
