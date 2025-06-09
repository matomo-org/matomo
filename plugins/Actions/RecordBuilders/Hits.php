<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\Archiver;

class Hits extends ArchiveProcessor\RecordBuilder
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_NUMERIC, Archiver::METRIC_HITS_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $query = $archiveProcessor->getLogAggregator()->queryActionsByDimension(
            [],
            '',
            ['count(distinct log_link_visit_action.idlink_va) as `' . PiwikMetrics::INDEX_NB_HITS . '`'],
            [],
            null
        );
        $data = $query->fetch();
        $nbHits = $data[PiwikMetrics::INDEX_NB_HITS];

        return [
            Archiver::METRIC_HITS_RECORD_NAME => $nbHits,
        ];
    }
}
