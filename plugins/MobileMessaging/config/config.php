<?php

return [
    'diagnostics.optional' => DI\add([
        DI\get(\Piwik\Plugins\MobileMessaging\Diagnostic\MobileMessagingInformational::class),
    ]),
];
