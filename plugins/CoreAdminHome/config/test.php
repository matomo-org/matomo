<?php

use Piwik\Tracker\Request;
use Piwik\Tracker\Failures;

return array(

    'Piwik\Tracker\Failures' => Piwik\DI::decorate(function ($previous) {
        /** @var Failures $previous */

        $generate = \Piwik\Container\StaticContainer::get('test.vars.generateTrackingFailures');
        if ($generate) {
            $previous->setNow(\Piwik\Date::factory('2018-07-07 01:02:03'));
            $previous->logFailure(Failures::FAILURE_ID_INVALID_SITE, new Request(array(
                'idsite' => 998, 'rec' => '1'
            )));
            $previous->logFailure(Failures::FAILURE_ID_NOT_AUTHENTICATED, new Request(array(
                'idsite' => 1,
                'url' => 'https://www.example.com/foo/bar?x=1',
                'action_name' => 'foobar',
                'rec' => '1'
            )));
            $previous->logFailure(Failures::FAILURE_ID_INVALID_SITE, new Request(array(
                'idsite' => 999, 'rec' => '1'
            )));
        }

        return $previous;
    }),

);
