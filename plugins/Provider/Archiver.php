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
    const PROVIDER_FIELD = "location_provider";

    public function archiveDay()
    {
        $metrics = $this->getProcessor()->getMetricsForDimension(self::PROVIDER_FIELD);
        $tableProvider = $this->getProcessor()->getDataTableFromDataArray($metrics);
        $this->getProcessor()->insertBlobRecord(self::PROVIDER_RECORD_NAME, $tableProvider->getSerialized($this->maximumRows, null, Piwik_Metrics::INDEX_NB_VISITS));
    }

    public function archivePeriod()
    {
        $this->getProcessor()->aggregateDataTableReports(array(self::PROVIDER_RECORD_NAME), $this->maximumRows);
    }
}