<?php

use Matomo\Dependencies\DI;

return array(
    'Piwik\Intl\Data\Provider\DateTimeFormatProvider' => DI\autowire('Piwik\Plugins\Intl\DateTimeFormatProvider')
);