<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Piwik\Common;
use Piwik\Db;

/**
 * Writes log to database.
 */
class DatabaseHandler extends AbstractProcessingHandler
{
    protected function write(array $record)
    {
        $sql = "INSERT INTO " . Common::prefixTable('logger_message')
            . " (tag, timestamp, level, message)"
            . " VALUES (?, ?, ?, ?)";

        $message = trim($record['formatted']);

        Db::query($sql, array($record['extra']['class'], $record['datetime']->format('Y-m-d H:i:s'), $record['level_name'], $message));
    }
}
