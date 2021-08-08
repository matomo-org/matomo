<?php

namespace Piwik\Tests\Unit\Config;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function testGetBool()
    {
        $userSettingsPath = __DIR__ . '/test_files/boolean_settings_test.ini.php';
        $settingsProvider = new GlobalSettingsProvider([], $userSettingsPath);
        $config = new Config($settingsProvider);

        $this->assertTrue($config->getBool('BoolSettings', 'one'));
        $this->assertTrue($config->getBool('BoolSettings', 'onestr'));
        $this->assertTrue($config->getBool('BoolSettings', 'truebool'));
        $this->assertFalse($config->getBool('BoolSettings', 'truestr'));
        $this->assertFalse($config->getBool('BoolSettings', 'two'));
        $this->assertFalse($config->getBool('BoolSettings', 'twostr'));
        $this->assertFalse($config->getBool('BoolSettings', 'invalid'));
    }
}
