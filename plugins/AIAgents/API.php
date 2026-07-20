<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AIAgents;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\API as PluginAPI;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Site;

/**
 * Provides reporting API methods for distinguishing AI agent traffic from human traffic.
 *
 * @method static \Piwik\Plugins\AIAgents\API getInstance()
 */
class API extends PluginAPI
{
    public const AI_AGENT_SEGMENT       = 'aiAgentName!=';
    public const AI_AGENT_COLUMN_SUFFIX = '_ai_agent';

    public const HUMAN_SEGMENT       = 'aiAgentName==';
    public const HUMAN_COLUMN_SUFFIX = '_human';

    /**
     * Returns visit summary metrics split between AI agents and human visitors.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string $segment Custom segment to append to the AI-agent and human traffic filters.
     * @param list<string>|string $columns Metrics to include in the response.
     *                                     Accepts a comma-separated list or array of metric names.
     * @return DataTable|DataTable\Map Visit summary metrics for the requested site and period, with AI-agent and
     *                            human-specific column suffixes.
     */
    public function get(
        $idSite,
        string $period,
        string $date,
        string $segment = '',
        $columns = ''
    ): DataTableInterface {
        Piwik::checkUserHasViewAccess($idSite);

        $columns = Piwik::getArrayFromApiParameter($columns);

        if ($idSite === 'all' || count(Site::getIdSitesFromIdSitesString($idSite, false, true)) > 1) {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('idSite');
        } elseif (Period::isMultiplePeriod($date, $period)) {
            $resultSet = new DataTable\Map();
            $resultSet->setKeyName('period');
        } else {
            $resultSet = new DataTable\Simple();
        }

        $clientTypes = [
            self::AI_AGENT_COLUMN_SUFFIX => self::AI_AGENT_SEGMENT,
            self::HUMAN_COLUMN_SUFFIX    => self::HUMAN_SEGMENT,
        ];

        foreach ($clientTypes as $columnSuffix => $clientTypeSegment) {
            $modifiedSegment      = Segment::combine($segment, SegmentExpression::AND_DELIMITER, $clientTypeSegment);
            $columnsForClientType = empty($columns) ? [] : $this->filterColumnsBySuffix($columns, $columnSuffix);

            // Only make the API call if either $columns is empty (i.e. no list of columns was passed in, so we
            // should fetch all columns) or if one of the columns that was passed in is for this client type
            if (!empty($columns) && empty($columnsForClientType)) {
                continue;
            }

            $params = [
                'idSite'         => $idSite,
                'period'         => $period,
                'date'           => $date,
                'segment'        => $modifiedSegment,
                'columns'        => implode(',', $columnsForClientType),
                'format'         => 'original',
                'format_metrics' => 0,
            ];

            /** @var DataTable\Map $response */
            $response = Request::processRequest('VisitsSummary.get', $params);

            $this->addSuffixToTableColumns($response, $columnSuffix);

            $merger = new MergeDataTables();
            $merger->mergeDataTables($resultSet, $response);
        }

        return $resultSet;
    }

    /**
     * @param array<string> $requestedColumns
     *
     * @return array<string>
     */
    protected function filterColumnsBySuffix(array $requestedColumns, string $suffix): array
    {
        $columns = [];

        foreach ($requestedColumns as $column) {
            if (!str_ends_with($column, $suffix)) {
                continue;
            }

            $columns[] = str_replace($suffix, '', $column);
        }

        return $columns;
    }

    protected function addSuffixToTableColumns(DataTableInterface $table, string $suffix): void
    {
        $renames = [];

        foreach ($table->getColumns() as $oldColumn) {
            $renames[$oldColumn] = $oldColumn . $suffix;
        }

        $table->filter('ReplaceColumnNames', [$renames]);
    }
}
