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
use Piwik\DataTable;

/**
 * The RecordBuilder class processes raw data into ready ro read reports.
 * It must implement two methods, one for aggregating daily reports
 * aggregate() and another returning information about the record.
 *
 * Plugins can have more than one RecordBuilder, and should try to divide them
 * up into the smallest number they can, while still performing as few total log aggregation
 * queries as possible (this results in improved performance overall).
 *
 * For more detailed information about RecordBuilders please visit Matomo developer guide
 * http://developer.piwik.org/api-reference/Piwik/ArchiveProcessor/RecordBuilder
 */
class ExampleBlob extends RecordBuilder
{
    const EXAMPLEPLUGIN_BLOB_RECORD_NAME = 'ExamplePlugin_exampleBlob';

    /**
     * This method should return the list of records this RecordBuilder creates. This example
     * archives one metric, so we return some information about them.
     */
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, self::EXAMPLEPLUGIN_BLOB_RECORD_NAME),
        ];
    }

    /**
     * inside this method you can implement your LogAggregator usage
     * to process daily reports. this code for example, uses idvisitor to group results:
     *
     * ```
     * $record = new DataTable();
     *
     * $query = $archiveProcessor->getLogAggregator()->queryVisitsByDimension(['idvisitor']);
     * while ($row = $query->fetch()) {
     *     $label = $row['idvisitor'];
     *     unset($row['idvisitor']);
     *     $record->sumRowWithLabel($label, $row);
     * }
     *
     * return [self::EXAMPLEPLUGIN_ARCHIVE_RECORD => $record];
     * ```
     *
     * non-day periods will automatically be aggregated together
     */
    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $record = new DataTable();

        $query = $archiveProcessor->getLogAggregator()->queryVisitsByDimension(['idvisitor']);
        while ($row = $query->fetch()) {
            $label = $row['idvisitor'];
            unset($row['idvisitor']);
            $record->sumRowWithLabel($label, $row);
        }

        return [self::EXAMPLEPLUGIN_BLOB_RECORD_NAME => $record];
    }
}
