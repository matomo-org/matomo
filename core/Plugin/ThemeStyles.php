<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Piwik;

class ThemeStyles
{
    public const AUTO_MODE = 'auto';
    public const LIGHT_MODE = 'light';
    public const DARK_MODE = 'dark';

    // to maintain BC w/ old names that were defined in LESS
    private static $propertyNamesToLessVariableNames = [
        'fontFamilyBase' => 'theme-fontFamily-base',
        'colorBrand' => 'theme-color-brand',
        'colorBrandContrast' => 'theme-color-brand-contrast',
        'colorFocusRing' => 'theme-color-focus-ring',
        'colorFocusRingAlternative' => 'theme-color-focus-ring-alternative',
        'colorTextHighContrast' => 'theme-color-text-highContrast',
        'colorText' => 'theme-color-text',
        'colorTextContrast' => 'theme-color-text-contrast',
        'colorTextLight' => 'theme-color-text-light',
        'colorTextLighter' => 'theme-color-text-lighter',
        'colorTextOnDisabled' => 'theme-color-text-on-disabled',
        'colorTextInvert' => 'theme-color-text-invert',
        'colorTextInvertContrast' => 'theme-color-text-invert-contrast',
        'colorTextInvertLight' => 'theme-color-text-invert-light',
        'colorTextDisabled' => 'theme-color-text-disabled',
        'colorLink' => 'theme-color-link',
        'colorBaseSeries' => 'theme-color-base-series',
        'colorHeadlineAlternative' => 'theme-color-headline-alternative',
        'colorHeaderBackground' => 'theme-color-header-background',
        'colorHeaderText' => 'theme-color-header-text',
        'colorMenuContrastText' => 'theme-color-menu-contrast-text',
        'colorMenuContrastTextSelected' => 'theme-color-menu-contrast-textSelected',
        'colorMenuContrastTextActive' => 'theme-color-menu-contrast-textActive',
        'colorMenuContrastBackground' => 'theme-color-menu-contrast-background',
        'colorWidgetExportedBackgroundBase' => 'theme-color-widget-exported-background-base',
        'colorWidgetTitleText' => 'theme-color-widget-title-text',
        'colorWidgetTitleBackground' => 'theme-color-widget-title-background',
        'colorBackgroundBase' => 'theme-color-background-base',
        'colorBackgroundTinyContrast' => 'theme-color-background-tinyContrast',
        'colorBackgroundLowContrast' => 'theme-color-background-lowContrast',
        'colorBackgroundContrast' => 'theme-color-background-contrast',
        'colorBackgroundHighContrast' => 'theme-color-background-highContrast',
        'colorBackgroundDisabled' => 'theme-color-background-disabled',
        'colorBorder' => 'theme-color-border',
        'colorBorderLight' => 'theme-color-border-light',
        'colorBoxShadow' => 'theme-color-boxShadow',
        'colorCode' => 'theme-color-code',
        'colorCodeBackground' => 'theme-color-code-background',
        'colorWidgetBackground' => 'theme-color-widget-background',
        'colorWidgetBorder' => 'theme-color-widget-border',
        'filterOnIllustration' => 'theme-filter-on-illustration',
    ];

    /**
     * @var string
     */
    protected $themeMode = self::AUTO_MODE;

    /**
     * @var string|array<string>
     */
    public $fontFamilyBase = '-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Cantarell, \'Helvetica Neue\', sans-serif';

    /**
     * @var string|array<string>
     */
    public $colorBrand = ['#43a047', '#778fd4'];

    /**
     * @var string|array<string>
     */
    public $colorBrandContrast = ['#fff', '#ffffff'];

    /**
     * @var string|array<string>
     */
    public $colorFocusRing = '#0969da';

    /**
     * @var string|array<string>
     */
    public $colorFocusRingAlternative;

    /**
     * @var string|array<string>
     */
    public $colorTextHighContrast = ['#000', '#d9d9d9'];

    /**
     * @var string|array<string>
     */
    public $colorText = ['#212121', '#ccc'];

    /**
     * @var string|array<string>
     */
    public $colorTextContrast = ['#37474f', '#bbb'];

    /**
     * @var string|array<string>
     */
    public $colorTextLight = ['#444', '#aaa'];

    /**
     * @var string|array<string>
     */
    public $colorTextLighter = ['#666666', '#999'];

    /**
     * @var string|array<string>
     */
    public $colorTextOnDisabled = ['#666666', '#999'];

    /**
     * @var string|array<string>
     */
    public $colorTextInvert = ['#ccc', '#555'];

    /**
     * @var string|array<string>
     */
    public $colorTextInvertContrast = ['#fff', '#000'];

    /**
     * @var string|array<string>
     */
    public $colorTextInvertLight = ['#b9b9b9', '#666'];

    /**
     * @var string|array<string>
     */
    public $colorTextDisabled = ['#aaa', '#666'];

    /**
     * @var string|array<string>
     */
    public $colorLink = ['#1976D2', '#778fd4'];

    /**
     * @var string|array<string>
     */
    public $colorBaseSeries = '#ee3024';

    /**
     * @var string|array<string>
     */
    public $colorHeadlineAlternative = ['#4E4E4E', '#aaa'];

    /**
     * @var string|array<string>
     */
    public $colorHeaderBackground = ['#3450A3', '#2b3138'];

    /**
     * @var string|array<string>
     */
    public $colorHeaderText =  ['#fff', '#ccc'];

    /**
     * @var string|array<string>
     */
    public $colorMenuContrastText;

    /**
     * @var string|array<string>
     */
    public $colorMenuContrastTextSelected;

    /**
     * @var string|array<string>
     */
    public $colorMenuContrastTextActive = ['#3450A3', '#fff'];

    /**
     * @var string|array<string>
     */
    public $colorMenuContrastBackground;

    /**
     * @var string|array<string>
     */
    public $colorWidgetExportedBackgroundBase;

    /**
     * @var string|array<string>
     */
    public $colorWidgetTitleText;

    /**
     * @var string|array<string>
     */
    public $colorWidgetTitleBackground;

    /**
     * @var string|array<string>
     */
    public $colorBackgroundBase = ['#f5f5f5', '#151819'];

    /**
     * @var string|array<string>
     */
    public $colorBackgroundTinyContrast = ['#f2f2f2', '#182c32'];

    /**
     * @var string|array<string>
     */
    public $colorBackgroundLowContrast = ['#d9d9d9', '#192d33'];

    /**
     * @var string|array<string>
     */
    public $colorBackgroundContrast = ['#fff', '#202329'];

    /**
     * @var string|array<string>
     */
    public $colorBackgroundHighContrast = ['#202020', '#404349'];

    /**
     * @var string|array<string>
     */
    public $colorBackgroundDisabled = ['#d9d9d9', '#303339'];

    /**
     * @var string|array<string>
     */
    public $colorBorderLight = ['#a9a399', '#645e54'];

    /**
     * @var string|array<string>
     */
    public $colorBorder = ['#cccccc', '#555555'];

    /**
     * @var string|array<string>
     */
    public $colorBoxShadow = ['rgba(0, 0, 0, 0.1)', 'rgba(0, 0, 0, 0.1)'];

    /**
     * @var string|array<string>
     */
    public $colorCode = '#f3f3f3';

    /**
     * @var string|array<string>
     */
    public $colorCodeBackground = '#4d4d4d';

    /**
     * @var string|array<string>
     */
    public $colorWidgetBackground;

    /**
     * @var string|array<string>
     */
    public $colorWidgetBorder;

    /**
     * @var string|array<string>
     */
    public $filterOnIllustration = ['none', 'brightness(89%) invert(100%) hue-rotate(180deg)'];

    public function __construct(string $themeMode)
    {
        $this->themeMode = $themeMode;
        $this->colorFocusRingAlternative = $this->colorBrand;
        $this->colorMenuContrastText = $this->colorText;
        $this->colorMenuContrastTextSelected = $this->colorMenuContrastText;
        $this->colorMenuContrastBackground = $this->colorBackgroundContrast;
        $this->colorWidgetExportedBackgroundBase = $this->colorBackgroundContrast;
        $this->colorWidgetTitleText = $this->colorText;
        $this->colorWidgetTitleBackground = $this->colorBackgroundContrast;
        $this->colorWidgetBackground = $this->colorBackgroundContrast;
        $this->colorWidgetBorder = $this->colorBackgroundTinyContrast;
    }

    /**
     * @return ThemeStyles
     */
    public static function get(string $mode = self::AUTO_MODE)
    {
        $result = new self($mode);

        /**
         * @ignore
         */
        Piwik::postEvent('Theme.configureThemeVariables', [$result]);

        return $result;
    }

    public function getIsLightMode(): bool
    {
        return $this->themeMode === self::LIGHT_MODE;
    }

    public function getIsDarkMode(): bool
    {
        return $this->themeMode === self::DARK_MODE;
    }

    public function getThemeMode(): string
    {
        return $this->themeMode;
    }

    public function getPropertyValue(string $name): string
    {
        if (!property_exists($this, $name)) {
            return '';
        }

        return $this->resolvePropertyValue($this->$name, $this->getIsDarkMode() ? 1 : 0);
    }

    public function toLessCode()
    {
        $rootCssVars = [];
        $darkCssVars = [];

        foreach (get_object_vars($this) as $name => $value) {
            $varName = isset(self::$propertyNamesToLessVariableNames[$name]) ? self::$propertyNamesToLessVariableNames[$name] : $this->getGenericThemeVarName($name);
            if (is_array($value)) {
                $rootCssVars[] = "    --$varName: " . $this->resolvePropertyValue($value, 0) . ";\n";
                $darkCssVars[] = "    --$varName: " . $this->resolvePropertyValue($value, 1) . ";\n";
            } else {
                $rootCssVars[] = "    --$varName: " . $this->resolvePropertyValue($value, 0) . ";\n";
            }
        }

        $result = ":root {\n    color-scheme: light;\n" . implode('', $rootCssVars) . "}\n\n";
        if (!empty($darkCssVars)) {
            $result .= "[data-theme-mode=\"dark\"] {\n    color-scheme: dark;\n" . implode('', $darkCssVars) . "}\n\n";
            $result .= "@media (prefers-color-scheme: dark) {\n";
            $result .= "    [data-theme-mode=\"auto\"] {\n        color-scheme: dark;\n" . implode('', $darkCssVars) . "    }\n";
            $result .= "}\n\n";
        }

        foreach (get_object_vars($this) as $name => $_) {
            $varName = isset(self::$propertyNamesToLessVariableNames[$name]) ? self::$propertyNamesToLessVariableNames[$name] : $this->getGenericThemeVarName($name);
            $result .= "@$varName: ~\"var(--$varName)\";\n";
        }
        return $result;
    }

    private function getGenericThemeVarName($propertyName)
    {
        return 'theme-' . $propertyName;
    }

    /**
     * @param mixed $value
     */
    private function resolvePropertyValue($value, int $preferredIndex): string
    {
        if (!is_array($value)) {
            return is_string($value) ? $value : '';
        }

        $fallbackIndex = $preferredIndex === 1 ? 0 : 1;

        if (isset($value[$preferredIndex]) && is_string($value[$preferredIndex])) {
            return $value[$preferredIndex];
        }

        if (isset($value[$fallbackIndex]) && is_string($value[$fallbackIndex])) {
            return $value[$fallbackIndex];
        }

        return '';
    }
}
