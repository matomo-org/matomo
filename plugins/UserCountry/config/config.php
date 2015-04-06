<?php

return array(
    'diagnostics.optional' => DI\add(array(
        DI\link('Piwik\Plugins\UserCountry\Diagnostic\GeolocationDiagnostic'),
    )),
);
