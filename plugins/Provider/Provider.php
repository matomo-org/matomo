<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Provider
 */
namespace Piwik\Plugins\Provider;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;

use Piwik\FrontController;
use Piwik\IP;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 *
 * @package Provider
 */
class Provider extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'Tracker.newVisitorInformation'            => 'logProviderInfo',
            'WidgetsList.addWidgets'                   => 'addWidget',
            'Menu.Reporting.addItems'                  => 'addMenu',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'      => Piwik::translate('General_Visitors'),
            'name'          => Piwik::translate('Provider_ColumnProvider'),
            'module'        => 'Provider',
            'action'        => 'getProvider',
            'dimension'     => Piwik::translate('Provider_ColumnProvider'),
            'documentation' => Piwik::translate('Provider_ProviderReportDocumentation', '<br />'),
            'order'         => 50
        );
    }

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('Provider_ColumnProvider'),
            'segment'        => 'provider',
            'acceptedValues' => 'comcast.net, proxad.net, etc.',
            'sqlSegment'     => 'log_visit.location_provider'
        );
    }

    public function install()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER IGNORE TABLE `" . Common::prefixTable('log_visit') . "` ADD `location_provider` VARCHAR( 100 ) NULL";

        // if the column already exist do not throw error. Could be installed twice...
        try {
            Db::exec($query);
        } catch (Exception $e) {
            if (!Db::get()->isErrNo($e, '1060')) {
                throw $e;
            }
        }
    }

    public function uninstall()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER TABLE `" . Common::prefixTable('log_visit') . "` DROP `location_provider`";
        Db::exec($query);
    }

    public function addWidget()
    {
        WidgetsList::add('General_Visitors', 'Provider_WidgetProviders', 'Provider', 'getProvider');
    }

    public function addMenu()
    {
        MenuMain::getInstance()->rename('General_Visitors', 'UserCountry_SubmenuLocations',
            'General_Visitors', 'Provider_SubmenuLocationsProvider');
    }

    public function postLoad()
    {
        Piwik::addAction('Template.footerUserCountry', array('Piwik\Plugins\Provider\Provider', 'footerUserCountry'));
    }

    /**
     * Logs the provider in the log_visit table
     */
    public function logProviderInfo(&$visitorInfo)
    {
        // if provider info has already been set, abort
        if (!empty($visitorInfo['location_provider'])) {
            return;
        }

        $ip = IP::N2P($visitorInfo['location_ip']);

        // In case the IP was anonymized, we should not continue since the DNS reverse lookup will fail and this will slow down tracking
        if (substr($ip, -2, 2) == '.0') {
            Common::printDebug("IP Was anonymized so we skip the Provider DNS reverse lookup...");
            return;
        }

        $hostname = $this->getHost($ip);
        $hostnameExtension = $this->getCleanHostname($hostname);

        // add the provider value in the table log_visit
        $visitorInfo['location_provider'] = $hostnameExtension;
        $visitorInfo['location_provider'] = substr($visitorInfo['location_provider'], 0, 100);

        // improve the country using the provider extension if valid
        $hostnameDomain = substr($hostnameExtension, 1 + strrpos($hostnameExtension, '.'));
        if ($hostnameDomain == 'uk') {
            $hostnameDomain = 'gb';
        }
        if (array_key_exists($hostnameDomain, Common::getCountriesList())) {
            $visitorInfo['location_country'] = $hostnameDomain;
        }
    }

    /**
     * Returns the hostname extension (site.co.jp in fvae.VARG.ceaga.site.co.jp)
     * given the full hostname looked up from the IP
     *
     * @param string $hostname
     *
     * @return string
     */
    private function getCleanHostname($hostname)
    {
        $extToExclude = array(
            'com', 'net', 'org', 'co'
        );

        $off = strrpos($hostname, '.');
        $ext = substr($hostname, $off);

        if (empty($off) || is_numeric($ext) || strlen($hostname) < 5) {
            return 'Ip';
        } else {
            $cleanHostname = null;

            /**
             * This event is triggered to get a clean hostname depending on a given hostname. For instance it is used
             * to return `site.co.jp` in `fvae.VARG.ceaga.site.co.jp`. Use this event to customize the way a hostname
             * is cleaned.
             *
             * Example:
             * ```
             * public function getCleanHostname(&$cleanHostname, $hostname)
             * {
             *     if ('fvae.VARG.ceaga.site.co.jp' == $hostname) {
             *         $cleanHostname = 'site.co.jp';
             *     }
             * }
             * ```
             */
            Piwik::postEvent('Provider.getCleanHostname', array(&$cleanHostname, $hostname));
            if ($cleanHostname !== null) {
                return $cleanHostname;
            }

            $e = explode('.', $hostname);
            $s = sizeof($e);

            // if extension not correct
            if (isset($e[$s - 2]) && in_array($e[$s - 2], $extToExclude)) {
                return $e[$s - 3] . "." . $e[$s - 2] . "." . $e[$s - 1];
            } else {
                return $e[$s - 2] . "." . $e[$s - 1];
            }
        }
    }

    /**
     * Returns the hostname given the IP address string
     *
     * @param string $ip IP Address
     * @return string hostname (or human-readable IP address)
     */
    private function getHost($ip)
    {
        return trim(strtolower(@IP::getHostByAddr($ip)));
    }

    static public function footerUserCountry(&$out)
    {
        $out = '<div>
			<h2>' . Piwik::translate('Provider_WidgetProviders') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('Provider', 'getProvider');
        $out .= '</div>';
    }

    /**
     * Daily archive: processes the report Visits by Provider
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Provider.getProvider'] = $this->getDisplayPropertiesForGetProvider();
    }

    private function getDisplayPropertiesForGetProvider()
    {
        return array(
            'translations' => array('label' => Piwik::translate('Provider_ColumnProvider')),
            'filter_limit' => 5
        );
    }
}
