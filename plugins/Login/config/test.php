<?php
return array(
    'Piwik\Plugins\Login\SystemSettings' => DI\decorate(function ($settings, \Psr\Container\ContainerInterface $c) {
        /** @var \Piwik\Plugins\Login\SystemSettings $settings */

        \Piwik\Access::doAsSuperUser(function () use ($settings, $c) {
            if ($c->get('test.vars.bruteForceBlockIps')) {
                $settings->blacklistedBruteForceIps->setValue(array('10.2.3.4'));
            } elseif (\Piwik\SettingsPiwik::isMatomoInstalled()) {
                $settings->blacklistedBruteForceIps->setValue(array());
            }
        });

        return $settings;
    }),
    'Piwik\Plugins\Login\Security\BruteForceDetection' => DI\decorate(function ($detection, \Psr\Container\ContainerInterface $c) {
        /** @var \Piwik\Plugins\Login\Security\BruteForceDetection $detection */

        if ($c->get('test.vars.bruteForceBlockIps')) {
            for ($i = 0; $i < 30; $i++) {
                // we block a random IP
                $detection->addFailedAttempt('10.55.66.77');
            }
        } else if ($c->get('test.vars.bruteForceBlockThisIp')) {
            for ($i = 0; $i < 30; $i++) {
                // we block this IP
                $detection->addFailedAttempt(\Piwik\IP::getIpFromHeader());
            }
        } elseif (\Piwik\SettingsPiwik::isMatomoInstalled()) {
            // prevent tests from blocking other tests
            $detection->deleteAll();
        }

        return $detection;
    }),
);