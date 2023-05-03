<?php

return [
    'diagnostics.optional' => Piwik\DI::add([
        Piwik\DI::get(\Piwik\Plugins\MobileMessaging\Diagnostic\MobileMessagingInformational::class),
    ]),
];
