<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Backend;

use Piwik\Common;
use Piwik\Db;
use Piwik\Log;

/**
 * Writes log to database.
 */
class DatabaseBackend extends Backend
{
    public function __invoke(array $record, Log $logger)
    {
        $message = $this->formatMessage($record, $logger);

        if (empty($message)) {
            return;
        }

        $sql = "INSERT INTO " . Common::prefixTable('logger_message')
            . " (tag, timestamp, level, message)"
            . " VALUES (?, ?, ?, ?)";

        Db::query($sql, array($record['extra']['class'], $record['time']->format('Y-m-d H:i:s'), $record['level_name'], (string) $message));
    }
}
