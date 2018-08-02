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
            'Emails.setThemeVariables' => 'setEmailThemeVariables',
        ];
    }

    public function setEmailThemeVariables(&$vars)
    {
        $vars['themeFontFamilyBase'] = 'Arial, Verdana, sans-serif';
        $vars['themeColorBrand'] = '#5793d4';
        $vars['themeColorHeaderBackground'] = '#0091ea';
        $vars['themeColorHeaderText'] = '#0d0d0d';
    }
}
