<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\UserId\Reports\GetUsers;
use Psr\Log\LoggerInterface;

/**
 * API for plugin UserId. Allows to get User IDs table and indexing visits log into user IDs index
 *
 * @method static \Piwik\Plugins\UserId\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /** @var Model */
    private $model;

    /** @var LoggerInterface */
    private $logger;

    /** @var Indexer */
    private $indexer;

    /**
     * @param LoggerInterface $logger
     * @param Model           $model
     * @param Indexer         $indexer
     */
    public function __construct(LoggerInterface $logger, Model $model, Indexer $indexer)
    {
        $this->model = $model;
        $this->logger = $logger;
        $this->indexer = $indexer;
    }

    /**
     * Get DataTable with User Ids and some aggregated data. Supports pagination, sorting
     * and filtering by user_id
     *
     * @param int    $idSite
     *
     * @return DataTable
     */
    public function getUsers($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (Rules::isBrowserTriggerEnabled()) {
            // Do incremental reindex right before the request if no cron archiving is enabled
            $this->indexer->reindex();
        }

        /*
         * Initialize GET parameters
         */
        $filterLimit     = Common::getRequestVar('filter_limit', 25, 'int');
        $filterOffset    = Common::getRequestVar('filter_offset', 0, 'int');
        $filterSortOrder = Common::getRequestVar('filter_sort_order', 'asc', 'string');
        $filterPattern   = Common::getRequestVar('filter_pattern', '', 'string');
        $filterSortColumn = Common::getRequestVar('filter_sort_column', 'user_id', 'string');
        // Only allow certain columns to sort by
        $filterSortColumn = in_array($filterSortColumn, GetUsers::getColumnsToDisplay()) ? $filterSortColumn : 'user_id';

        $dataTable = new DataTable();
        // Don't delegate calculation of total rows to DataTable. Do additional DB query to get total rows number
        $dataTable->setMetadata(
            DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME, $this->model->getTotalUsersNumber($idSite, $filterPattern)
        );
        $dataTable->addRowsFromSimpleArray(
            $this->model->getSiteUserIds(
                $idSite, $filterOffset, $filterLimit, $filterSortOrder, $filterSortColumn, $filterPattern
            )
        );
        /*
         * Disable DataTable filters. We do sorting, pagination and filtering in SQL queries instead
         */
        $dataTable->disableFilter('Sort');
        $dataTable->disableFilter('Limit');
        $dataTable->disableFilter('Pattern');

        foreach ($dataTable->getRows() as $row) {
            /*
             * Format dates
             */
            $row->setColumn(
                'first_visit_time',
                Date::factory($row->getColumn('first_visit_time'))->getLocalized(Date::DATE_FORMAT_SHORT)
            );
            $row->setColumn(
                'last_visit_time',
                Date::factory($row->getColumn('last_visit_time'))->getLocalized(Date::DATE_FORMAT_SHORT)
            );

            /*
             * Get idvisitor and remove it from DataTable. We need it to form an URL that is used in
             * JS to show visitor details popover. See rowaction.js
             */
            $idVisitor = $row->getColumn('idvisitor');
            $row->deleteColumn('idvisitor');
            $row->setMetadata(
                'url', 'module=Live&action=getVisitorProfilePopup&visitorId=' . urlencode(bin2hex($idVisitor))
            );
        }

        return $dataTable;
    }

    /**
     * Cleans the User IDs index
     *
     * @return bool
     */
    public function cleanIndex()
    {
        Piwik::checkUserHasSuperUserAccess();

        try {
            $this->indexer->cleanIndex();
            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                "An exception occurred during UserId.cleanIndex process", array('exception' => $e)
            );
            return false;
        }
    }

    /**
     * Performs an incremental reindex of raw visitors log data to user IDs index
     *
     * @return bool
     */
    public function reindex()
    {
        Piwik::checkUserHasSuperUserAccess();

        try {
            $this->indexer->reindex();
            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                "An exception occurred during UserId.reindex process", array('exception' => $e)
            );
            return false;
        }
    }
}
