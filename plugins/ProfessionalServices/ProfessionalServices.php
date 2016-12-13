<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Common;

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

    public function getGoalFunnelOverviewPromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('Funnels')
            || $this->isRequestForDashboardWidget()) {
            return;
        }

        $out .= '
            <p style="margin-top:3em;margin-bottom:3em" class=" alert-info alert">Did you know?
                A Funnel defines a series of actions that you expect your visitors to take on their way to converting a goal.
                <br/>With <a target="_blank" rel="noreferrer" href="https://piwik.org/recommends/conversion-funnel/">Funnels for Piwik</a>,
                you can easily determine your funnel and see where your visitors drop off and how to focus efforts to increase your conversions.
            </p>';
    }


    public function getGoalOverviewPromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('AbTesting')
            || $this->isRequestForDashboardWidget()) {
            return;
        }

        $out .= '
            <p style="margin-top:3em" class=" alert-info alert">Did you know?
                With <a target="_blank" rel="noreferrer" href="https://piwik.org/recommends/ab-testing-learn-more/">A/B Testing for Piwik</a> you can immediately increase conversions and sales by creating different versions of a page to see which one grows your business.
            </p>
            ';
    }

    public function getEventsPromo(&$out)
    {
        if($this->isRequestForDashboardWidget()) {
            return;
        }
        $inlineAd = '';
        if(!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MediaAnalytics')) {
            $inlineAd = '<br/>When you publish videos or audios, <a target="_blank" rel="noreferrer" href="https://piwik.org/recommends/media-analytics-website">Media Analytics gives deep insights into your audience</a> and how they watch your videos or listens to your music.';
        }
        $out .= '<p style="margin-top:3em" class=" alert-info alert">Did you know?
                <br/>Using Events you can measure any user interaction and gain amazing insights into your audience. <a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/docs/event-tracking/">Learn more</a>.
              <br/> To measure blocks of content such as image galleries, listings or ads: use <a target="_blank" href="?module=Proxy&action=redirect&url=http://developer.piwik.org/guides/content-tracking">Content Tracking</a> and see exactly which content is viewed and clicked.
              ' . $inlineAd . '
            </p>';
    }
}
