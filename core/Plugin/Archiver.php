<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Piwik\ArchiveProcessor;
use Piwik\Config as PiwikConfig;
use Piwik\ErrorHandler;

/**
 * The base class that should be extended by plugins that compute their own
 * analytics data.
 *
 * Descendants should implement the {@link aggregateDayReport()} and {@link aggregateMultipleReports()}
 * methods.
 *
 * Both of these methods should persist analytics data using the {@link \Piwik\ArchiveProcessor}
 * instance returned by {@link getProcessor()}. The {@link aggregateDayReport()} method should
 * compute analytics data using the {@link \Piwik\DataAccess\LogAggregator} instance
 * returned by {@link getLogAggregator()}.
 *
 * ### Examples
 *
 * **Extending Archiver**
 *
 *     class MyArchiver extends Archiver
 *     {
 *         public function aggregateDayReport()
 *         {
 *             $logAggregator = $this->getLogAggregator();
 *
 *             $data = $logAggregator->queryVisitsByDimension(...);
 *
 *             $dataTable = new DataTable();
 *             $dataTable->addRowsFromSimpleArray($data);
 *
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->insertBlobRecords('MyPlugin_myReport', $dataTable->getSerialized(500));
 *         }
 *
 *         public function aggregateMultipleReports()
 *         {
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->aggregateDataTableRecords('MyPlugin_myReport', 500);
 *         }
 *     }
 *
 * @api
 */
abstract class Archiver
{
    /**
     * @var \Piwik\ArchiveProcessor
     */
    private $processor;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var mixed
     */
    protected $maximumRows;

    /**
     * Constructor.
     *
     * @param ArchiveProcessor $processor The ArchiveProcessor instance to use when persisting archive
     *                                    data.
     */
    public function __construct(ArchiveProcessor $processor)
    {
        $this->maximumRows = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $processor;
        $this->enabled = true;
    }

    /**
     * @ignore
     */
    final public function callAggregateDayReport()
    {
        try {
            ErrorHandler::pushFatalErrorBreadcrumb(static::class);

            $this->aggregateDayReport();
        } finally {
            ErrorHandler::popFatalErrorBreadcrumb();
        }
    }

    /**
     * @ignore
     */
    final public function callAggregateMultipleReports()
    {
        try {
            ErrorHandler::pushFatalErrorBreadcrumb(static::class);

            $this->aggregateMultipleReports();
        } finally {
            ErrorHandler::popFatalErrorBreadcrumb();
        }
    }

    /**
     * Archives data for a day period.
     *
     * Implementations of this method should do more computation intensive activities such
     * as aggregating data across log tables. Since this method only deals w/ data logged for a day,
     * aggregating individual log table rows isn't a problem. Doing this for any larger period,
     * however, would cause performance degradation.
     *
     * Aggregate log table rows using a {@link Piwik\DataAccess\LogAggregator} instance. Get a
     * {@link Piwik\DataAccess\LogAggregator} instance using the {@link getLogAggregator()} method.
     */
    abstract public function aggregateDayReport();

    /**
     * Archives data for a non-day period.
     *
     * Implementations of this method should only aggregate existing reports of subperiods of the
     * current period. For example, it is more efficient to aggregate reports for each day of a
     * week than to aggregate each log entry of the week.
     *
     * Use {@link Piwik\ArchiveProcessor::aggregateNumericMetrics()} and {@link Piwik\ArchiveProcessor::aggregateDataTableRecords()}
     * to aggregate archived reports. Get the {@link Piwik\ArchiveProcessor} instance using the {@link getProcessor()}
     * method.
     */
    abstract public function aggregateMultipleReports();

    /**
     * Returns a {@link Piwik\ArchiveProcessor} instance that can be used to insert archive data for
     * the period, segment and site we are archiving data for.
     *
     * @return \Piwik\ArchiveProcessor
     * @api
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Returns a {@link Piwik\DataAccess\LogAggregator} instance that can be used to aggregate log table rows
     * for this period, segment and site.
     *
     * @return \Piwik\DataAccess\LogAggregator
     * @api
     */
    protected function getLogAggregator()
    {
        return $this->getProcessor()->getLogAggregator();
    }

    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Whether this Archiver should be used or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * By overwriting this method and returning true, a plugin archiver can force the archiving to run even when there
     * was no visit for the website/date/period/segment combination
     * (by default, archivers are skipped when there is no visit).
     *
     * @return bool
     */
    public static function shouldRunEvenWhenNoVisits()
    {
        return false;
    }

    protected function isRequestedReport(string $reportName)
    {
        $requestedReport = $this->getProcessor()->getParams()->getArchiveOnlyReport();

        return empty($requestedReport) || $requestedReport == $reportName;
    }
}
