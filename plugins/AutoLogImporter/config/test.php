<?php

return array(

    'Piwik\Plugins\AutoLogImporter\Settings' => DI\decorate(function (\Piwik\Plugins\AutoLogImporter\Settings $settings) {
        if ($settings->enabled->isWritableByCurrentUser()) {
            $path = PIWIK_INCLUDE_PATH;
            if (\Piwik\Common::stringEndsWith($path, '/')) {
                $path = substr($path, 0, -1);
            }
            $settings->enabled->setValue(true);
            $settings->logFilesPath->setValue($path . '/plugins/AutoLogImporter/tests/resources');

            if (!empty($_GET['no_autologimporter_match'])) {
                $settings->filePattern->setValue('*.nothing');
            } else {
                $settings->filePattern->setValue('*.log');
            }
        }

        return $settings;
    }),

    'AutoLogImporter.logImportOptions' => array('--token-auth=9ad1de7f8b329ab919d854c556f860c1'),
    'AutoLogImporter.currentTimestamp' => '1440592473'
);
