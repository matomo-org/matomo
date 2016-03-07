<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId;

use Psr\Log\LoggerInterface;

/**
 * Allows to reindex unique user IDs and some statistics from log_visit table to user_ids table.
 */
class Indexer
{
    /** Small enough to support MySQL max_allowed_packet = 4Mb */
    const INDEX_BUNCH_SIZE = 5000;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Model
     */
    private $model;

    /**
     * @param LoggerInterface $logger
     * @param Model           $model
     */
    public function __construct(LoggerInterface $logger, Model $model)
    {
        $this->logger = $logger;
        $this->model = $model;
    }

    /**
     * Aggregate raw visits data from log_visit DB table and save it into user_ids DB table
     */
    public function reindex()
    {
        $lastVisitId = $this->model->getLastVisitId();
        $lastIndexedVisitId = $this->model->getLastIndexedVisitId();

        /*
         * Split the whole process on bunches, 5000 rows each
         */
        while ($lastVisitId > $lastIndexedVisitId) {
            $visitsAggregatedByUser = $this->model->getVisitsAggregatedByUser(
                $lastIndexedVisitId, $lastVisitId, self::INDEX_BUNCH_SIZE
            );
            $this->model->indexNewVisitsToUserIdsTable($visitsAggregatedByUser ?: array());
            $lastIndexedVisitId = $this->model->getLastIndexedVisitId();
        }
    }

    /**
     * Remove all data from index so it will be completely rebuilt with reindex() method
     */
    public function cleanIndex()
    {
        $this->model->cleanUserIdsTable();
    }
}
