<?php
return array(

    'diagnostics.informational' => DI\add(array(
        DI\get('Piwik\Plugins\PrivacyManager\Diagnostic\PrivacyInformational'),
    )),
);
