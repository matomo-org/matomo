<?php

return array(

    'Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor' => DI\autowire()
        ->constructorParameter('visitStandardLength', DI\get('ini.Tracker.visit_standard_length'))
        ->constructorParameter('trackerAlwaysNewVisitor', DI\get('ini.Debug.tracker_always_new_visitor')),

);
