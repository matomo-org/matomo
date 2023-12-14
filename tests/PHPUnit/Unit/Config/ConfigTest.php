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
     */
    private function getBool(string $section, string $name): bool
    {
        return $this->config->$section[$name] == 1;
    }

    /**
     * @dataProvider getTestCases
     */
    public function testGetBool($expected, $setting)
    {
        $this->assertSame($expected, $this->getBool('BoolSettings', $setting));
    }

    public function getTestCases(): array
    {
        return [
            [true, 'one'],
            [true, 'onestr'],
            [true, 'truebool'],
            [true, 'isyes'],
            [true, 'ison'],
            [false, 'truestr'],
            [false, 'two'],
            [false, 'twostr'],
            [false, 'invalid'],
            [false, 'oneinstr'],
            [false, 'twoinstr'],
            [false, 'isoff'],
            [false, 'isno'],
        ];
    }
}
