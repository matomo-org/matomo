<?php

use Matomo\Dependencies\DI;

return array(

    'Piwik\Plugins\DBStats\MySQLMetadataDataAccess' => DI\create('Piwik\Plugins\DBStats\tests\Mocks\MockDataAccess'),

);