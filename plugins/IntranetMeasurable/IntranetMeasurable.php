<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable;

use Piwik\Container\StaticContainer;
use Piwik\Site;

class IntranetMeasurable extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Tracker.Cache.getSiteAttributes' => 'recordWebsiteDataInCache',
        );
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function recordWebsiteDataInCache(&$array, $idSite)
    {
        $idSite = (int) $idSite;
        $type = Site::getTypeFor($idSite);
        if ($type === Type::ID) {
            /** @var \Piwik\Plugins\IntranetMeasurable\MeasurableSettings $measurableSettings */
            $measurableSettings = StaticContainer::getContainer()->make(
                '\Piwik\Plugins\IntranetMeasurable\MeasurableSettings',
                array('idSite' => $idSite, 'idMeasurableType' => Site::getTypeFor($idSite))
            );

            // add the 'hosts' entry in the website array
            $array['enable_trust_visitors_cookies'] = (int) $measurableSettings->trustvisitorcookies->getValue();
        }
    }
}
