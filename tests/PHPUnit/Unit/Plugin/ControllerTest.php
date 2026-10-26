<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugin\Controller;
use ReflectionMethod;

/**
 * @group Core
 */
class ControllerTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $originalGeneralConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalGeneralConfig = Config::getInstance()->General;
    }

    public function tearDown(): void
    {
        Config::getInstance()->General = $this->originalGeneralConfig;

        parent::tearDown();
    }

    /**
     * @dataProvider getPhpVersionsForCriticalVulnerability
     */
    public function testIsPhpVersionAffectedByCriticalVulnerability(int $major, int $minor, int $release, bool $expected): void
    {
        $method = new ReflectionMethod(Controller::class, 'isPhpVersionAffectedByCriticalVulnerability');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke(null, $major, $minor, $release));
    }

    public function getPhpVersionsForCriticalVulnerability(): iterable
    {
        yield 'PHP 7.2 has no upstream fix' => [7, 2, 5, true];
        yield 'PHP 7.4 has no upstream fix' => [7, 4, 33, true];
        yield 'PHP 8.0 has no upstream fix' => [8, 0, 30, true];
        yield 'PHP 8.1 has no upstream fix' => [8, 1, 34, true];

        yield 'PHP 8.2 before fix' => [8, 2, 34, true];
        yield 'PHP 8.2 fixed release' => [8, 2, 35, false];
        yield 'PHP 8.2 after fix' => [8, 2, 36, false];

        yield 'PHP 8.3 before fix' => [8, 3, 35, true];
        yield 'PHP 8.3 fixed release' => [8, 3, 36, false];
        yield 'PHP 8.3 after fix' => [8, 3, 37, false];

        yield 'PHP 8.4 before fix' => [8, 4, 26, true];
        yield 'PHP 8.4 fixed release' => [8, 4, 27, false];
        yield 'PHP 8.4 after fix' => [8, 4, 28, false];

        yield 'PHP 8.5 before fix' => [8, 5, 11, true];
        yield 'PHP 8.5 fixed release' => [8, 5, 12, false];
        yield 'PHP 8.5 after fix' => [8, 5, 13, false];

        yield 'PHP 8.6 not affected' => [8, 6, 0, false];
        yield 'PHP 9.0 not affected' => [9, 0, 0, false];
    }

    /**
     * @dataProvider getPhpCriticalVulnerabilityNotificationConfigValues
     * @param mixed $configValue null removes the setting
     */
    public function testIsPhpCriticalVulnerabilityNotificationEnabled($configValue, bool $expected): void
    {
        $general = Config::getInstance()->General;
        unset($general['enable_php_critical_vulnerability_notification']);
        if ($configValue !== null) {
            $general['enable_php_critical_vulnerability_notification'] = $configValue;
        }
        Config::getInstance()->General = $general;

        $method = new ReflectionMethod(Controller::class, 'isPhpCriticalVulnerabilityNotificationEnabled');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke(null));
    }

    public function getPhpCriticalVulnerabilityNotificationConfigValues(): iterable
    {
        yield 'enabled' => ['1', true];
        yield 'enabled with true' => ['true', true];
        yield 'disabled' => ['0', false];
        yield 'disabled with false' => ['false', false];
        // the INI reader turns an unquoted false, no or off into a real boolean
        yield 'disabled with boolean false' => [false, false];

        // a broken setting must never hide a security warning
        yield 'missing setting' => [null, true];
        yield 'invalid value' => ['nope', true];
    }
}
