<?php

return array(
    'tracker.request.processors' => DI\add(array(
        DI\get('Piwik\Plugins\CustomVariables\Tracker\CustomVariablesRequestProcessor'),
    )),

    // in tests we do not use 'today' to make tests results deterministic
    'CustomVariables.today' => 'today',

);
