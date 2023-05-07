<?php

return array(
    'diagnostics.optional' => Piwik\DI::add(array(
        Piwik\DI::get('Piwik\Plugins\CustomJsTracker\Diagnostic\TrackerJsCheck'),
    )),
);
