<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution;

use Piwik\Archive;
use Piwik\Piwik;

/**
 * @see plugins/Resolution/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Resolution/functions.php';

/**
 * @method static \Piwik\Plugins\Resolution\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    public function getResolution($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::RESOLUTION_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        return $dataTable;
    }

    public function getConfiguration($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CONFIGURATION_RECORD_NAME, $idSite, $period, $date, $segment);
        // use GroupBy filter to avoid duplicate rows if old reports are displayed
        $dataTable->queueFilter('GroupBy', array('label', __NAMESPACE__ . '\getConfigurationLabel'));
        return $dataTable;
    }
}
