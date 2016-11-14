<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

class ProfessionalServices extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Request.getRenamedModuleAndAction' => 'renameProfessionalServicesModule',
            'Template.afterGoalConversionOverviewReport' => 'getGoalOverviewPromo',
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


    public function getGoalOverviewPromo(&$out)
    {
        if(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('AbTesting')) {
            return;
        }

        $out .= '
            <p style="margin-top:3em" class=" alert-info alert">Did you know?
                With <a target="_blank" rel="noreferrer" href="https://piwik.org/recommends/ab-testing-learn-more/">A/B Testing for Piwik</a> you can immediately increase conversions and sales by creating different versions of a page to see which grows your business.
            </p>
            ';
    }

    public function getEventsPromo(&$out)
    {
        $inlineAd = '';
        if(!\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MediaAnalytics')) {
            $inlineAd = '<br/>When you publish videos or audios, <a target="_blank" rel="noreferrer" href="https://piwik.org/recommends/media-analytics-website">Media Analytics gives deep insights into your audience</a> and how they watch your videos or listens to your music.';
        }
        $out .= '<p style="margin-top:3em" class=" alert-info alert">Did you know?
                <br/>Using Events you can measure any user interaction and gain amazing insights into your audience. <a target="_blank" href="?module=Proxy&action=redirect&url=http://piwik.org/docs/event-tracking/">Learn more</a>.
              ' . $inlineAd . '
            </p>';
    }
}
