<?php

return array(
    'diagnostics.optional' => Piwik\DI::add(array(
        Piwik\DI::get('Piwik\Plugins\UserCountry\Diagnostic\GeolocationDiagnostic'),
    )),
);
