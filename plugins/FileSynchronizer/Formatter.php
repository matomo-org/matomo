<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter as MetricsFormatter;

class Formatter
{

    public function addTimeAgoAsSentence($files)
    {
        $formatter = new MetricsFormatter();
        $now = StaticContainer::get('FileSynchronizer.currentTimestamp');

        foreach ($files as &$file) {
            $diff = $now - Date::factory($file['start_date'])->getTimestamp();
            $file['time_ago'] = $formatter->getPrettyTimeFromSeconds($diff, true);
        }

        return $files;
    }

    public function formatSyncedFiles($files)
    {
        $formatter = new MetricsFormatter();

        foreach ($files as &$syncedFile) {
            $syncedFile['source_filename'] = basename($syncedFile['source']);
            $syncedFile['date'] = substr($syncedFile['start_date'], 0, 10);
            $syncedFile['duration'] = $syncedFile['duration_in_ms'] . 'ms';
            $syncedFile['size_human'] = $formatter->getPrettySizeFromBytes($syncedFile['file_size']);
        }

        return $files;
    }

}
