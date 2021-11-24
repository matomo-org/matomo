<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Transitions;

use Piwik\Common;
use Piwik\Plugins\Transitions\API;

/**
 */
class Transitions extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
            'Template.jsGlobalVariables'             => 'addJsGlobalVariables',
        );
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = "General_Actions.Transitions_Transitions";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/Transitions/stylesheets/transitions.less';
        $stylesheets[] = 'plugins/Transitions/angularjs/transitionexporter/transitionexporter.popover.less';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Transitions/javascripts/transitions.js';
        $jsFiles[] = 'plugins/Transitions/angularjs/transitionswitcher/transitionswitcher.controller.js';
        $jsFiles[] = 'plugins/Transitions/angularjs/transitionexporter/transitionexporter.directive.js';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_TransitionsRowActionTooltipTitle';
        $translationKeys[] = 'General_TransitionsRowActionTooltip';
        $translationKeys[] = 'Actions_PageUrls';
        $translationKeys[] = 'Actions_WidgetPageTitles';
        $translationKeys[] = 'Transitions_NumPageviews';
        $translationKeys[] = 'Transitions_Transitions';
        $translationKeys[] = 'CoreHome_ThereIsNoDataForThisReport';
        $translationKeys[] = 'General_Others';
    }

    public function addJsGlobalVariables(&$out)
    {

        $idSite = Common::getRequestVar('idSite', 1, 'int');
        $period = Common::getRequestVar('period', 'day', 'string');
        $date = Common::getRequestVar('date', 'yesterday', 'string');

        $api = API::getInstance();
        if($api->getPeriodAllowed($period, $idSite, $date)) {
            $allowed = 'true';
        } else {
            $allowed = 'false';
        }

        $out .= "      
        piwik.transitionsPeriodAllowed = $allowed;\n
        ";

    }
}
