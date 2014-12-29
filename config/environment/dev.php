<?php

return array(

    // Disable translation cache
    'Piwik\Translation\Loader\LoaderInterface' => DI\object('Piwik\Translation\Loader\JsonFileLoader'),

);
