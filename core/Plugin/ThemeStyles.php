<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Piwik;

/**
 * Holds the theme variables. An instance is passed to the `Theme.configureThemeVariables` event and
 * its public properties are the theme variables a theme can override.
 *
 * @api
 */
class ThemeStyles
{
    /**
     * Theme mode that follows the user's system color scheme.
     *
     * @internal
     */
    public const AUTO_MODE = 'auto';
    /**
     * Theme mode that always uses the light variants.
     *
     * @internal
     */
    public const LIGHT_MODE = 'light';
    /**
     * Theme mode that always uses the dark variants.
     *
     * @internal
     */
    public const DARK_MODE = 'dark';

    // to maintain BC w/ old names that were defined in LESS
    private static $propertyNamesToLessVariableNames = [
        'fontFamilyBase' => 'theme-fontFamily-base',
        'colorBrand' => 'theme-color-brand',
        'colorBrandContrast' => 'theme-color-brand-contrast',
        'colorSuccess' => 'theme-color-success',
        'colorWarning' => 'theme-color-warning',
        'colorDanger' => 'theme-color-danger',
        'colorPositive' => 'theme-color-positive',
        'colorNegative' => 'theme-color-negative',
        'colorNeutral' => 'theme-color-neutral',
        'colorFocusRing' => 'theme-color-focus-ring',
        'colorFocusRingAlternative' => 'theme-color-focus-ring-alternative',
        'colorTextHighContrast' => 'theme-color-text-highContrast',
        'colorText' => 'theme-color-text',
        'colorTextContrast' => 'theme-color-text-contrast',
        'colorTextLight' => 'theme-color-text-light',
        'colorTextLighter' => 'theme-color-text-lighter',
        'colorTextOnDisabled' => 'theme-color-text-on-disabled',
        'colorTextPlaceholder' => 'theme-color-text-placeholder',
        'colorTextInvert' => 'theme-color-text-invert',
        'colorTextInvertContrast' => 'theme-color-text-invert-contrast',
        'colorTextInvertLight' => 'theme-color-text-invert-light',
        'colorTextDisabled' => 'theme-color-text-disabled',
        'colorTextBrand' => 'theme-color-text-brand',
        'colorLink' => 'theme-color-link',
        'colorBaseSeries' => 'theme-color-base-series',
        'colorHeadlineAlternative' => 'theme-color-headline-alternative',
        'colorHeaderBackground' => 'theme-color-header-background',
        'colorHeaderText' => 'theme-color-header-text',
        'colorBackgroundBase' => 'theme-color-background-base',
        'colorBackgroundTinyContrast' => 'theme-color-background-tinyContrast',
        'colorBackgroundLowContrast' => 'theme-color-background-lowContrast',
        'colorBackgroundContrast' => 'theme-color-background-contrast',
        'colorBackgroundHighContrast' => 'theme-color-background-highContrast',
        'colorBackgroundBrandTinyContrast' => 'theme-color-background-brand-tinyContrast',
        'colorBackgroundBrandLowContrast' => 'theme-color-background-brand-lowContrast',
        'colorBackgroundDisabled' => 'theme-color-background-disabled',
        'colorBorder' => 'theme-color-border',
        'colorBorderLight' => 'theme-color-border-light',
        'colorBorderBrand' => 'theme-color-border-brand',
        'colorBoxShadow' => 'theme-color-boxShadow',
        'shadowOverlay' => 'theme-shadow-overlay',
        'colorCode' => 'theme-color-code',
        'colorCodeBackground' => 'theme-color-code-background',
        'filterOnIllustration' => 'theme-filter-on-illustration',

        // Deprecated since Matomo 6.0.0, will be removed in Matomo 7. These names are
        // component-oriented: they are named after the component they were first created for rather
        // than the role they fill. Matomo's theming is moving to usage-oriented names. See the
        // @deprecated tag on each property below for the replacement, where one already exists.
        'colorMenuContrastText' => 'theme-color-menu-contrast-text',
        'colorMenuContrastTextSelected' => 'theme-color-menu-contrast-textSelected',
        'colorMenuContrastTextActive' => 'theme-color-menu-contrast-textActive',
        'colorMenuContrastBackground' => 'theme-color-menu-contrast-background',
        'colorMenuContrastBackgroundHover' => 'theme-color-menu-contrast-backgroundHover',
        'colorWidgetBackground' => 'theme-color-widget-background',
        'colorWidgetBorder' => 'theme-color-widget-border',
        'colorWidgetTitleText' => 'theme-color-widget-title-text',
        'colorWidgetTitleBackground' => 'theme-color-widget-title-background',
        'colorWidgetExportedBackgroundBase' => 'theme-color-widget-exported-background-base',

        // Deprecated since Matomo 6.0.0, will be removed in Matomo 7. A duplicate of colorBorder.
        'colorBorderAlternative' => 'theme-color-border-alternative',
    ];

    /**
     * @var string
     */
    protected $themeMode = self::AUTO_MODE;

    /**
     * Font family used for the text of the user interface.
     *
     * @var string|array<string>
     */
    public $fontFamilyBase = '-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Cantarell, \'Helvetica Neue\', sans-serif';

    /**
     * Brand color, used for primary buttons and highlights.
     *
     * @var string|array<string>
     */
    public $colorBrand = ['#00b4be', '#00d7e3'];

    /**
     * Color of text and icons placed on top of $colorBrand.
     *
     * @var string|array<string>
     */
    public $colorBrandContrast = ['#fff', '#ffffff'];

    /**
     * Semantic "all is fine / success / positive" green. Kept theme-independent from
     * $colorBrand so that success states stay green even when a theme overrides the brand.
     *
     * @var string|array<string>
     */
    public $colorSuccess = ['#43a047', '#66bb6a'];

    /**
     * Semantic "needs attention / not yet fatal" amber, the counterpart to $colorSuccess. Kept
     * theme-independent from $colorBrand for the same reason.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorWarning = ['#a18a0b', '#d4b93c'];

    /**
     * Semantic "this has failed / is blocking" red, completing $colorSuccess and $colorWarning.
     * Kept theme-independent from $colorBrand for the same reason, and a pair rather than
     * the flat @color-red-matomo it replaces, which does not reach 4.5:1 on a dark background.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorDanger = ['#c0322e', '#ef7f7c'];

    /**
     * Something that reads as positive, such as a rising metric in an evolution, a trend or a
     * sparkline maximum. Unlike $colorSuccess, it does not say that an operation succeeded.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorPositive = ['#33B343', '#4dcc5d'];

    /**
     * Something that reads as negative, such as a falling metric in an evolution, a trend or a
     * sparkline minimum. Unlike $colorDanger, it does not say that something failed.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorNegative = '#ee3024';

    /**
     * Something that reads as neither positive nor negative, such as an unchanged metric in an
     * evolution or a trend. Defaults to $colorTextLighter.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorNeutral;

    /**
     * Color of the focus outline of interactive elements.
     *
     * @var string|array<string>
     */
    public $colorFocusRing = '#0969da';

    /**
     * Alternative focus outline color, for elements where $colorFocusRing is hard to see.
     *
     * @var string|array<string>
     */
    public $colorFocusRingAlternative = '#00b4bE';

    /**
     * Text color with the highest contrast against the page background.
     *
     * @var string|array<string>
     */
    public $colorTextHighContrast = ['#000', '#d9d9d9'];

    /**
     * Default text color.
     *
     * @var string|array<string>
     */
    public $colorText = ['#212121', '#ccc'];

    /**
     * Secondary text color, slightly softer than $colorText.
     *
     * @var string|array<string>
     */
    public $colorTextContrast = ['#37474f', '#bbb'];

    /**
     * Lighter text color, for secondary text.
     *
     * @var string|array<string>
     */
    public $colorTextLight = ['#444', '#aaa'];

    /**
     * Lightest regular text color, for tertiary text such as hints and captions.
     *
     * @var string|array<string>
     */
    public $colorTextLighter = ['#646464', '#999'];

    /**
     * Text color for text placed on a disabled element.
     *
     * @var string|array<string>
     */
    public $colorTextOnDisabled = ['#666666', '#999'];

    /**
     * Text color of input placeholders.
     *
     * @var string|array<string>
     * @since Matomo 5.12.0
     */
    public $colorTextPlaceholder = ['#d1d1d1', '#565656'];

    /**
     * Text color for dark surfaces in light mode and light surfaces in dark mode.
     *
     * @var string|array<string>
     */
    public $colorTextInvert = ['#ccc', '#555'];

    /**
     * Higher contrast variant of $colorTextInvert.
     *
     * @var string|array<string>
     */
    public $colorTextInvertContrast = ['#fff', '#000'];

    /**
     * Lighter variant of $colorTextInvert; not used by core stylesheets.
     *
     * @var string|array<string>
     */
    public $colorTextInvertLight = ['#b9b9b9', '#666'];

    /**
     * Text color of disabled elements.
     *
     * @var string|array<string>
     */
    public $colorTextDisabled = ['#aaa', '#666'];

    /**
     * Color of links.
     *
     * @var string|array<string>
     */
    public $colorLink = ['#1976D2', '#778fd4'];

    /**
     * Text sitting on $colorBackgroundBrandTinyContrast or $colorBackgroundBrandLowContrast, where
     * $colorBrand itself does not reach the required contrast ratio.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorTextBrand = ['#186f72', '#4ad3dc'];

    /**
     * Exposed to themes as the LESS variable `@theme-color-base-series`; not used by core stylesheets.
     *
     * @var string|array<string>
     */
    public $colorBaseSeries = '#ee3024';

    /**
     * Alternative color for headlines.
     *
     * @var string|array<string>
     */
    public $colorHeadlineAlternative = ['#4E4E4E', '#aaa'];

    /**
     * Background color of the page header. Defaults to $colorBackgroundContrast.
     *
     * @var string|array<string>
     */
    public $colorHeaderBackground;

    /**
     * Text color of the page header. Defaults to $colorTextLighter.
     *
     * @var string|array<string>
     */
    public $colorHeaderText;

    /**
     * Color of the menu dropdown caret.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7 (`@theme-color-menu-contrast-text`)
     *             - component-oriented naming. Despite the name this no longer colours menu item
     *             labels; it only remains for the menu dropdown caret. A usage-oriented replacement
     *             will be defined before this is removed.
     */
    public $colorMenuContrastText;

    /**
     * Not used by core stylesheets.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-menu-contrast-textSelected`) - component-oriented naming. No core
     *             stylesheet reads this variable, so setting it has no effect on Matomo's own UI.
     */
    public $colorMenuContrastTextSelected;

    /**
     * Accent color of hovered, focused and active elements.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-menu-contrast-textActive`) - component-oriented naming. In practice
     *             this is the app-wide hover/focus/active accent and is used well beyond menus. A
     *             usage-oriented replacement will be defined before this is removed.
     */
    public $colorMenuContrastTextActive = ['#1976D2', '#fff'];

    /**
     * Hover and focus background of rows on the navigation surface.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-menu-contrast-backgroundHover`) - component-oriented naming. In
     *             practice this is the hover and focus background of a row on the navigation
     *             surface. A usage-oriented replacement will be defined before this is removed.
     */
    public $colorMenuContrastBackgroundHover = ['#eff0f1', '#151819'];

    /**
     * Background color of the navigation surface.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-menu-contrast-background`) - component-oriented naming. In practice
     *             this is the navigation surface, which themes intentionally tint differently from
     *             card surfaces. A usage-oriented replacement will be defined before this is removed.
     */
    public $colorMenuContrastBackground;

    /**
     * Page background color of exported and embedded widgets.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-widget-exported-background-base`) - component-oriented naming, and
     *             misleading: this is the page canvas for embedded and exported output, which
     *             deliberately differs from the app canvas $colorBackgroundBase so that embedded
     *             widgets blend into a host page. A usage-oriented replacement will be defined
     *             before this is removed.
     */
    public $colorWidgetExportedBackgroundBase;

    /**
     * Text color of the widget and report header strip.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7 (`@theme-color-widget-title-text`)
     *             - component-oriented naming. In practice this is the text of the widget and report
     *             header strip, which themes intentionally colour differently from $colorText. A
     *             usage-oriented replacement will be defined before this is removed.
     */
    public $colorWidgetTitleText;

    /**
     * Background color of the widget and report header strip.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7
     *             (`@theme-color-widget-title-background`) - component-oriented naming. In practice
     *             this is the widget and report header strip, which themes intentionally tint
     *             differently from the card surface $colorBackgroundContrast. A usage-oriented
     *             replacement will be defined before this is removed.
     */
    public $colorWidgetTitleBackground;

    /**
     * Background color of the page.
     *
     * @var string|array<string>
     */
    public $colorBackgroundBase = ['#f5f5f5', '#151819'];

    /**
     * Background color that differs only slightly from the surface it is placed on.
     *
     * @var string|array<string>
     */
    public $colorBackgroundTinyContrast = ['#f2f2f2', '#182c32'];

    /**
     * Background color with a low contrast to the surface it is placed on.
     *
     * @var string|array<string>
     */
    public $colorBackgroundLowContrast = ['#d9d9d9', '#192d33'];

    /**
     * Background color of elevated surfaces such as cards and panels.
     *
     * @var string|array<string>
     */
    public $colorBackgroundContrast = ['#fff', '#202329'];

    /**
     * Background color with a high contrast to the page background, such as tooltips.
     *
     * @var string|array<string>
     */
    public $colorBackgroundHighContrast = ['#202020', '#404349'];

    /**
     * Background color of disabled elements.
     *
     * @var string|array<string>
     */
    public $colorBackgroundDisabled = ['#d9d9d9', '#303339'];

    /**
     * Brand-tinted surfaces, for panels and chips that should read as part of the product rather
     * than as neutral chrome. "Tiny" is the fainter of the two, for large areas; "low" carries
     * enough contrast for a selected or highlighted control.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorBackgroundBrandTinyContrast = ['#f3fdff', '#152e2d'];

    /**
     * Brand-tinted background with a higher contrast than $colorBackgroundBrandTinyContrast.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorBackgroundBrandLowContrast = ['#e1fbfb', '#16302f'];

    /**
     * Border color used for some separators and input outlines.
     *
     * @var string|array<string>
     */
    public $colorBorderLight = ['#a9a399', '#645e54'];

    /**
     * Brand-tinted border, the counterpart to $colorBorder for elements drawn on a brand-tinted
     * surface.
     *
     * @var string|array<string>
     * @since Matomo 6.0.0
     */
    public $colorBorderBrand = ['#bfe7e9', '#2f5e5e'];

    /**
     * Default border, for dividers and the outlines of cards, inputs and panels.
     *
     * @var string|array<string>
     */
    public $colorBorder = ['#e0e0e0', '#555555'];

    /**
     * Not used by core stylesheets.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7 (`@theme-color-border-alternative`)
     *             - use $colorBorder (`@theme-color-border`) instead, which now carries this value.
     *             Core no longer reads this variable.
     */
    public $colorBorderAlternative;

    /**
     * Color of box shadows.
     *
     * @var string|array<string>
     */
    public $colorBoxShadow = ['rgba(0, 0, 0, 0.1)', 'rgba(0, 0, 0, 0.1)'];

    /**
     * Box shadow of overlays such as dialogs and dropdowns.
     *
     * @var string|array<string>
     */
    public $shadowOverlay = ['0 0 3px #A5B1CA4D, 0 10px 40px #A5B1CA4D', '0 0 3px #00000080, 0 10px 40px #00000080'];

    /**
     * Text color of code blocks.
     *
     * @var string|array<string>
     */
    public $colorCode = '#f3f3f3';

    /**
     * Background color of code blocks.
     *
     * @var string|array<string>
     */
    public $colorCodeBackground = '#4d4d4d';

    /**
     * Background color of elevated content surfaces.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7 (`@theme-color-widget-background`)
     *             - component-oriented naming. This is the generic elevated content surface and is
     *             used well beyond widgets. Use $colorBackgroundContrast
     *             (`@theme-color-background-contrast`) instead.
     */
    public $colorWidgetBackground;

    /**
     * Not used by core stylesheets.
     *
     * @var string|array<string>
     * @deprecated since Matomo 6.0.0, will be removed in Matomo 7 (`@theme-color-widget-border`)
     *             - component-oriented naming. No core stylesheet reads this variable, so setting it
     *             has no effect on Matomo's own UI. Note that core draws the widget border with
     *             $colorBackgroundLowContrast, not with this variable.
     */
    public $colorWidgetBorder;

    /**
     * CSS filter applied to illustrations, to adapt them to the theme mode.
     *
     * @var string|array<string>
     */
    public $filterOnIllustration = ['none', 'brightness(89%) invert(100%) hue-rotate(180deg)'];

    /**
     * @internal
     */
    public function __construct(string $themeMode)
    {
        $this->themeMode = $themeMode;
        $this->colorMenuContrastText = $this->colorText;
        $this->colorMenuContrastTextSelected = $this->colorMenuContrastText;
        $this->colorMenuContrastBackground = $this->colorBackgroundContrast;
        $this->colorWidgetExportedBackgroundBase = $this->colorBackgroundContrast;
        $this->colorWidgetTitleText = $this->colorText;
        $this->colorWidgetTitleBackground = $this->colorBackgroundContrast;
        $this->colorWidgetBackground = $this->colorBackgroundContrast;
        $this->colorWidgetBorder = $this->colorBackgroundTinyContrast;
        $this->colorBorderAlternative = $this->colorBorder;
        $this->colorHeaderBackground = $this->colorBackgroundContrast;
        $this->colorHeaderText = $this->colorTextLighter;
        $this->colorNeutral = $this->colorTextLighter;
    }

    /**
     * @param string $mode The theme mode: auto, light or dark.
     * @return ThemeStyles
     * @internal
     */
    public static function get(string $mode = self::AUTO_MODE)
    {
        $result = new self($mode);

        /**
         * Triggered when the theme variables are created, so themes can change colors and fonts.
         *
         * A value is either one string for both modes or an array of the light and the dark mode value.
         *
         * **Example**
         *
         *     public function configureThemeVariables(ThemeStyles $vars): void
         *     {
         *         $vars->fontFamilyBase = 'Arial, Verdana, sans-serif';
         *         $vars->colorBrand = ['#5793d4', '#5793d4'];
         *     }
         *
         * @param ThemeStyles $vars The theme variables to change.
         */
        Piwik::postEvent('Theme.configureThemeVariables', [$result]);

        return $result;
    }

    /**
     * Returns whether the theme mode is light.
     *
     * @return bool True if the theme mode is light.
     * @internal
     */
    public function getIsLightMode(): bool
    {
        return $this->themeMode === self::LIGHT_MODE;
    }

    /**
     * Returns whether the theme mode is dark.
     *
     * @return bool True if the theme mode is dark.
     * @internal
     */
    public function getIsDarkMode(): bool
    {
        return $this->themeMode === self::DARK_MODE;
    }

    /**
     * Returns the theme mode.
     *
     * @return string One of the AUTO_MODE, LIGHT_MODE and DARK_MODE constants.
     * @internal
     */
    public function getThemeMode(): string
    {
        return $this->themeMode;
    }

    /**
     * Returns the value of a theme variable for the current mode; an empty string if the property does not exist.
     *
     * @param string $name The property name, eg 'colorBrand'.
     * @return string The value for the current mode.
     * @internal
     */
    public function getPropertyValue(string $name): string
    {
        if (!property_exists($this, $name)) {
            return '';
        }

        return $this->resolvePropertyValue($this->$name, $this->getIsDarkMode() ? 1 : 0);
    }

    /**
     * @internal
     */
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
