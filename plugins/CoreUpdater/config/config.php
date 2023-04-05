<?php

return array(
    'Piwik\Plugins\CoreUpdater\Updater' => Piwik\DI::autowire()
        ->constructorParameter('tmpPath', Piwik\DI::get('path.tmp')),

    'diagnostics.optional' => Piwik\DI::add(array(
        Piwik\DI::get('Piwik\Plugins\CoreUpdater\Diagnostic\HttpsUpdateCheck'),
    )),
);
