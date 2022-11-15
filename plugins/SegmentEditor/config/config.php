<?php

use Matomo\Dependencies\DI;

return array(

    'Piwik\DataAccess\LogQueryBuilder' => DI\get('Piwik\Plugins\SegmentEditor\SegmentQueryDecorator'),

);
