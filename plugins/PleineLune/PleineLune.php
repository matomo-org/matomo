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
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('PleineLune_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            Piwik_AssetManager::CSS_IMPORT_EVENT => 'getCssFiles',
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/PleineLune/stylesheets/theme.css";
    }
}
