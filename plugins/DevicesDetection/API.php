<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DevicesDetection
 */
class Piwik_DevicesDetection_API
{

    static private $instance = null;

    /**
     * 
     * @return Piwik_DevicesDetection_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param string $name
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by device type (eg. desktop, smartphone, tablet)
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getType($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_types', $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getDeviceTypeLabel'));
        $dataTable->filter('ColumnCallbackReplace', array('label', 'ucfirst'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getDeviceTypeLogo'));
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by device manufacturer name
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getBrand($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_brands', $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getDeviceBrandLabel'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_GetBrandLogo'));
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by device model
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getModel($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_models', $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getModelName'));
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by OS family (eg. Windows, Android, Linux)
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getOsFamilies($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_os', $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', array('label', 'Piwik_getOSFamilyFullNameExtended'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getOsFamilyLogoExtended'));
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by OS version (eg. Android 4.0, Windows 7)
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getOsVersions($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_osVersions', $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getOsLogoExtended'));
        $dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getOsFullNameExtended'));

        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by Browser family (eg. Firefox, InternetExplorer)
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getBrowserFamilies($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browsers', $idSite, $period, $date, $segment);
        $dataTable->filter('GroupBy', array('label', 'Piwik_getBrowserFamilyFullNameExtended'));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getBrowserFamilyLogoExtended'));
        return $dataTable;
    }

    /**
     * Gets datatable displaying number of visits by Browser version (eg. Firefox 20, Safari 6.0)
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $segment
     * @return DataTable
     */
    public function getBrowserVersions($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('DevicesDetection_browserVersions', $idSite, $period, $date, $segment);
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getBrowserLogoExtended'));
        $dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserNameExtended'));
        return $dataTable;
    }

}