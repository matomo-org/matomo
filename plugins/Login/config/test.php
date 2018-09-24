<?php
return array(
    'Piwik\Plugins\Login\SystemSettings' => DI\decorate(function ($settings, \Interop\Container\ContainerInterface $c) {
        /** @var \Piwik\Plugins\Login\SystemSettings $settings */

        if ($c->get('test.vars.bruteForceBlockIps')) {
            $settings->blacklistedBruteForceIps->setValue(array('10.2.3.4'));
        } else {
            $settings->blacklistedBruteForceIps->setValue(array());
        }

        return $settings;
    }),
    'Piwik\Plugins\Login\Security\BruteForceDetection' => DI\decorate(function ($detection, \Interop\Container\ContainerInterface $c) {
        /** @var \Piwik\Plugins\Login\Security\BruteForceDetection $detection */

        if ($c->get('test.vars.bruteForceBlockIps')) {
            for ($i = 0; $i < 30; $i++) {
                // we block a random IP
                $detection->addFailedLoginAttempt('10.55.66.77');
            }
        } else if ($c->get('test.vars.bruteForceBlockThisIp')) {
            for ($i = 0; $i < 30; $i++) {
                // we block this IP
                $detection->addFailedLoginAttempt(\Piwik\IP::getIpFromHeader());
            }
        }

        return $detection;
    }),
);