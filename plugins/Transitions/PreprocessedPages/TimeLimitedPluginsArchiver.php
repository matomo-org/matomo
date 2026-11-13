<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\PluginsArchiver;

/**
 * Archives a day within a report request, whose query of the day's visits gets a time limit like the report's own
 * log queries.
 */
final class TimeLimitedPluginsArchiver extends PluginsArchiver
{
    private float $timeLimit;

    /**
     * @param float $timeLimit MAX_EXECUTION_TIME in seconds, none if not positive
     */
    public function __construct(Parameters $params, float $timeLimit)
    {
        parent::__construct($params);

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
