<?php

return array(
    'Piwik\Plugins\CoreUpdater\Updater' => DI\object()
        ->constructorParameter('tmpPath', DI\get('path.tmp')),
);
