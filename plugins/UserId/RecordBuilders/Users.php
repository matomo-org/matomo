<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserId\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\UserId\Archiver;
use Piwik\RankingQuery;

class Users extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();
        $this->maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_userid_users'];
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::USERID_ARCHIVE_RECORD),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $record = new DataTable();

        $visitorIdsUserIdsMap = [];

        $userIdFieldName = Archiver::USER_ID_FIELD;
        $visitorIdFieldName = Archiver::VISITOR_ID_FIELD;

        $rankingQueryLimit = $this->getRankingQueryLimit();

        $rankingQuery = false;
        if ($rankingQueryLimit > 0) {
            $rankingQuery = new RankingQuery($rankingQueryLimit);
            $rankingQuery->addLabelColumn($userIdFieldName);
            $rankingQuery->addLabelColumn($visitorIdFieldName);
        }

        /** @var \Zend_Db_Statement $query */
        $query = $archiveProcessor->getLogAggregator()->queryVisitsByDimension(
            array($userIdFieldName),
            "log_visit.$userIdFieldName IS NOT NULL AND log_visit.$userIdFieldName != ''",
            array("LOWER(HEX($visitorIdFieldName)) as $visitorIdFieldName"),
            false,
            $rankingQuery,
            $userIdFieldName . ' ASC'
        );

        $rowsCount = 0;
        foreach ($query as $row) {
            $rowsCount++;

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_NB_ACTIONS => $row[Metrics::INDEX_NB_ACTIONS],
                Metrics::INDEX_NB_USERS => $row[Metrics::INDEX_NB_USERS],
                Metrics::INDEX_MAX_ACTIONS => $row[Metrics::INDEX_MAX_ACTIONS],
                Metrics::INDEX_SUM_VISIT_LENGTH => $row[Metrics::INDEX_SUM_VISIT_LENGTH],
                Metrics::INDEX_BOUNCE_COUNT => $row[Metrics::INDEX_BOUNCE_COUNT],
                Metrics::INDEX_NB_VISITS_CONVERTED => $row[Metrics::INDEX_NB_VISITS_CONVERTED],
            ];

            $record->sumRowWithLabel($row[$userIdFieldName], $columns);

            // Remember visitor ID per user. We use it to fill metadata before actual inserting rows to DB.
            if (
                !empty($row[Archiver::USER_ID_FIELD])
                && !empty($row[Archiver::VISITOR_ID_FIELD])
            ) {
                $visitorIdsUserIdsMap[$row[Archiver::USER_ID_FIELD]] = $row[Archiver::VISITOR_ID_FIELD];
            }
        }

        $this->setVisitorIds($record, $visitorIdsUserIdsMap);

        return [Archiver::USERID_ARCHIVE_RECORD => $record];
    }

    /**
     * Fill visitor ID as metadata before actual inserting rows to DB.
     *
     * @param DataTable $dataTable
     */
    private function setVisitorIds(DataTable $dataTable, array $visitorIdsUserIdsMap)
    {
        foreach ($dataTable->getRows() as $row) {
            $userId = $row->getColumn('label');
            if (isset($visitorIdsUserIdsMap[$userId])) {
                $row->setMetadata(Archiver::VISITOR_ID_FIELD, $visitorIdsUserIdsMap[$userId]);
            }
        }
    }

    private function getRankingQueryLimit()
    {
        $configGeneral = Config::getInstance()->General;
        $configLimit = $configGeneral['archiving_ranking_query_row_limit'];
        $limit = $configLimit == 0 ? 0 : max(
            $configLimit,
            $this->maxRowsInTable
        );
        return $limit;
    }
}
