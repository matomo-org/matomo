<?php

namespace Piwik\Tests\Unit\Config;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Config\ConfigNotFoundException;
use PHPUnit\Framework\TestCase;
use Piwik\Container\StaticContainer;

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

        $this->assertTrue($this->getBool('BoolSettings', 'one'));
        $this->assertTrue($this->getBool('BoolSettings', 'onestr'));
        $this->assertTrue($this->getBool('BoolSettings', 'truebool'));
        $this->assertTrue($this->getBool('BoolSettings', 'isyes'));
        $this->assertTrue($this->getBool('BoolSettings', 'ison'));
    }

    public function testGetBool_false()
    {
        $this->assertFalse($this->getBool('BoolSettings', 'truestr'));
        $this->assertFalse($this->getBool('BoolSettings', 'two'));
        $this->assertFalse($this->getBool('BoolSettings', 'twostr'));
        $this->assertFalse($this->getBool('BoolSettings', 'invalid'));
        $this->assertFalse($this->getBool('BoolSettings', 'oneinstr'));
        $this->assertFalse($this->getBool('BoolSettings', 'twoinstr'));
        $this->assertFalse($this->getBool('BoolSettings', 'isoff'));
        $this->assertFalse($this->getBool('BoolSettings', 'isno'));
    }

    /**
     * Returns a boolean variable setting for convenience
     * when calling e.g. getBool('General', 'force_ssl')
     * This also documents that a boolean is only true if
     * it is equal to 1.
     * @see https://github.com/matomo-org/matomo/pull/17865
     * return 1 === $value || '1' === $value || true === $value; // was suggested for future use by @sgiehl
     *
     * @param string $section Configuration section
     * @param string $name variable name
     * @return bool whether it is considered set true (== 1)
     *
     * @internal
     * @throws ConfigNotFoundException
     */
    private function getBool(string $section, string $name): bool
    {
        return $this->config->$section[$name] == 1;
    }
}
