<?php

return array(

    'diagnostics.informational' => Piwik\DI::add(array(
        Piwik\DI::get('Piwik\Plugins\PrivacyManager\Diagnostic\PrivacyInformational'),
    )),
);
