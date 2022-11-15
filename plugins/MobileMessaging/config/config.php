<?php

use Matomo\Dependencies\DI;

return [
    'diagnostics.optional' => DI\add([
        DI\get(\Piwik\Plugins\MobileMessaging\Diagnostic\MobileMessagingInformational::class),
    ]),
];
