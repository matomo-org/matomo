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
        'colorBorder' => 'theme-color-border',
        'colorBorderLight' => 'theme-color-border-light',
        'colorCode' => 'theme-color-code',
        'colorCodeBackground' => 'theme-color-code-background',
        'colorWidgetBackground' => 'theme-color-widget-background',
        'colorWidgetBorder' => 'theme-color-widget-border',
        'filterOnIllustration' => 'theme-filter-on-illustration',
    ];

    /**
     * @var string
     */
    public $themeMode = 'default';

    /**
     * @var array<string>
     */
    public $themeModeList = ['default', 'dark'];

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
    public $colorTextInvert = ['#ccc', '#212121'];

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
    public $colorTextDisabled = ['#d3d3d3', '#666'];

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
    public $colorHeadlineAlternative = '#4E4E4E';

    /**
     * @var string|array<string>
     */
    public $colorHeaderBackground = ['#3450A3', '#2b3138'];

    /**
     * @var string|array<string>
     */
    public $colorHeaderText =  ['#fff', '#202329'];

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
    public $colorBackgroundBase = ['#eff0f1', '#151819'];

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
    public $colorBackgroundHighContrast = ['#202020', '#000'];

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
    public static function get(string $mode = 'default')
    {
        $result = new self($mode);

        /**
         * @ignore
         */
        Piwik::postEvent('Theme.configureThemeVariables', [$result]);

        return $result;
    }

    public function getThemeModeIndex(): int
    {
        return array_search($this->themeMode, $this->themeModeList) ?: 0;
    }

    public function getPropertyValue(string $name): string
    {
        $value = $this->$name;
        if (!is_array($value)) {
            return $value;
        }
        $index = $this->getThemeModeIndex();

        return $value[$index] ?? $value[0];
    }

    public function toLessCode()
    {
        $result = '';
        $index = $this->getThemeModeIndex();
        foreach (get_object_vars($this) as $name => $value) {
            if (is_array($value)) {
                $value = $value[$index] ?? $value[0];
            }
            $varName = isset(self::$propertyNamesToLessVariableNames[$name]) ? self::$propertyNamesToLessVariableNames[$name] : $this->getGenericThemeVarName($name);
            $result .= "@$varName: $value;\n";
        }
        return $result;
    }

    private function getGenericThemeVarName($propertyName)
    {
        return 'theme-' . $propertyName;
    }
}
