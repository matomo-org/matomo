<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Common;
use Piwik\View;

class ProfessionalServices extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Request.getRenamedModuleAndAction' => 'renameProfessionalServicesModule',
            'Template.afterGoalConversionOverviewReport' => array('function' => 'getGoalOverviewPromo', 'after' => true),
            'Template.afterGoalCannotAddNewGoal' => array('function' => 'getGoalOverviewPromo', 'after' => true),
            'Template.endGoalEditTable' => array('function' => 'getGoalFunnelOverviewPromo', 'after' => true),
            'Template.afterEventsReport' => 'getEventsPromo',
            'Template.afterReferrersKeywordsReport' => 'getSearchKeywordsPerformancePromo',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/ProfessionalServices/stylesheets/widget.less';
    }

    /**
     * @deprecated Can be removed in Piwik 3.0
     * @param $module
     * @param $action
     */
    public function renameProfessionalServicesModule(&$module, &$action)
    {
        if ($module == 'ProfessionalServices') {
            $module = 'ProfessionalServices';

            if($action == 'promoPiwikPro') {
                $action = 'promoServices';
            }

            if($action == 'rssPiwikPro') {
                $action = 'rss';
            }
        }
    }

    public function isRequestForDashboardWidget()
    {
        $isWidget = Common::getRequestVar('widget', 0, 'int');
        return $isWidget;
    }

    public function getSearchKeywordsPerformancePromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('SearchEngineKeywordsPerformance')
            || $this->isRequestForDashboardWidget()) {
            return;
        }

        $view = new View('@ProfessionalServices/promoSearchKeywords');
        $out .= $view->render();
    }

    public function getGoalFunnelOverviewPromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Funnels')
            || $this->isRequestForDashboardWidget()) {
            return;
        }

        $view = new View('@ProfessionalServices/promoFunnel');
        $out .= $view->render();
    }


    public function getGoalOverviewPromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('AbTesting')
            || $this->isRequestForDashboardWidget()) {
            return;
        }

        $view = new View('@ProfessionalServices/promoExperiments.twig');
        $out .= $view->render();
    }

    public function getEventsPromo(&$out)
    {
        if($this->isRequestForDashboardWidget()) {
            return;
        }
        $view = new View('@ProfessionalServices/promoBelowEvents');
        $view->displayMediaAnalyticsAd = !\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MediaAnalytics');
        $out .= $view->render();
    }
}
