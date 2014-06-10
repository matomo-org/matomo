<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\FrontController;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;

/**
 *
 */
class Provider extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Tracker.newVisitorInformation'   => 'enrichVisitWithProviderInfo',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
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

    public function postLoad()
    {
        Piwik::addAction('Template.footerUserCountry', array('Piwik\Plugins\Provider\Provider', 'footerUserCountry'));
    }

    /**
     * Logs the provider in the log_visit table
     */
    public function enrichVisitWithProviderInfo(&$visitorInfo, \Piwik\Tracker\Request $request)
    {
        // if provider info has already been set, abort
        if (!empty($visitorInfo['location_provider'])) {
            return;
        }

        $privacyConfig = new PrivacyManagerConfig();
        $ip = IP::N2P($privacyConfig->useAnonymizedIpForVisitEnrichment ? $visitorInfo['location_ip'] : $request->getIp());

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
             * Triggered when prettifying a hostname string.
             * 
             * This event can be used to customize the way a hostname is displayed in the 
             * Providers report.
             *
             * **Example**
             * 
             *     public function getCleanHostname(&$cleanHostname, $hostname)
             *     {
             *         if ('fvae.VARG.ceaga.site.co.jp' == $hostname) {
             *             $cleanHostname = 'site.co.jp';
             *         }
             *     }
             * 
             * @param string &$cleanHostname The hostname string to display. Set by the event
             *                               handler.
             * @param string $hostname The full hostname.
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

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'Provider.getProvider':
                $this->configureViewForGetProvider($view);
                break;
        }
    }

    private function configureViewForGetProvider(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 5;
        $view->config->addTranslation('label', Piwik::translate('Provider_ColumnProvider'));
    }
}
