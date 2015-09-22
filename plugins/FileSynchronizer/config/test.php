<?php

return array(

    'Piwik\Plugins\FileSynchronizer\Settings' => DI\decorate(function (\Piwik\Plugins\FileSynchronizer\Settings $settings) {
        if ($settings->enabled->isWritableByCurrentUser()) {
            $path = PIWIK_INCLUDE_PATH;
            if (\Piwik\Common::stringEndsWith($path, '/')) {
                $path = substr($path, 0, -1);
            }
            $settings->enabled->setValue(true);
            $settings->filePattern->setValue('*.log');
            $settings->sourceDirectory->setValue($path . '/plugins/FileSynchronizer/tests/resources/source');
            $settings->targetDirectory->setValue($path . '/plugins/FileSynchronizer/tests/resources/target');
            $settings->targetFilenameTemplate->setValue('$basename_$filename_idsite_1_.$extension');
        }

        return $settings;
    }),

    'FileSynchronizer.currentTimestamp' => '1440592473'
);
