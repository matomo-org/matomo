<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId;

use Piwik\Archive;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Psr\Log\LoggerInterface;

/**
 * API for plugin UserId. Allows to get User IDs table.
 *
 * @method static \Piwik\Plugins\UserId\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get DataTable with User Ids and some aggregated data. Supports pagination, sorting
     * and filtering by user_id
     *
     * @param int $idSite
     *
     * @param     $period
     * @param     $date
     * @param     $segment
     * @param     $expanded
     * @param     $flat
     *
     * @return DataTable
     */
    public function getUsers($idSite, $period, $date, $segment = false, $expanded = false, $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable(Archiver::USERID_ARCHIVE_RECORD);

        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->queueFilter('Piwik\Plugins\UserId\DataTable\Filter\AddVisitorProfileUrl');

        return $dataTable;
    }
}
