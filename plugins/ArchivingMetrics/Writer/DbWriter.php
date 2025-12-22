<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics\Writer;

use Piwik\Common;
use Piwik\Db;

final class DbWriter implements WriterInterface
{
    public function write(array $record): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('archiving_metrics') . ' (idarchive, idsite, segment, date1, date2, period, ts_started, ts_finished, total_time, total_time_exclusive)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $record['idarchive'],
                $record['idsite'],
                $record['segment'],
                $record['date1'],
                $record['date2'],
                $record['period'],
                $record['ts_started'],
                $record['ts_finished'],
                $record['total_time'],
                $record['total_time_exclusive'],
            ]
        );
    }
}
