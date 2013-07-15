<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Overlay
 */

class Piwik_Overlay extends Piwik_Plugin
{
    public function getInformation()
    {
        $suffix = ' Note: Requires the Transitions plugin enabled.';
        $info = parent::getInformation();
        $info['description'] .= ' ' . $suffix;
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles' => 'getJsFiles'
        );
    }

    /**
     * Returns required Js Files
     * @param $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Overlay/javascripts/rowaction.js';
        $jsFiles[] = 'plugins/Overlay/javascripts/Overlay_Helper.js';
    }
}
