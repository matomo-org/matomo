<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Provider
 */

/**
 *
 * @package Piwik_Provider
 */
class Piwik_Provider extends Piwik_Plugin
{
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('Provider_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
            'TrackerPlugin'   => true, // this plugin must be loaded during the stats logging
        );

        return $info;
    }

    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'Tracker.newVisitorInformation'    => 'logProviderInfo',
            'WidgetsList.add'                  => 'addWidget',
            'Menu.add'                         => 'addMenu',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
        );
        return $hooks;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();
        $reports[] = array(
            'category'      => Piwik_Translate('General_Visitors'),
            'name'          => Piwik_Translate('Provider_ColumnProvider'),
            'module'        => 'Provider',
            'action'        => 'getProvider',
            'dimension'     => Piwik_Translate('Provider_ColumnProvider'),
            'documentation' => Piwik_Translate('Provider_ProviderReportDocumentation', '<br />'),
            'order'         => 50
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('Provider_ColumnProvider'),
            'segment'        => 'provider',
            'acceptedValues' => 'comcast.net, proxad.net, etc.',
            'sqlSegment'     => 'log_visit.location_provider'
        );
    }

    function install()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER IGNORE TABLE `" . Piwik_Common::prefixTable('log_visit') . "` ADD `location_provider` VARCHAR( 100 ) NULL";

        // if the column already exist do not throw error. Could be installed twice...
        try {
            Piwik_Exec($query);
        } catch (Exception $e) {
            if (!Zend_Registry::get('db')->isErrNo($e, '1060')) {
                throw $e;
            }
        }

    }

    function uninstall()
    {
        // add column hostname / hostname ext in the visit table
        $query = "ALTER TABLE `" . Piwik_Common::prefixTable('log_visit') . "` DROP `location_provider`";
        Piwik_Exec($query);
    }

    function addWidget()
    {
        Piwik_AddWidget('General_Visitors', 'Provider_WidgetProviders', 'Provider', 'getProvider');
    }

    function addMenu()
    {
        Piwik_RenameMenuEntry('General_Visitors', 'UserCountry_SubmenuLocations',
            'General_Visitors', 'Provider_SubmenuLocationsProvider');
    }

    function postLoad()
    {
        Piwik_AddAction('template_footerUserCountry', array('Piwik_Provider', 'footerUserCountry'));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     * @return mixed
     */
    function archivePeriod($notification)
    {
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $dataTableToSum = array('Provider_hostnameExt');
        $archiveProcessing->archiveDataTable($dataTableToSum, null, $maximumRowsInDataTable);
    }

    /**
     * Daily archive: processes the report Visits by Provider
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function archiveDay($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $recordName = 'Provider_hostnameExt';
        $labelSQL = "log_visit.location_provider";
        $interestByProvider = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableProvider = $archiveProcessing->getDataTableFromArray($interestByProvider);
        $columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $archiveProcessing->insertBlobRecord($recordName, $tableProvider->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
        destroy($tableProvider);
    }

    /**
     * Logs the provider in the log_visit table
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function logProviderInfo($notification)
    {
        $visitorInfo =& $notification->getNotificationObject();

        // if provider info has already been set, abort
        if (!empty($visitorInfo['location_provider'])) {
            return;
        }

        $ip = Piwik_IP::N2P($visitorInfo['location_ip']);

        // In case the IP was anonymized, we should not continue since the DNS reverse lookup will fail and this will slow down tracking
        if (substr($ip, -2, 2) == '.0') {
            printDebug("IP Was anonymized so we skip the Provider DNS reverse lookup...");
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
        if (array_key_exists($hostnameDomain, Piwik_Common::getCountriesList())) {
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
            Piwik_PostEvent('Provider.getCleanHostname', $cleanHostname, $hostname);
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
        return trim(strtolower(@Piwik_IP::getHostByAddr($ip)));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    static public function footerUserCountry($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '<div>
			<h2>' . Piwik_Translate('Provider_WidgetProviders') . '</h2>';
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('Provider', 'getProvider');
        $out .= '</div>';
    }

}
