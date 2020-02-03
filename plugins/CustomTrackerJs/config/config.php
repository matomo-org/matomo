<?php

return array(
    'diagnostics.optional' => DI\add(array(
        DI\get('Piwik\Plugins\CustomTrackerJs\Diagnostic\TrackerJsCheck'),
    )),
);
