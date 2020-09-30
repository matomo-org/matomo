<?php

return array(
    'observers.global' => DI\add(array(
        array('API.Tour.getChallenges.end', DI\value(function (&$challenges) {
            $completeAllChanges = \Piwik\Container\StaticContainer::get('test.vars.completeAllChallenges');
            if ($completeAllChanges) {
                foreach ($challenges as &$challenge) {
                    $challenge['isSkipped'] = true;
                    $challenge['isCompleted'] = true;
                }
            }
            $completeNoChallenge = \Piwik\Container\StaticContainer::get('test.vars.completeNoChallenge');
            if ($completeNoChallenge) {
                foreach ($challenges as &$challenge) {
                    $challenge['isSkipped'] = false;
                    $challenge['isCompleted'] = false;
                }
            }
        })),
    ))
);
