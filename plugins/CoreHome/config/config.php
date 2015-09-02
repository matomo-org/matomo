<?php

return array(

    'Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor' => DI\object()
        ->constructorParameter('visitStandardLength', DI\get('ini.Tracker.visit_standard_length')),

    'tracker.request.processors' => DI\add(array(
        DI\get('Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor'),
    )),

);
