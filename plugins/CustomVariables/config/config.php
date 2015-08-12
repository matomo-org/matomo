<?php

return array(

    'tracker.request.processors' => DI\add(array(
        DI\get('Piwik\Plugins\CustomVariables\Tracker\CustomVariablesRequestProcessor'),
    )),

);
