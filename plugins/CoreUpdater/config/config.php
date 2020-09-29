<?php

return array(
    'Piwik\Plugins\CoreUpdater\Updater' => DI\autowire()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),

    'diagnostics.optional' => DI\add(array(
        DI\get('Piwik\Plugins\CoreUpdater\Diagnostic\HttpsUpdateCheck'),
    )),
);
