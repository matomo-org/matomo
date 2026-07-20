<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Unit;

use Piwik\Auth\PasswordStrength;
use Piwik\Plugin\ThemeStyles;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\Controller;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Translation\Translator;

/**
 * @group UsersManager
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Controller
     */
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(
            $this->createMock(Translator::class),
            $this->createMock(PasswordVerifier::class),
            $this->createMock(Model::class),
            $this->createMock(PasswordStrength::class)
        );
    }

    /**
     * @dataProvider provideValidThemeModes
     */
    public function testGetValidatedThemeModeShouldAcceptKnownModes(string $themeMode)
    {
        $method = new \ReflectionMethod($this->controller, 'getValidatedThemeMode');
        $method->setAccessible(true);

        $this->assertSame($themeMode, $method->invoke($this->controller, $themeMode));
    }

    public function testGetValidatedThemeModeShouldRejectUnknownMode()
    {
        $method = new \ReflectionMethod($this->controller, 'getValidatedThemeMode');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme mode');

        $method->invoke($this->controller, 'invalid');
    }

    public function provideValidThemeModes()
    {
        return [
            [ThemeStyles::AUTO_MODE],
            [ThemeStyles::LIGHT_MODE],
            [ThemeStyles::DARK_MODE],
        ];
    }
}
