<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions;

use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

/**
 * Runs the log queries of the Transitions report, for the API and for archiving pre-processed pages.
 */
class Model
{
    public const OTHERS_LABEL = 'Others';

    /**
     * Partitions of the internal referrers result: other action types, page actions and site searches.
     */
    public const PARTITION_OTHER = 0;
    public const PARTITION_PAGE = 1;
    public const PARTITION_SITE_SEARCH = 2;

    /**
     * The referrer of a visit: search engine, social network, AI assistant, website URL or campaign and keyword.
     */
    private const REFERRER_DATA_DIMENSION = 'CASE log_visit.referer_type
				WHEN ' . Common::REFERRER_TYPE_DIRECT_ENTRY . ' THEN \'\'
				WHEN ' . Common::REFERRER_TYPE_SEARCH_ENGINE . ' THEN log_visit.referer_name
				WHEN ' . Common::REFERRER_TYPE_SOCIAL_NETWORK . ' THEN log_visit.referer_name
				WHEN ' . Common::REFERRER_TYPE_AI_ASSISTANT . ' THEN log_visit.referer_name
				WHEN ' . Common::REFERRER_TYPE_WEBSITE . ' THEN log_visit.referer_url
				WHEN ' . Common::REFERRER_TYPE_CAMPAIGN . ' THEN CONCAT_WS(\' \', log_visit.referer_name, log_visit.referer_keyword)
			END';

    /**
     * The batched queries rank each page and partition separately in a partition `position of the page in the chunk *
     * 256 + partition`. Partitions are action or referrer types, which fit in a TINYINT, so no two pages overlap.
     */
    private const PAGE_PARTITION_COLUMN = 'transitions_page_partition';
    private const PAGE_PARTITION_FACTOR = 256;

    /**
     * The archived lists keep, of equal counts at the row limit, the lowest labels, whatever order the database
     * returns tied rows in. The live report's queries keep their order.
     */
    private const ACTIONS_TIE_ORDER = '`name`, `url_prefix`';
    private const REFERRERS_ORDER = '`' . Metrics::INDEX_NB_VISITS . '` DESC, `referrer_data`';

    /**
     * RankingQuery joins one table per partition, and MySQL joins at most 61 tables.
     */
    private const MAX_PARTITIONS_PER_QUERY = 50;

    /**
     * Referrer types, in the order the report lists them.
     */
    public const REFERRER_TYPES = [
        Common::REFERRER_TYPE_DIRECT_ENTRY,
        Common::REFERRER_TYPE_SEARCH_ENGINE,
        Common::REFERRER_TYPE_SOCIAL_NETWORK,
        Common::REFERRER_TYPE_AI_ASSISTANT,
        Common::REFERRER_TYPE_WEBSITE,
        Common::REFERRER_TYPE_CAMPAIGN,
    ];

    /**
     * Counts the previous actions of the action, ranked per partition. Reloads of the action are returned separately
     * in `excludedFromLimit`.
     *
     * @param float $timeLimit MAX_EXECUTION_TIME in seconds, none if not positive.
     * @param string $othersLabel Label of the row grouping the actions beyond the limit; it is put into the SQL
     *                            unescaped, so it must be a constant.
     * @return array<mixed> The ranking query result: `result` holds the rows by partition, `excludedFromLimit` the
     *                      reloads.
     */
    public function queryInternalReferrers(
        LogAggregator $logAggregator,
        int $idaction,
        string $actionType,
        int $limit,
        float $timeLimit = -1,
        string $othersLabel = self::OTHERS_LABEL
    ): array {
        $rankingQuery = new RankingQuery($limit);
        $rankingQuery->setOthersLabel($othersLabel);
        $rankingQuery->addLabelColumn(['name', 'url_prefix']);
        $rankingQuery->setColumnToMarkExcludedRows('is_self');
        $rankingQuery->partitionResultIntoMultipleGroups('action_partition', [self::PARTITION_OTHER, self::PARTITION_PAGE, self::PARTITION_SITE_SEARCH]);

        $type = $this->getColumnTypeSuffix($actionType);
        $mainActionType = Action::TYPE_PAGE_URL;
        $dimension = 'idaction_url_ref';

        if ($actionType == 'title') {
            $mainActionType = Action::TYPE_PAGE_TITLE;
            $dimension = 'idaction_name_ref';
        }

        $selects = [
            'log_action.name',
            'log_action.url_prefix',
            'CASE WHEN log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction) . ' THEN 1 ELSE 0 END AS `is_self`',
            'CASE
                WHEN log_action.type = ' . $mainActionType . ' THEN ' . self::PARTITION_PAGE . '
                        WHEN log_action.type = ' . Action::TYPE_SITE_SEARCH . ' THEN ' . self::PARTITION_SITE_SEARCH . '
                        ELSE ' . self::PARTITION_OTHER . '
                    END AS `action_partition`',
        ];

        $where = ' log_link_visit_action.idaction_' . $type . ' = ' . intval($idaction);

        if ($dimension == 'idaction_url_ref') {
            // site search referrers are logged with url_ref=NULL
            // when we find one, we have to join on name_ref
            $dimension = 'if ( %1$s.idaction_url_ref IS NULL, %1$s.idaction_name_ref, %1$s.idaction_url_ref )';
            $dimension = sprintf($dimension, 'log_link_visit_action');
            $joinLogActionOn = $dimension;
        } else {
            $joinLogActionOn = $dimension;
        }
        $metrics = [Metrics::INDEX_NB_ACTIONS];
        $data = $logAggregator->queryActionsByDimension(
            [$dimension],
            $where,
            $selects,
            $metrics,
            $rankingQuery,
            $joinLogActionOn,
            $secondaryOrderBy = "`name`",
            $timeLimit
        );

        return is_array($data) ? $data : [];
    }

    /**
     * Counts the following actions of the action, ranked per action type.
     *
     * @param float $timeLimit MAX_EXECUTION_TIME in seconds, none if not positive.
     * @param string $othersLabel Label of the row grouping the actions beyond the limit; it is put into the SQL
     *                            unescaped, so it must be a constant.
     * @return array<mixed> The rows by action type, see {@link getFollowingActionTypes()}.
     */
    public function queryFollowingActions(
        LogAggregator $logAggregator,
        int $idaction,
        string $actionType,
        int $limit,
        bool $includeLoops,
        float $timeLimit = -1,
        string $othersLabel = self::OTHERS_LABEL
    ): array {
        if ($actionType != 'title') {
            // specific setup for page urls
            $dimension = 'if ( %1$s.idaction_url IS NULL, %1$s.idaction_name, %1$s.idaction_url )';
            $dimension = sprintf($dimension, 'log_link_visit_action');
            // site search referrers are logged with url=NULL
            // when we find one, we have to join on name
            $joinLogActionColumn = $dimension;
            $selects = ['log_action.name', 'log_action.url_prefix', 'log_action.type'];
        } else {
            // specific setup for page titles:
            // join log_action on name and url and pick depending on url type
            // the table joined on url is log_action1
            $joinLogActionColumn = ['idaction_url', 'idaction_name'];
            $dimension = '
				CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.idaction
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.idaction
					' /* following download or outlink: use url */ . '
					ELSE log_action1.idaction
				END
			';
            $selects             = [
                'CASE
					' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.name
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.name
					' /* following download or outlink: use url */ . '
					ELSE log_action1.name
				END AS `name`',
                'CASE
                    ' /* following site search */ . '
					WHEN log_link_visit_action.idaction_url IS NULL THEN log_action2.type
					' /* following page view: use page title */ . '
					WHEN log_action1.type = ' . Action::TYPE_PAGE_URL . ' THEN log_action2.type
					' /* following download or outlink: use url */ . '
					ELSE log_action1.type
				END AS `type`',
                'NULL AS `url_prefix`',
            ];
        }

        $rankingQuery = new RankingQuery($limit);
        $rankingQuery->setOthersLabel($othersLabel);
        $rankingQuery->addLabelColumn(['name', 'url_prefix']);
        $rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->getFollowingActionTypes($actionType)));

        $type = $this->getColumnTypeSuffix($actionType);
        $where = 'log_link_visit_action.idaction_' . $type . '_ref = ' . intval($idaction);
        if (!$includeLoops) {
            $where .= ' AND (log_link_visit_action.idaction_' . $type . ' IS NULL OR '
                . 'log_link_visit_action.idaction_' . $type . ' != ' . intval($idaction) . ')';
        }

        $metrics = [Metrics::INDEX_NB_ACTIONS];
        $data = $logAggregator->queryActionsByDimension(
            [$dimension],
            $where,
            $selects,
            $metrics,
            $rankingQuery,
            $joinLogActionColumn,
            $secondaryOrderBy = "`name`",
            $timeLimit
        );

        return is_array($data) ? $data : [];
    }

    /**
     * Returns the report list of each action type of the following actions.
     *
     * @return array<int, string> list name by action type
     */
    public function getFollowingActionTypes(string $actionType): array
    {
        $types = [];
        if ($actionType != 'title') {
            $types[Action::TYPE_PAGE_URL] = 'followingPages';
        } else {
            $types[Action::TYPE_PAGE_TITLE] = 'followingPages';
        }

        // these types are available for both titles and urls
        $types[Action::TYPE_SITE_SEARCH] = 'followingSiteSearches';
        $types[Action::TYPE_OUTLINK] = 'outlinks';
        $types[Action::TYPE_DOWNLOAD] = 'downloads';

        return $types;
    }

    /**
     * Returns the label of an action row of the ranking queries: the URL without protocol for pages, the full URL
     * for outlinks and downloads, the title for page titles and the keyword for site searches.
     *
     * @param array<mixed> $row
     */
    public function getActionLabel(array $row, int $type): ?string
    {
        $name = isset($row['name']) && is_scalar($row['name']) ? (string) $row['name'] : null;

        if ($type == Action::TYPE_PAGE_TITLE) {
            if (empty($name)) {
                $name = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE);
            }
            return $name;
        }

        if ($type == Action::TYPE_OUTLINK || $type == Action::TYPE_DOWNLOAD) {
            $urlPrefix = isset($row['url_prefix']) && is_numeric($row['url_prefix']) ? (int) $row['url_prefix'] : null;
            return PageUrl::reconstructNormalizedUrl($name, $urlPrefix);
        }

        return $name;
    }

    /**
     * Counts the visits entering on the action, ranked per referrer type. Each row has the referrer type in
     * `referer_type` and the search engine, social network, AI assistant, website URL or campaign in
     * `referrer_data`.
     *
     * @param float $timeLimit MAX_EXECUTION_TIME in seconds, none if not positive.
     * @param string $othersLabel Label of the row grouping the referrers beyond the limit; it is put into the SQL
     *                            unescaped, so it must be a constant.
     * @return array<mixed> The rows by referrer type.
     */
    public function queryExternalReferrers(
        LogAggregator $logAggregator,
        int $idaction,
        string $actionType,
        int $limit,
        float $timeLimit = -1,
        string $othersLabel = self::OTHERS_LABEL
    ): array {
        $rankingQuery = new RankingQuery($limit);
        $rankingQuery->setOthersLabel($othersLabel);

        // we generate a single column that contains the interesting data for each referrer.
        // the reason we cannot group by referer_* becomes clear when we look at search engine keywords.
        // referer_url contains the url from the search engine, referer_keyword the keyword we want to
        // group by. when we group by both, we don't get a single column for the keyword but instead
        // one column per keyword + search engine url. this way, we could not get the top keywords using
        // the ranking query.
        $dimensions = [
            'referrer_data' => self::REFERRER_DATA_DIMENSION, 'referer_type',
        ];
        $rankingQuery->addLabelColumn('referrer_data');

        // get one limited group per referrer type
        $rankingQuery->partitionResultIntoMultipleGroups('referer_type', self::REFERRER_TYPES);

        $type    = $this->getColumnTypeSuffix($actionType);
        $where   = 'visit_entry_idaction_' . $type . ' = ' . intval($idaction);
        $metrics = [Metrics::INDEX_NB_VISITS];
        $data    = $logAggregator->queryVisitsByDimension($dimensions, $where, [], $metrics, $rankingQuery, false, $timeLimit);

        return is_array($data) ? $data : [];
    }

    /**
     * Like {@link queryInternalReferrers()} for several page URLs at once, with one log scan per chunk of pages.
     *
     * @param list<int> $idActions
     * @return array<int, array{result: array<int, array<mixed>>, excludedFromLimit: array<mixed>}> by idaction
     */
    public function queryInternalReferrersOfPages(LogAggregator $logAggregator, array $idActions, int $limit, string $othersLabel, float $timeLimit = -1): array
    {
        $partitions = [self::PARTITION_OTHER, self::PARTITION_PAGE, self::PARTITION_SITE_SEARCH];
        $dimension = sprintf('if ( %1$s.idaction_url_ref IS NULL, %1$s.idaction_name_ref, %1$s.idaction_url_ref )', 'log_link_visit_action');
        $partition = 'CASE
            WHEN log_action.type = ' . Action::TYPE_PAGE_URL . ' THEN ' . self::PARTITION_PAGE . '
            WHEN log_action.type = ' . Action::TYPE_SITE_SEARCH . ' THEN ' . self::PARTITION_SITE_SEARCH . '
            ELSE ' . self::PARTITION_OTHER . ' END';

        $pages = [];
        foreach ($this->chunkPages($idActions, count($partitions)) as $chunk) {
            $pagePartition = $this->getPagePartitionExpression('log_link_visit_action.idaction_url', $chunk, $partition);
            $rankingQuery = $this->makePagesRankingQuery($chunk, $partitions, $limit, $othersLabel);
            $rankingQuery->addLabelColumn(['name', 'url_prefix']);
            $rankingQuery->setColumnToMarkExcludedRows('is_self');

            $data = $logAggregator->queryActionsByDimension(
                [$dimension, self::PAGE_PARTITION_COLUMN => $pagePartition],
                'log_link_visit_action.idaction_url IN (' . implode(',', $chunk) . ')',
                [
                    'log_action.name',
                    'log_action.url_prefix',
                    'CASE WHEN log_link_visit_action.idaction_url_ref = log_link_visit_action.idaction_url THEN 1 ELSE 0 END AS `is_self`',
                ],
                [Metrics::INDEX_NB_ACTIONS],
                $rankingQuery,
                $dimension,
                self::ACTIONS_TIE_ORDER,
                $timeLimit
            );
            $data = is_array($data) ? $data : [];

            $excludedRowsByPage = array_fill_keys($chunk, []);
            foreach (is_array($data['excludedFromLimit'] ?? null) ? $data['excludedFromLimit'] : [] as $row) {
                $idAction = is_array($row) && is_numeric($row[self::PAGE_PARTITION_COLUMN] ?? null)
                    ? ($chunk[intdiv((int) $row[self::PAGE_PARTITION_COLUMN], self::PAGE_PARTITION_FACTOR)] ?? null)
                    : null;
                if ($idAction !== null) {
                    $excludedRowsByPage[$idAction][] = $row;
                }
            }

            $rowsByPage = $this->splitPagePartitions($chunk, is_array($data['result'] ?? null) ? $data['result'] : []);
            foreach ($chunk as $idAction) {
                $pages[$idAction] = ['result' => $rowsByPage[$idAction] ?? [], 'excludedFromLimit' => $excludedRowsByPage[$idAction] ?? []];
            }
        }

        return $pages;
    }

    /**
     * Like {@link queryFollowingActions()} without reloads for several page URLs at once, with one log scan per chunk
     * of pages.
     *
     * @param list<int> $idActions
     * @return array<int, array<int, array<mixed>>> rows by action type by idaction
     */
    public function queryFollowingActionsOfPages(LogAggregator $logAggregator, array $idActions, int $limit, string $othersLabel, float $timeLimit = -1): array
    {
        $types = array_keys($this->getFollowingActionTypes('url'));
        $dimension = sprintf('if ( %1$s.idaction_url IS NULL, %1$s.idaction_name, %1$s.idaction_url )', 'log_link_visit_action');
        $pages = [];
        foreach ($this->chunkPages($idActions, count($types)) as $chunk) {
            $pagePartition = $this->getPagePartitionExpression('log_link_visit_action.idaction_url_ref', $chunk, 'log_action.type');
            $rankingQuery = $this->makePagesRankingQuery($chunk, $types, $limit, $othersLabel);
            $rankingQuery->addLabelColumn(['name', 'url_prefix']);

            $data = $logAggregator->queryActionsByDimension(
                [$dimension, self::PAGE_PARTITION_COLUMN => $pagePartition],
                'log_link_visit_action.idaction_url_ref IN (' . implode(',', $chunk) . ')'
                    . ' AND (log_link_visit_action.idaction_url IS NULL'
                    . ' OR log_link_visit_action.idaction_url != log_link_visit_action.idaction_url_ref)',
                ['log_action.name', 'log_action.url_prefix'],
                [Metrics::INDEX_NB_ACTIONS],
                $rankingQuery,
                $dimension,
                self::ACTIONS_TIE_ORDER,
                $timeLimit
            );

            $pages += $this->splitPagePartitions($chunk, is_array($data) ? $data : []);
        }

        return $pages;
    }

    /**
     * Like {@link queryExternalReferrers()} for several page URLs at once, with one log scan per chunk of pages.
     *
     * @param list<int> $idActions
     * @return array<int, array<int, array<mixed>>> rows by referrer type by idaction
     */
    public function queryExternalReferrersOfPages(LogAggregator $logAggregator, array $idActions, int $limit, string $othersLabel, float $timeLimit = -1): array
    {
        $pages = [];
        foreach ($this->chunkPages($idActions, count(self::REFERRER_TYPES)) as $chunk) {
            $dimensions = [
                'referrer_data' => self::REFERRER_DATA_DIMENSION,
                'referer_type',
                self::PAGE_PARTITION_COLUMN => $this->getPagePartitionExpression('log_visit.visit_entry_idaction_url', $chunk, 'log_visit.referer_type'),
            ];
            $rankingQuery = $this->makePagesRankingQuery($chunk, self::REFERRER_TYPES, $limit, $othersLabel);
            $rankingQuery->addLabelColumn('referrer_data');

            $data = $logAggregator->queryVisitsByDimension(
                $dimensions,
                'log_visit.visit_entry_idaction_url IN (' . implode(',', $chunk) . ')',
                [],
                [Metrics::INDEX_NB_VISITS],
                $rankingQuery,
                self::REFERRERS_ORDER,
                $timeLimit
            );

            $pages += $this->splitPagePartitions($chunk, is_array($data) ? $data : []);
        }

        return $pages;
    }

    /**
     * @param list<int> $idActions
     * @return list<list<int>>
     */
    private function chunkPages(array $idActions, int $partitionsPerPage): array
    {
        return array_chunk($idActions, max(1, intdiv(self::MAX_PARTITIONS_PER_QUERY, $partitionsPerPage)));
    }

    /**
     * Ranks the rows of each page and partition separately, see {@link PAGE_PARTITION_COLUMN}.
     *
     * @param list<int> $idActions
     * @param list<int> $partitions
     */
    private function makePagesRankingQuery(array $idActions, array $partitions, int $limit, string $othersLabel): RankingQuery
    {
        $values = [];
        foreach (array_keys($idActions) as $position) {
            foreach ($partitions as $partition) {
                $values[] = $position * self::PAGE_PARTITION_FACTOR + $partition;
            }
        }

        $rankingQuery = new RankingQuery($limit);
        $rankingQuery->setOthersLabel($othersLabel);
        $rankingQuery->partitionResultIntoMultipleGroups(self::PAGE_PARTITION_COLUMN, $values);

        return $rankingQuery;
    }

    /**
     * @param list<int> $idActions the pages of the chunk
     */
    private function getPagePartitionExpression(string $pageColumn, array $idActions, string $partition): string
    {
        $position = 'CASE ' . $pageColumn;
        foreach ($idActions as $index => $idAction) {
            $position .= ' WHEN ' . $idAction . ' THEN ' . $index;
        }

        return '(' . $position . ' END * ' . self::PAGE_PARTITION_FACTOR . ' + ' . $partition . ')';
    }

    /**
     * @param list<int> $idActions the pages of the chunk
     * @param array<mixed> $rowsByPagePartition
     * @return array<int, array<int, array<mixed>>> rows by partition by idaction, for every page of the chunk
     */
    private function splitPagePartitions(array $idActions, array $rowsByPagePartition): array
    {
        $pages = array_fill_keys($idActions, []);
        foreach ($rowsByPagePartition as $pagePartition => $rows) {
            $idAction = $idActions[intdiv((int) $pagePartition, self::PAGE_PARTITION_FACTOR)] ?? null;
            if ($idAction !== null) {
                $pages[$idAction][(int) $pagePartition % self::PAGE_PARTITION_FACTOR] = is_array($rows) ? $rows : [];
            }
        }

        return $pages;
    }

    private function getColumnTypeSuffix(string $actionType): string
    {
        if ($actionType == 'title') {
            return 'name';
        }
        return 'url';
    }
}
