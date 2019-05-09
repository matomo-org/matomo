<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleTheme;

use Piwik\Plugin;

class ExampleTheme extends Plugin
{
    public function getListHooksRegistered()
    {
        return [
            'Theme.configureThemeVariables' => 'configureThemeVariables',
        ];
    }

    public function configureThemeVariables(Plugin\ThemeStyles $vars)
    {
        $vars->fontFamilyBase = 'Verdana, sans-serif';
    }
}
