<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;

/**
 * Archives a day within a report request, whose query of the day's visits gets a time limit like the report's own
 * log queries. A segment is queried without the segment cache, whose filling of a temporary table can't be time
 * limited.
 */
final class TimeLimitedPluginsArchiver extends PluginsArchiver
{
    private float $timeLimit;

    /**
     * @param float $timeLimit MAX_EXECUTION_TIME in seconds, none if not positive
     */
    public function __construct(Parameters $params, float $timeLimit)
    {
        $archiveWriter = new ArchiveWriter($params);
        parent::__construct($params, $archiveWriter);

        $logAggregator = new LogAggregator($params);
        $logAggregator->setQueryOriginHint('Transitions');
        $this->archiveProcessor = new ArchiveProcessor($params, $archiveWriter, $logAggregator);
        $this->timeLimit = $timeLimit;
    }

    /**
     * As the parent, with the time limit.
     *
     * @return array<string, mixed>
     */
    protected function aggregateDayVisitsMetrics()
    {
        $query = $this->archiveProcessor->getLogAggregator()->queryVisitsByDimension([], false, [], false, false, false, $this->timeLimit);
        $metrics = $this->convertMetricsIdToName($query instanceof \Zend_Db_Statement_Interface ? $query->fetch() : []);
        $this->archiveProcessor->insertNumericRecords($metrics);

        return $metrics;
    }
}
