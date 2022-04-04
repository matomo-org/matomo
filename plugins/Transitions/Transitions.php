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
use Piwik\Config;

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
        $stylesheets[] = 'plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.less';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = 'plugins/Transitions/javascripts/transitions.js';
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
        $translationKeys[] = 'Actions_ActionType';
        $translationKeys[] = 'Transitions_TopX';
        $translationKeys[] = 'Transitions_AvailableInOtherReports';
        $translationKeys[] = 'Actions_SubmenuPageTitles';
        $translationKeys[] = 'Actions_SubmenuPagesEntry';
        $translationKeys[] = 'Actions_SubmenuPagesExit';
        $translationKeys[] = 'Transitions_AvailableInOtherReports2';
    }

    public function addJsGlobalVariables(&$out)
    {
        $idSite = Common::getRequestVar('idSite', 1, 'int');
        $maxPeriodAllowed = self::getPeriodAllowedConfig($idSite);

        $out .= '    piwik.transitionsMaxPeriodAllowed = "'.($maxPeriodAllowed ? $maxPeriodAllowed : 'all').'"'."\n";
    }

    /**
     * Retrieve the period allowed config setting for a site or all sites if null
     *
     * @param $idSite
     *
     * @return string
     */
    public static function getPeriodAllowedConfig($idSite) : string
    {
        $transitionsGeneralConfig = Config::getInstance()->Transitions;
        $generalMaxPeriodAllowed = ($transitionsGeneralConfig && !empty($transitionsGeneralConfig['max_period_allowed']) ? $transitionsGeneralConfig['max_period_allowed']: null);

        $siteMaxPeriodAllowed = null;
        if ($idSite) {
            $sectionName = 'Transitions_'.$idSite;
            $transitionsSiteConfig = Config::getInstance()->$sectionName;
            $siteMaxPeriodAllowed = ($transitionsSiteConfig && !empty($transitionsSiteConfig['max_period_allowed']) ? $transitionsSiteConfig['max_period_allowed'] : null);
        }

        if (!$generalMaxPeriodAllowed && !$siteMaxPeriodAllowed) {
            return 'all'; // No config setting, so all periods are valid
        }

        // Site setting overrides general, if it exists
        return $siteMaxPeriodAllowed ?? $generalMaxPeriodAllowed;
    }
}
