<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Metrics\Formatter as MetricsFormatter;

class Formatter
{
    public function addTimeAgoAsSentence($files)
    {
        $formatter = new MetricsFormatter();
        $now = StaticContainer::get('AutoLogImporter.currentTimestamp');

        foreach ($files as &$file) {
            $diff = $now - Date::factory($file['start_date'])->getTimestamp();
            $file['time_ago'] = $formatter->getPrettyTimeFromSeconds($diff, true);
        }

        return $files;
    }

    public function formatImportedFiles($files)
    {
        $formatter = new MetricsFormatter();

        foreach ($files as &$file) {
            $file['filename'] = basename($file['file']);
            $file['date'] = substr($file['start_date'], 0, 10);
            $file['size_human'] = $formatter->getPrettySizeFromBytes((int) $file['file_size']);
        }

        return $files;
    }
}
