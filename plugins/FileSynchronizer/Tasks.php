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
use Piwik\Settings\Setting;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->hourly('syncFiles');
    }

    public function syncFiles()
    {
        $settings = StaticContainer::get('Piwik\Plugins\FileSynchronizer\Settings');

        if (!$settings->enabled->getValue()) {
            return;
        }

        // we validate to make sure it is still readable etc.
        $this->validateSetting($settings->copyCommandTemplate);
        $this->validateSetting($settings->sourceDirectory);
        $this->validateSetting($settings->targetDirectory);

        $sync = StaticContainer::get('Piwik\Plugins\FileSynchronizer\SyncFiles');
        $sync->sync();
    }

    private function validateSetting(Setting $setting)
    {
        if (isset($setting->validate)) {
            call_user_func($setting->validate, $setting->getValue(), $setting);
        }
    }
}
