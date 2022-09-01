<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\Contents\Archiver;

/**
 * API for plugin Contents
 *
 * @method static \Piwik\Plugins\Contents\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function getContentNames($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable);
    }

    public function getContentPieces($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable);
    }

    private function getDataTable($name, $idSite, $period, $date, $segment, $expanded, $idSubtable)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $recordName = Dimensions::getRecordNameForAction($name);
        $dataTable  = Archive::createDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded, $flat=false, $idSubtable);

        if (empty($idSubtable)) {
            $dataTable->filter('AddSegmentValue', array(function ($label) {
                if ($label === Archiver::CONTENT_PIECE_NOT_SET) {
                    return false;
                }

                return $label;
            }));
        }

        $this->filterDataTable($dataTable);
        return $dataTable;
    }

    /**
     * @param DataTable $dataTable
     */
    private function filterDataTable($dataTable)
    {
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->filter(function (DataTable $table) {
            $row = $table->getRowFromLabel(Archiver::CONTENT_PIECE_NOT_SET);
            if ($row) {
                $row->setColumn('label', Piwik::translate('General_NotDefined', Piwik::translate('Contents_ContentPiece')));
            }
        });
    }
}
