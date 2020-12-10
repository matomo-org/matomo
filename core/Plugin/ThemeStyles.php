<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
        'colorText' => 'theme-color-text',
        'colorTextLight' => 'theme-color-text-light',
        'colorTextLighter' => 'theme-color-text-lighter',
        'colorTextContrast' => 'theme-color-text-contrast',
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
        'colorCode' => 'theme-color-code',
        'colorCodeBackground' => 'theme-color-code-background',
        'colorWidgetBackground' => 'theme-color-widget-background',
        'colorWidgetBorder' => 'theme-color-widget-border',
    ];

    /**
     * @var string
     */
    public $fontFamilyBase = '-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Cantarell, \'Helvetica Neue\', sans-serif';

    /**
     * @var string
     */
    public $colorBrand = '#43a047';

    /**
     * @var string
     */
    public $colorBrandContrast = '#fff';

    /**
     * @var string
     */
    public $colorText = '#212121';

    /**
     * @var string
     */
    public $colorTextLight = '#444';

    /**
     * @var string
     */
    public $colorTextLighter = '#666666';

    /**
     * @var string
     */
    public $colorTextContrast = '#37474f';

    /**
     * @var string
     */
    public $colorLink = '#1976D2';

    /**
     * @var string
     */
    public $colorBaseSeries = '#ee3024';

    /**
     * @var string
     */
    public $colorHeadlineAlternative = '#4E4E4E';

    /**
     * @var string
     */
    public $colorHeaderBackground = '#3450A3';

    /**
     * @var string
     */
    public $colorHeaderText =  '#fff';

    /**
     * @var string
     */
    public $colorMenuContrastText;

    /**
     * @var string
     */
    public $colorMenuContrastTextSelected;

    /**
     * @var string
     */
    public $colorMenuContrastTextActive = '#3450A3';

    /**
     * @var string
     */
    public $colorMenuContrastBackground;

    /**
     * @var string
     */
    public $colorWidgetExportedBackgroundBase;

    /**
     * @var string
     */
    public $colorWidgetTitleText;

    /**
     * @var string
     */
    public $colorWidgetTitleBackground;

    /**
     * @var string
     */
    public $colorBackgroundBase = '#eff0f1';

    /**
     * @var string
     */
    public $colorBackgroundTinyContrast = '#f2f2f2';

    /**
     * @var string
     */
    public $colorBackgroundLowContrast = '#d9d9d9';

    /**
     * @var string
     */
    public $colorBackgroundContrast = '#fff';

    /**
     * @var string
     */
    public $colorBackgroundHighContrast = '#202020';

    /**
     * @var string
     */
    public $colorBorder = '#cccccc';

    /**
     * @var string
     */
    public $colorCode = '#f3f3f3';

    /**
     * @var string
     */
    public $colorCodeBackground = '#4d4d4d';

    /**
     * @var string
     */
    public $colorWidgetBackground;

    /**
     * @var string
     */
    public $colorWidgetBorder;

    public function __construct()
    {
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
    public static function get()
    {
        $result = new self();

        /**
         * @ignore
         */
        Piwik::postEvent('Theme.configureThemeVariables', [$result]);

        return $result;
    }

    public function toLessCode()
    {
        $result = '';
        foreach (get_object_vars($this) as $name => $value) {
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
