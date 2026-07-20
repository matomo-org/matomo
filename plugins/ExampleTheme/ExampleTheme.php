<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleTheme;

use Piwik\Plugin;

class ExampleTheme extends Plugin
{
    public function registerEvents()
    {
        return [
            'Theme.configureThemeVariables' => 'configureThemeVariables',
        ];
    }

    public function configureThemeVariables(Plugin\ThemeStyles $vars)
    {
        $vars->fontFamilyBase = 'Arial, Verdana, sans-serif';
        $vars->colorBrand = ['#5793d4', '#5793d4'];
        $vars->colorBackgroundBase = ['#d9e0e3', '#151819'];
        $vars->colorHeaderBackground = ['#0091ea', '#2b3138'];
        $vars->colorHeaderText = ['#0d0d0d', '#ccc'];
        $vars->colorWidgetTitleBackground = ['#80d8ff', '#202329'];
        $vars->colorWidgetTitleText = ['#01579b', '#ccc'];
        $vars->colorMenuContrastText = ['#0091ea', '#ccc'];
        $vars->colorMenuContrastTextActive = ['#006064', '#fff'];
        $vars->colorMenuContrastTextSelected = ['#00838f', '#ccc'];
        $vars->colorMenuContrastBackground = ['#e1f5fe', '#202329'];
    }
}
