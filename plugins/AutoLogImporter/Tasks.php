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
use Piwik\Settings\Setting;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('importLogFiles');
    }

    public function importLogFiles()
    {
        $settings = StaticContainer::get('Piwik\Plugins\AutoLogImporter\Settings');

        if (!$settings->enabled->getValue()) {
            return;
        }

        // we validate to make sure directory is still readable etc, if not an exception will be thrown
        $this->validateSetting($settings->logFilesPath);

        $importer = StaticContainer::get('Piwik\Plugins\AutoLogImporter\LogImporter');
        $importer->importFiles();
    }

    private function validateSetting(Setting $setting)
    {
        if (isset($setting->validate)) {
            call_user_func($setting->validate, $setting->getValue(), $setting);
        }
    }
}
