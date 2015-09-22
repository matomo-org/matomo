<?php

return array(

    'FileSynchronizer.currentTimestamp' => DI\factory(function () {
        return \Piwik\Date::now()->getTimestamp();
    }),
);
