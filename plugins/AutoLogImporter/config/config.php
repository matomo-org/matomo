<?php

return array(

    'AutoLogImporter.logImportOptions' => array(),

    'AutoLogImporter.currentTimestamp' => DI\factory(function () {
        return \Piwik\Date::now()->getTimestamp();
    }),
);
