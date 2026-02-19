<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\ThemeStyles;

/**
 * @group Core
 */
class ThemeStylesTest extends TestCase
{
    public function testToLessCodeShouldNotEmitModeSwitchBlocksIfNoDarkOverridesExist()
    {
        $themeStyles = new ThemeStyles(ThemeStyles::AUTO_MODE);

        foreach (get_object_vars($themeStyles) as $name => $value) {
            if ($name === 'themeMode') {
                continue;
            }

            if (is_array($value)) {
                $themeStyles->$name = $value[0];
            }
        }

        $less = $themeStyles->toLessCode();

        $this->assertStringNotContainsString('html[data-theme-mode="dark"]', $less);
        $this->assertStringNotContainsString('html[data-theme-mode="auto"]', $less);
        $this->assertStringNotContainsString('prefers-color-scheme: dark', $less);
    }

    public function testToLessCodeShouldEmitModeSwitchBlocksIfDarkOverridesExist()
    {
        $themeStyles = new ThemeStyles(ThemeStyles::AUTO_MODE);
        $less = $themeStyles->toLessCode();

        $this->assertStringContainsString('html[data-theme-mode="dark"]', $less);
        $this->assertStringContainsString('html[data-theme-mode="auto"]', $less);
        $this->assertStringContainsString('prefers-color-scheme: dark', $less);
    }
}
