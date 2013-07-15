<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_PleineLune
 */

/**
 *
 * @package Piwik_PleineLune
 */
class Piwik_PleineLune extends Piwik_Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            Piwik_AssetManager::CSS_IMPORT_EVENT => 'getCssFiles',
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/PleineLune/stylesheets/theme.less";
    }
}
