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
    public function testGetPropertyValueReturnsLightModeArrayValue()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);

        $this->assertSame('#43a047', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueReturnsDarkModeArrayValue()
    {
        $styles = new ThemeStyles(ThemeStyles::DARK_MODE);

        $this->assertSame('#778fd4', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueReturnsScalarStringValue()
    {
        $styles = new ThemeStyles(ThemeStyles::DARK_MODE);

        $this->assertSame('#f3f3f3', $styles->getPropertyValue('colorCode'));
    }

    public function testGetPropertyValueReturnsEmptyStringForUnknownProperty()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);

        $this->assertSame('', $styles->getPropertyValue('unknownProperty'));
    }

    public function testGetPropertyValueFallsBackToLightValueWhenDarkValueIsMissing()
    {
        $styles = new ThemeStyles(ThemeStyles::DARK_MODE);
        $styles->colorBrand = ['#123456'];

        $this->assertSame('#123456', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueFallsBackToDarkValueWhenLightValueIsMissing()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $styles->colorBrand = [1 => '#654321'];

        $this->assertSame('#654321', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueReturnsEmptyStringForNullOverride()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $styles->colorCode = null;

        $this->assertSame('', $styles->getPropertyValue('colorCode'));
    }

    public function testGetPropertyValueReturnsEmptyStringWhenArrayEntriesAreUnusable()
    {
        $styles = new ThemeStyles(ThemeStyles::DARK_MODE);
        $styles->colorBrand = [null, null];

        $this->assertSame('', $styles->getPropertyValue('colorBrand'));
    }

    public function testToLessCodeUsesModeSpecificValuesForValidArrays()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $lessCode = $styles->toLessCode();

        $this->assertStringContainsString('--theme-color-brand: #43a047;', $lessCode);
        $this->assertStringContainsString('[data-theme-mode="dark"] {' . "\n" . '    color-scheme: dark;' . "\n" . '    --theme-color-brand: #778fd4;', $lessCode);
    }

    public function testToLessCodeFallsBackToSharedValueWhenDarkValueIsMissing()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $styles->colorBrand = ['#123456'];
        $lessCode = $styles->toLessCode();

        $this->assertStringContainsString('--theme-color-brand: #123456;', $lessCode);
        $this->assertStringContainsString('[data-theme-mode="dark"] {' . "\n" . '    color-scheme: dark;' . "\n" . '    --theme-color-brand: #123456;', $lessCode);
    }

    public function testToLessCodeFallsBackToDarkValueWhenLightValueIsMissing()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $styles->colorBrand = [1 => '#654321'];
        $lessCode = $styles->toLessCode();

        $this->assertStringContainsString('--theme-color-brand: #654321;', $lessCode);
        $this->assertStringContainsString('[data-theme-mode="dark"] {' . "\n" . '    color-scheme: dark;' . "\n" . '    --theme-color-brand: #654321;', $lessCode);
    }

    public function testToLessCodeUsesEmptyStringWhenArrayEntriesAreUnusable()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $styles->colorBrand = [null, null];
        $lessCode = $styles->toLessCode();

        $this->assertStringContainsString('--theme-color-brand: ;', $lessCode);
        $this->assertStringContainsString('[data-theme-mode="dark"] {' . "\n" . '    color-scheme: dark;' . "\n" . '    --theme-color-brand: ;', $lessCode);
    }
}
