<?php

use Piwik\Container\StaticContainer;
use Piwik\Auth\PasswordStrength;

return [
    'Piwik\Auth' => Piwik\DI::create('Piwik\Plugins\Login\Auth'),
    'Piwik\Auth\PasswordStrength' => Piwik\DI::factory(function () {
        $settings = StaticContainer::get('Piwik\Plugins\Login\SystemSettings');
        $featureEnabled = $settings->enablePasswordStrengthCheck->getValue();
        return new PasswordStrength($featureEnabled);
    }),
];
