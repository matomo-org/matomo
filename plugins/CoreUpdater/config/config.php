<?php

return array(
    'Piwik\Plugins\CoreUpdater\Updater' => DI\object()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),

    'diagnostics.optional' => DI\add(array(
        DI\link('Piwik\Plugins\CoreUpdater\Diagnostic\HttpsUpdateCheck'),
    )),
);
