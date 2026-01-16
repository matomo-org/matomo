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
use Piwik\Plugins\ArchivingMetrics\Context;

final class DbWriter implements WriterInterface
{
    public function write(Context $context, array $timing): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('archiving_metrics') . ' (idarchive, idsite, archive_name, date1, date2, period, ts_started, ts_finished, total_time, total_time_exclusive)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $timing['idarchive'],
                $context->idSite,
                $timing['archive_name'],
                $context->period->getDateTimeStart()->toString('Y-m-d'),
                $context->period->getDateTimeEnd()->toString('Y-m-d'),
                $context->period->getId(),
                $timing['ts_started'],
                $timing['ts_finished'],
                $timing['total_time'],
                $timing['total_time_exclusive'],
            ]
        );
    }
}
