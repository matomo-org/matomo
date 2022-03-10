<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId;

use Piwik\Archive;
use Piwik\Piwik;
use Piwik\DataTable;

/**
 * API for plugin UserId. Allows to get User IDs table.
 *
 * @method static \Piwik\Plugins\UserId\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get a report of all User Ids.
     *
     * @param int $idSite
     *
     * @param string  $period
     * @param int  $date
     * @param string|bool  $segment
     *
     * @return DataTable
     */
    public function getUsers($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable(Archiver::USERID_ARCHIVE_RECORD);

        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->queueFilter('AddSegmentByLabel', array('userId'));

        return $dataTable;
    }
}
