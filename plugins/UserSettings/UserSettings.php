<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 *
 */
class UserSettings extends \Piwik\Plugin
{
    /**
     * Mapping between the browser family shortcode and the displayed name
     *
     * @type array
     */
    public static $browserType_display = array(
        'ie'     => 'Trident (IE)',
        'gecko'  => 'Gecko (Firefox)',
        'khtml'  => 'KHTML (Konqueror)',
        'webkit' => 'WebKit (Safari, Chrome)',
        'opera'  => 'Presto (Opera)',
    );

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Live.getAllVisitorDetails'            => 'extendVisitorDetails'
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['operatingSystem']          = $instance->getOperatingSystem();
        $visitor['operatingSystemCode']      = $instance->getOperatingSystemCode();
        $visitor['operatingSystemShortName'] = $instance->getOperatingSystemShortName();
        $visitor['operatingSystemIcon']      = $instance->getOperatingSystemIcon();
        $visitor['browserFamily']            = $instance->getBrowserFamily();
        $visitor['browserFamilyDescription'] = $instance->getBrowserFamilyDescription();
        $visitor['browserName']              = $instance->getBrowser();
        $visitor['browserIcon']              = $instance->getBrowserIcon();
        $visitor['browserCode']              = $instance->getBrowserCode();
        $visitor['browserVersion']           = $instance->getBrowserVersion();
        $visitor['screenType']               = $instance->getScreenType();
        $visitor['resolution']               = $instance->getResolution();
        $visitor['screenTypeIcon']           = $instance->getScreenTypeIcon();
        $visitor['plugins']                  = $instance->getPlugins();
        $visitor['pluginsIcons']             = $instance->getPluginIcons();
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits')
        );

        $translations = array_merge($translations, $metrics);
    }

}
