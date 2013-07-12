<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Feedback
 */

/**
 *
 * @package Piwik_Feedback
 */
class Piwik_Feedback extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Feedback_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'TopMenu.add'              => 'addTopMenu',
        );
    }

    public function addTopMenu()
    {
        Piwik_AddTopMenu(
            'General_GiveUsYourFeedback',
            array('module' => 'Feedback', 'action' => 'index', 'segment' => false),
            true,
            $order = 20,
            $isHTML = false,
            $tooltip = Piwik_Translate('Feedback_TopLinkTooltip')
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/Feedback/stylesheets/feedback.css";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Feedback/javascripts/feedback.js";
    }
}
