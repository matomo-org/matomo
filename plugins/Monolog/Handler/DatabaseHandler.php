<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

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
        $sql = sprintf(
            'INSERT INTO %s (tag, timestamp, level, message) VALUES (?, ?, ?, ?)',
            Common::prefixTable('logger_message')
        );

        $queryLog = Db::isQueryLogEnabled();
        Db::enableQueryLog(false);

        Db::query($sql, array(
            $record['extra']['class'],
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['level_name'],
            trim($record['formatted'])
        ));

        Db::enableQueryLog($queryLog);
    }
}
