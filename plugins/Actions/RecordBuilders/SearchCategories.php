<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\DataTable;
use Piwik\Plugins\Actions\Archiver;
use Piwik\Plugins\Actions\ArchivingHelper;

class SearchCategories extends ArchiveProcessor\RecordBuilder
{
    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();
        $query = $logAggregator->queryActionsByDimension(['search_cat'], "%s.search_cat != '' AND %s.search_cat IS NOT NULL");

        $table = new DataTable();

        while ($row = $query->fetch()) {
            $label = $row['search_cat'];
            unset($row['search_cat']);

            $row = new DataTable\Row([DataTable\Row::COLUMNS => ['label' => $label] + $row]);
            $table->addRow($row);
        }

        return [
            Archiver::SITE_SEARCH_CATEGORY_RECORD_NAME => $table,
        ];
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::SITE_SEARCH_CATEGORY_RECORD_NAME)
                ->setMaxRowsInTable(ArchivingHelper::$maximumRowsInDataTableSiteSearch),
        ];
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        return $archiveProcessor->getParams()->getSite()->isSiteSearchEnabled();
    }
}
