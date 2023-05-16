<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Date;
use Piwik\Option;

/**
 * The RecordBuilder class processes raw data into ready ro read reports.
 * It must implement two methods, one for aggregating daily reports
 * aggregate() and another returning information about the record.
 *
 * Plugins can have more than one RecordBuilder, and should try to divide them
 * up into the smallest number they can (this results in improved performance overall).
 *
 * For more detailed information about RecordBuilders please visit Matomo developer guide
 * http://developer.piwik.org/api-reference/Piwik/ArchiveProcessor/RecordBuilder
 */
class ExampleRecordBuilder extends RecordBuilder
{
    /**
     * It is good practice to store your archive names (reports stored in database)
     * as class constants. You can define as many record names as you want
     * for your plugin.
     *
     * Also important to note that record names must be prefixed with the plugin name.
     *
     * These are only example record names, so feel free to change them to suit your needs.
     */
    const EXAMPLEPLUGIN_ARCHIVE_RECORD = "ExamplePlugin_archive_record";
    const EXAMPLEPLUGIN_METRIC_NAME = 'ExamplePlugin_example_metric';
    const EXAMPLEPLUGIN_CONST_METRIC_NAME = 'ExamplePlugin_example_metric2';

    private $daysFrom = '2016-07-08';

    /**
     * This method should return the list of records this RecordBuilder creates. This example
     * archives two metrics, so we return some information about them.
     */
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            Record::make(Record::TYPE_NUMERIC, self::EXAMPLEPLUGIN_METRIC_NAME),
            Record::make(Record::TYPE_NUMERIC, self::EXAMPLEPLUGIN_CONST_METRIC_NAME),
        ];
    }

    /**
     * inside this method you can implement your LogAggregator usage
     * to process daily reports. this code for example, uses idvisitor to group results:
     *
     * ```
     * $visitorMetrics = $this
     * ->getLogAggregator()
     * ->getMetricsFromVisitByDimension('idvisitor')
     * ->asDataTable();
     * $visitorReport = $visitorMetrics->getSerialized();
     * return [self::EXAMPLEPLUGIN_ARCHIVE_RECORD => $visitorReport];
     * ```
     *
     * non-day periods will automatically be aggregated together
     */
    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        $params = $archiveProcessor->getParams();

        $records = [];

        if ($params->isRequestedReport(self::EXAMPLEPLUGIN_METRIC_NAME)) {
            // insert a test numeric metric that is the difference in days between the day we're archiving and
            // $this->daysFrom.
            $daysFrom = Date::factory($this->daysFrom);
            $date = $params->getPeriod()->getDateStart();

            $differenceInSeconds = $daysFrom->getTimestamp() - $date->getTimestamp();
            $differenceInDays = round($differenceInSeconds / 86400);

            $records[self::EXAMPLEPLUGIN_METRIC_NAME] = $differenceInDays;
        }

        if ($params->isRequestedReport(self::EXAMPLEPLUGIN_CONST_METRIC_NAME)) {
            $callCount = $this->getAndIncrementArchiveCallCount($archiveProcessor);
            $metricValue = $callCount > 0 ? 1 : 0;

            $records[self::EXAMPLEPLUGIN_CONST_METRIC_NAME] = $metricValue;
        }

        return $records;
    }

    private function getAndIncrementArchiveCallCount(ArchiveProcessor $archiveProcessor)
    {
        $params = $archiveProcessor->getParams();
        $optionName = 'ExamplePlugin.metricValue.' . md5($params->getSite()->getId() . '.' . $params->getPeriod()->getRangeString()
                . '.' . $params->getPeriod()->getLabel() . '.' . $params->getSegment()->getHash());
        $value = (int) Option::get($optionName);
        Option::set($optionName, $value + 1);
        return $value;
    }
}
