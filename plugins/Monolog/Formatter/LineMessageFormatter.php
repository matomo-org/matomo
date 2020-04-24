<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Formats a log message into a line of text using our custom Piwik log format.
 */
class LineMessageFormatter implements FormatterInterface
{
    /**
     * The log message format string that turns a tag name, date-time and message into
     * one string to log.
     *
     * @var string
     */
    private $logMessageFormat;

    private $allowInlineLineBreaks;

    /**
     * @param string $logMessageFormat
     * @param bool $allowInlineLineBreaks If disabled, a log message will be created for each line
     */
    public function __construct($logMessageFormat, $allowInlineLineBreaks = true)
    {
        $this->logMessageFormat = $logMessageFormat;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks;
    }

    public function format(array $record)
    {
        $class = isset($record['extra']['class']) ? $record['extra']['class'] : '';
        $date = $record['datetime']->format('Y-m-d H:i:s T');

        $message = trim($record['message']);

        if ($this->allowInlineLineBreaks) {
            $message  = str_replace("\n", "\n  ", $message); // intend lines
            $messages = array($message);
        } else {
            $messages = explode("\n", $message);
        }

        $total = '';

        foreach ($messages as $message) {
            $message = $this->prefixMessageWithRequestId($record, $message);
            $total  .= $this->formatMessage($class, $message, $date, $record);
        }

        return $total;
    }

    private function formatMessage($class, $message, $date, $record)
    {
        $trace = isset($record['context']['trace']) ? self::formatTrace($record['context']['trace']) : '';
        $message = str_replace(
            array('%tag%', '%message%', '%datetime%', '%level%', '%trace%'),
            array($class, $message, $date, $record['level_name'], $trace),
            $this->logMessageFormat
        );

        $message = trim($message) . "\n";
        return $message;
    }

    private static function formatTrace(array $trace, $numLevels = 10)
    {
        $strTrace = '';
        for ($i = 0; $i < $numLevels; $i++) {
            if (!isset($trace[$i])) {
                continue;
            }

            $level = $trace[$i];
            $levelTrace = '';
            if (isset($level['file'], $level['line'])) {
                $levelTrace = '#' . $i . (str_replace(PIWIK_DOCUMENT_ROOT, '', $level['file'])) . '(' . $level['line'] . ')';
            } elseif (isset($level['class'], $level['type'], $level['function'])) {
                $levelTrace = '[internal function]: ' . $level['class'] . $level['type'] . $level['function'] . '()';
            }
            if ($levelTrace) {
                $strTrace .= $levelTrace . ",";
            }
        }
        return trim($strTrace, ",");
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    private function prefixMessageWithRequestId(array $record, $message)
    {
        $requestId = isset($record['extra']['request_id']) ? $record['extra']['request_id'] : '';

        $message = trim($message);

        if ($requestId) {
            $message = '[' . $requestId . '] ' . $message;
        }

        return $message;
    }
}
