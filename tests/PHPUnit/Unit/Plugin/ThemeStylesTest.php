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

        $this->assertSame('#00b4be', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueReturnsDarkModeArrayValue()
    {
        $styles = new ThemeStyles(ThemeStyles::DARK_MODE);

        $this->assertSame('#00d7e3', $styles->getPropertyValue('colorBrand'));
    }

    public function testGetPropertyValueReturnsSuccessGreenIndependentlyOfBrand()
    {
        $light = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $dark = new ThemeStyles(ThemeStyles::DARK_MODE);

        $this->assertSame('#43a047', $light->getPropertyValue('colorSuccess'));
        $this->assertSame('#66bb6a', $dark->getPropertyValue('colorSuccess'));
    }

    public function testGetPropertyValueReturnsPositiveNegativeAndNeutralColors()
    {
        $light = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $dark = new ThemeStyles(ThemeStyles::DARK_MODE);

        $this->assertSame('#33B343', $light->getPropertyValue('colorPositive'));
        $this->assertSame('#4dcc5d', $dark->getPropertyValue('colorPositive'));
        $this->assertSame('#ee3024', $light->getPropertyValue('colorNegative'));
        $this->assertSame('#ee3024', $dark->getPropertyValue('colorNegative'));
        $this->assertSame($light->getPropertyValue('colorTextLighter'), $light->getPropertyValue('colorNeutral'));
        $this->assertSame($dark->getPropertyValue('colorTextLighter'), $dark->getPropertyValue('colorNeutral'));
    }

    public function testToLessCodeExposesPositiveNegativeAndNeutralColors()
    {
        $lessCode = (new ThemeStyles(ThemeStyles::LIGHT_MODE))->toLessCode();

        $this->assertStringContainsString('--theme-color-positive: #33B343;', $lessCode);
        $this->assertStringContainsString('--theme-color-negative: #ee3024;', $lessCode);
        $this->assertStringContainsString('--theme-color-neutral: #646464;', $lessCode);
        $this->assertStringContainsString('@theme-color-positive: ~"var(--theme-color-positive)";', $lessCode);
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

        $this->assertStringContainsString('--theme-color-brand: #00b4be;', $lessCode);
        $this->assertStringContainsString('[data-theme-mode="dark"] {' . "\n" . '    color-scheme: dark;' . "\n" . '    --theme-color-brand: #00d7e3;', $lessCode);
        $this->assertStringContainsString('--theme-color-success: #43a047;', $lessCode);
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

    public function testToLessCodeKeepsDeprecatedBorderAlternativeAsAliasOfBorder()
    {
        $styles = new ThemeStyles(ThemeStyles::LIGHT_MODE);
        $lessCode = $styles->toLessCode();
        [$rootBlock, $darkBlock] = explode('[data-theme-mode="dark"]', $lessCode, 2);

        $this->assertStringContainsString('--theme-color-border: #e0e0e0;', $rootBlock);
        $this->assertStringContainsString('--theme-color-border: #555555;', $darkBlock);
        $this->assertStringContainsString('--theme-color-border-alternative: #e0e0e0;', $rootBlock);
        $this->assertStringContainsString('--theme-color-border-alternative: #555555;', $darkBlock);
        $this->assertStringContainsString('@theme-color-border-alternative: ~"var(--theme-color-border-alternative)";', $lessCode);
    }
}
