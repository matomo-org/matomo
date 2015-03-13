<?php

return array(
    'Piwik\Plugins\CoreUpdater\Updater' => DI\object()
        ->constructorParameter('tmpPath', DI\link('path.tmp')),
);
