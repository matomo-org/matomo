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
class Piwik_Provider_Archiver extends Piwik_PluginsArchiver
{
    const PROVIDER_RECORD_NAME = 'Provider_hostnameExt';
    protected $maximumRows;

    public function __construct($processor)
    {
        parent::__construct($processor);
        $this->maximumRows = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
    }

    public function archiveDay()
    {
        $labelSQL = "log_visit.location_provider";
        $metricsByProvider = $this->getProcessor()->getMetricsForLabel($labelSQL);
        $tableProvider = $this->getProcessor()->getDataTableFromArray($metricsByProvider);
        $this->getProcessor()->insertBlobRecord(self::PROVIDER_RECORD_NAME, $tableProvider->getSerialized($this->maximumRows, null, Piwik_Archive::INDEX_NB_VISITS));
    }

    public function archivePeriod()
    {
        $this->getProcessor()->archiveDataTable(array(self::PROVIDER_RECORD_NAME), null, $this->maximumRows);
    }
}