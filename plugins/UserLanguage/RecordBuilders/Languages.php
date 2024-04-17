<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserLanguage\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Common;
use Piwik\Config as PiwikConfig;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Metrics;
use Piwik\Plugins\UserLanguage\Archiver;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserLanguage/functions.php';

class Languages extends RecordBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->maxRowsInTable = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->columnToSortByBeforeTruncation = Metrics::INDEX_NB_VISITS;
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::LANGUAGE_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $query = $archiveProcessor->getLogAggregator()->queryVisitsByDimension(["label" => Archiver::LANGUAGE_DIMENSION]);
        $countryCodes = $regionDataProvider->getCountryList($includeInternalCodes = true);

        $metricsByLanguage = new DataTable();

        while ($row = $query->fetch()) {
            $langCode = Common::extractLanguageCodeFromBrowserLanguage($row['label']);
            $countryCode = Common::extractCountryCodeFromBrowserLanguage($row['label'], $countryCodes, $enableLanguageToCountryGuess = true);

            if ($countryCode == 'xx' || $countryCode == $langCode) {
                $label = $langCode;
            } else {
                $label = $langCode . '-' . $countryCode;
            }

            $columns = [
                Metrics::INDEX_NB_UNIQ_VISITORS => $row[Metrics::INDEX_NB_UNIQ_VISITORS],
                Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                Metrics::INDEX_NB_ACTIONS => $row[Metrics::INDEX_NB_ACTIONS],
                Metrics::INDEX_NB_USERS => $row[Metrics::INDEX_NB_USERS],
                Metrics::INDEX_MAX_ACTIONS => $row[Metrics::INDEX_MAX_ACTIONS],
                Metrics::INDEX_SUM_VISIT_LENGTH => $row[Metrics::INDEX_SUM_VISIT_LENGTH],
                Metrics::INDEX_BOUNCE_COUNT => $row[Metrics::INDEX_BOUNCE_COUNT],
                Metrics::INDEX_NB_VISITS_CONVERTED => $row[Metrics::INDEX_NB_VISITS_CONVERTED],
            ];

            $metricsByLanguage->sumRowWithLabel($label, $columns);
        }

        return [
            Archiver::LANGUAGE_RECORD_NAME => $metricsByLanguage,
        ];
    }
}
