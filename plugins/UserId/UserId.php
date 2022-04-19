<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId;


/**
 * Plugin adds a new Users report showing all unique user IDs and some aggregated data
 */
class UserId extends \Piwik\Plugin
{
    /**
     * Register event observers
     *
     * @return array
     */
    public function registerEvents()
    {
        return array(
            // Add plugin's custom JS files
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            // Add translations for the client side JS
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    /**
     * Add a custom JS to the page. It adds possibility to open visitor details popover for each
     * user ID in a report table
     *
     * @param $jsFiles
     */
    public function getJavaScriptFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserId/javascripts/rowaction.js";
    }

    /**
     * Add translations for the client side JS
     *
     * @param $translationKeys
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_ViewVisitorProfile";
    }
}
