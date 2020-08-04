<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin;

use Piwik\ArchiveProcessor;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Sequence;
use Psr\Log\LoggerInterface;

/**
 * Class Archiver
 *
 * Archiver is class processing raw data into ready ro read reports.
 * It must implement two methods for aggregating daily reports
 * aggregateDayReport() and other for summing daily reports into periods
 * like week, month, year or custom range aggregateMultipleReports().
 *
 * For more detailed information about Archiver please visit Piwik developer guide
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    /**
     * It is a good practice to store your archive names (reports stored in database)
     * in Archiver class constants. You can define as many record names as you want
     * for your plugin.
     *
     * Also important thing is that record name must be prefixed with plugin name.
     *
     * This is only an example record name, so feel free to change it to suit your needs.
     */
    const EXAMPLEPLUGIN_ARCHIVE_RECORD = "ExamplePlugin_archive_record";
    const EXAMPLEPLUGIN_METRIC_NAME = 'ExamplePlugin_example_metric';
    const EXAMPLEPLUGIN_CONST_METRIC_NAME = 'ExamplePlugin_example_metric2';

    private $daysFrom = '2016-07-08';

    /**
     * @var string
     */
    private $requestedReport = null;

    public function __construct(ArchiveProcessor $processor)
    {
        parent::__construct($processor);

        $this->requestedReport = $processor->getParams()->getArchiveOnlyReport();
        if ($this->requestedReport) {
            $processor->getParams()->setIsPartialArchive(true);
        }

        $this->createSequence();
    }

    public function aggregateDayReport()
    {
        /**
         * inside this method you can implement your LogAggreagator usage
         * to process daily reports, this one uses idvisitor to group results.
         *
         * $visitorMetrics = $this
         * ->getLogAggregator()
         * ->getMetricsFromVisitByDimension('idvisitor')
         * ->asDataTable();
         * $visitorReport = $visitorMetrics->getSerialized();
         * $this->getProcessor()->insertBlobRecord(self::EXAMPLEPLUGIN_ARCHIVE_RECORD, $visitorReport);
         */

        if ($this->isArchiving(self::EXAMPLEPLUGIN_METRIC_NAME)) {
            // insert a test numeric metric that is the difference in days between the day we're archiving and
            // $this->daysFrom.
            $daysFrom = Date::factory($this->daysFrom);
            $date = $this->getProcessor()->getParams()->getPeriod()->getDateStart();

            $differenceInSeconds = $daysFrom->getTimestamp() - $date->getTimestamp();
            $differenceInDays = round($differenceInSeconds / 86400);

            $this->getProcessor()->insertNumericRecord(self::EXAMPLEPLUGIN_METRIC_NAME, $differenceInDays);
        }

        if ($this->isArchiving(self::EXAMPLEPLUGIN_CONST_METRIC_NAME)) {
            $archiveCount = $this->incrementArchiveCount();
            $archiveCount = 50 + $archiveCount;
            $archiveCount += 5 - ($archiveCount % 5); // round up to nearest 5 multiple to avoid random test failures
            $this->getProcessor()->insertNumericRecord(self::EXAMPLEPLUGIN_CONST_METRIC_NAME, $archiveCount);
        }
    }

    public function aggregateMultipleReports()
    {
        /**
         * Inside this method you can simply point daily records
         * to be summed. This work for most cases.
         * However if needed, also custom queries can be implemented
         * for periods to achieve more acurrate results.
         *
         * $this->getProcessor()->aggregateDataTableRecords(self::EXAMPLEPLUGIN_ARCHIVE_RECORD);
         */

        $reports = [];
        if ($this->isArchiving(self::EXAMPLEPLUGIN_METRIC_NAME)) {
            $reports[] = self::EXAMPLEPLUGIN_METRIC_NAME;
        }
        if ($this->isArchiving(self::EXAMPLEPLUGIN_CONST_METRIC_NAME)) {
            $reports[] = self::EXAMPLEPLUGIN_CONST_METRIC_NAME;
        }
        $this->getProcessor()->aggregateNumericMetrics($reports);
    }

    private function incrementArchiveCount()
    {
        $sequence = new Sequence('ExamplePlugin_archiveCount');
        $result = $sequence->getNextId();
        return $result;
    }

    private function isArchiving(string $reportName)
    {
        return empty($this->requestedReport) || $this->requestedReport == $reportName;
    }

    private function createSequence()
    {
        $sequence = new Sequence('ExamplePlugin_archiveCount');
        if (!$sequence->exists()) {
            for ($i = 0; $i < 100; ++$i) {
                try {
                    $sequence->create();
                    break;
                } catch (\Exception $ex) {
                    // ignore
                }
            }
        }
    }
}
