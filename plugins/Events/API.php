<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\Archive;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * Custom Events API
 *
 * @package Events
 * @method static \Piwik\Plugins\Events\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = Archive::getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->filter(function (DataTable $table) {
            $row = $table->getRowFromLabel(Archiver::EVENT_NAME_NOT_SET);
            if($row) {
                $row->setColumn('label', Piwik::translate(Archiver::EVENT_NAME_NOT_SET));
            }
        });
        return $dataTable;
    }

    public function getCategory($idSite, $period, $date, $segment = false, $expanded = false)
    {
        return $this->getDataTable(Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
    }

    public function getAction($idSite, $period, $date, $segment = false, $expanded = false)
    {
        return $this->getDataTable(Archiver::EVENTS_ACTION_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
    }

    public function getName($idSite, $period, $date, $segment = false, $expanded = false)
    {
        return $this->getDataTable(Archiver::EVENTS_NAME_ACTION_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
    }

    public function getActionFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_CATEGORY_ACTION_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getNameFromCategoryId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_CATEGORY_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getCategoryFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_ACTION_CATEGORY_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getNameFromActionId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_ACTION_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getActionFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_NAME_ACTION_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }

    public function getCategoryFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        return $this->getDataTable(Archiver::EVENTS_NAME_CATEGORY_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
    }
}