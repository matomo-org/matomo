<?php

return [
    'diagnostics.informational' => Piwik\DI::add(array(
        // adds an informational system check for the database user create permission
        Piwik\DI::get('Piwik\Plugins\VisitFrequency\Diagnostic\CreatePermissionCheck'),
    ))
];
