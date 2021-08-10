<?php

namespace Piwik\Tests\Unit\Config;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    private $config;

    protected function setUp(): void
    {
        $userSettingsPath = __DIR__ . '/test_files/boolean_settings_test.ini.php';
        $settingsProvider = new GlobalSettingsProvider([], $userSettingsPath);
        $this->config = new Config($settingsProvider);
    }

    public function testGetBool_true()
    {
        $config = $this->config;

        $this->assertTrue($config->getBool('BoolSettings', 'one'));
        $this->assertTrue($config->getBool('BoolSettings', 'onestr'));
        $this->assertTrue($config->getBool('BoolSettings', 'truebool'));
        $this->assertTrue($config->getBool('BoolSettings', 'isyes'));
        $this->assertTrue($config->getBool('BoolSettings', 'ison'));
    }

    public function testGetBool_false()
    {
        $config = $this->config;

        $this->assertFalse($config->getBool('BoolSettings', 'truestr'));
        $this->assertFalse($config->getBool('BoolSettings', 'two'));
        $this->assertFalse($config->getBool('BoolSettings', 'twostr'));
        $this->assertFalse($config->getBool('BoolSettings', 'invalid'));
        $this->assertFalse($config->getBool('BoolSettings', 'oneinstr'));
        $this->assertFalse($config->getBool('BoolSettings', 'twoinstr'));
        $this->assertFalse($config->getBool('BoolSettings', 'isoff'));
        $this->assertFalse($config->getBool('BoolSettings', 'isno'));
        $this->assertFalse($config->getBool('BoolSettings', 'truestr'));
    }
}
