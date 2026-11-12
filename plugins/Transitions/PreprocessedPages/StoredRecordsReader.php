<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Archive;
use Piwik\Archive\DataCollection;
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\UnprocessedSegmentException;
use Piwik\Segment;
use Piwik\Site;

/**
 * Reads the stored records of a pre-processed page from existing archives. It never launches archiving.
 *
 * A day is covered when the page's records exist. A longer period is covered when, in its own archive, the page's
 * `days` counter equals {@link Records::DAYS_WITH_VISITS}, the days of the period that had visits, both recorded when
 * the period was archived. A range is covered when each of its subperiods is.
 */
final class StoredRecordsReader
{
    /**
     * Reads the page's records for the period. A range is summed from its subperiods.
     *
     * @param list<string> $tableParts the lists and the referrers to read, see {@link Records}
     * @return StoredRecords|null null unless the records cover the whole period
     */
    public function read(int $idSite, Period $period, Segment $segment, int $idAction, array $tableParts): ?StoredRecords
    {
        $periods = $period instanceof Range ? array_values($period->getSubperiods()) : [$period];

        return $this->readSum($idSite, $periods, $segment, $idAction, $tableParts);
    }

    /**
     * Sums the page's records of several periods in memory, for example the subperiods of a range or the days of a
     * longer period. The sum is not stored.
     *
     * @param list<Period> $periods periods other than ranges
     * @param list<string> $tableParts the lists and the referrers to read, see {@link Records}
     * @return StoredRecords|null null unless the records cover every period
     */
    public function readSum(int $idSite, array $periods, Segment $segment, int $idAction, array $tableParts): ?StoredRecords
    {
        try {
            return $this->readSumOfPeriods($idSite, $periods, $segment, $idAction, $tableParts);
        } catch (UnprocessedSegmentException $e) {
            // posted for an HTTP API request of a segment to pre-process that has no archives yet
            return null;
        }
    }

    /**
     * @param list<Period> $periods
     * @param list<string> $tableParts
     */
    private function readSumOfPeriods(int $idSite, array $periods, Segment $segment, int $idAction, array $tableParts): ?StoredRecords
    {
        $numericNames = [];
        foreach (Records::NUMERICS as $part) {
            $numericNames[$part] = Records::getRecordName($idAction, $part);
        }

        $archives = [];
        $pageRows = [];
        $siteRows = [];
        foreach ($this->groupByLabel($periods) as $label => $periodsOfLabel) {
            $archive = $this->buildArchive($idSite, $periodsOfLabel, $segment);
            if ($archive === null) {
                return null;
            }

            $archives[$label] = $archive;
            $pageRows += $this->getNumericRows($archive, array_values($numericNames));
            if ($label !== 'day') {
                $siteRows += $this->getNumericRows($archive, [Records::DAYS_WITH_VISITS]);
            }
        }

        $site = new Site($idSite);
        $coveredPeriods = [];
        $tsArchived = null;
        foreach ($periods as $period) {
            $rangeString = $period->getRangeString();
            if (!isset($pageRows[$rangeString][$numericNames[Records::PAGEVIEWS]])) {
                if ($this->isOutsideOfSiteLifetime($site, $period)) {
                    // never archived, as there can't be visits
                    continue;
                }

                return null;
            }

            if ($period->getLabel() !== 'day') {
                if (
                    !isset($siteRows[$rangeString][Records::DAYS_WITH_VISITS])
                    || $this->getValue($siteRows, $period, Records::DAYS_WITH_VISITS) !== $this->getValue($pageRows, $period, $numericNames[Records::DAYS])
                ) {
                    return null;
                }

                $tsArchived = $this->getOlderTsArchived($siteRows[$rangeString], $tsArchived);
            }

            $tsArchived = $this->getOlderTsArchived($pageRows[$rangeString], $tsArchived);
            $coveredPeriods[$rangeString] = $period;
        }

        if (empty($coveredPeriods) || $tsArchived === null) {
            return null;
        }

        $numerics = array_fill_keys(Records::NUMERICS, 0);
        foreach ($coveredPeriods as $period) {
            foreach ($numericNames as $part => $name) {
                $numerics[$part] += (int) $this->getValue($pageRows, $period, $name);
            }
        }

        $tables = [];
        foreach ($tableParts as $part) {
            $name = Records::getRecordName($idAction, $part);
            $tablesOfPeriods = [];
            foreach ($archives as $archive) {
                $result = $part === Records::REFERRERS ? $archive->getDataTableExpanded($name) : $archive->getDataTable($name);
                foreach ($this->getTablesByPeriod($result) as $rangeString => $table) {
                    if (isset($coveredPeriods[$rangeString])) {
                        $tablesOfPeriods[$rangeString] = $table;
                    }
                }
            }

            // reports purged from old archives can leave the numeric records behind
            if (count($tablesOfPeriods) !== count($coveredPeriods)) {
                return null;
            }

            $tables[$part] = $this->sumTables(array_values($tablesOfPeriods));
        }

        return new StoredRecords($numerics, $tables, $tsArchived);
    }

    /**
     * @param array<mixed> $row a numeric row of {@link getNumericRows()}
     */
    private function getOlderTsArchived(array $row, ?string $tsArchived): ?string
    {
        $metadata = $row[DataCollection::METADATA_CONTAINER_ROW_KEY] ?? [];
        $ts = is_array($metadata) ? ($metadata[DataTable::ARCHIVED_DATE_METADATA_NAME] ?? null) : null;

        return is_string($ts) && ($tsArchived === null || strcmp($ts, $tsArchived) < 0) ? $ts : $tsArchived;
    }

    /**
     * The periods `Archive` skips when it launches archiving (core/Archive.php, cacheArchiveIdsAfterLaunching()).
     */
    private function isOutsideOfSiteLifetime(Site $site, Period $period): bool
    {
        return $period->getDateEnd()->addDay(2)->isEarlier($site->getCreationDate())
            || $period->getDateStart()->isLater(Date::factory('now', $site->getTimezone()));
    }

    /**
     * @param list<Period> $periods
     */
    private function buildArchive(int $idSite, array $periods, Segment $segment): ?Archive
    {
        $archive = Archive::factory($segment, $periods, [$idSite], false, true);
        if (!$archive instanceof Archive) {
            // reading without launching archiving can't be guaranteed
            return null;
        }

        $archive->forceFetchingWithoutLaunchingArchiving();

        return $archive;
    }

    /**
     * @param list<string> $names
     * @return array<string, array<mixed>> the values and metadata by period range string; empty without an archive
     */
    private function getNumericRows(Archive $archive, array $names): array
    {
        $result = $archive->getNumeric($names);
        if (!is_array($result)) {
            return [];
        }

        $rows = [];
        foreach ($result as $rangeString => $row) {
            if (is_array($row)) {
                $rows[(string) $rangeString] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param array<string, array<mixed>> $rows
     */
    private function getValue(array $rows, Period $period, string $name): float
    {
        $value = $rows[$period->getRangeString()][$name] ?? 0;

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @param DataTable|DataTable\Map $result
     * @return array<string, DataTable> the tables found in an archive, by period range string
     */
    private function getTablesByPeriod($result): array
    {
        $tables = [];
        $candidates = $result instanceof DataTable\Map ? $result->getDataTables() : [$result];
        foreach ($candidates as $table) {
            $period = $table instanceof DataTable ? $table->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX) : null;
            // only a table read from an archive has the time it was archived
            if ($table instanceof DataTable && $period instanceof Period && $table->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME)) {
                $tables[$period->getRangeString()] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param list<DataTable> $tables
     */
    private function sumTables(array $tables): DataTable
    {
        if (count($tables) === 1) {
            return $tables[0];
        }

        $sum = new DataTable();
        foreach ($tables as $table) {
            $sum->addDataTable($table);
        }

        return $sum;
    }

    /**
     * @param list<Period> $periods
     * @return array<string, list<Period>>
     */
    private function groupByLabel(array $periods): array
    {
        $groups = [];
        foreach ($periods as $period) {
            $groups[$period->getLabel()][] = $period;
        }

        return $groups;
    }
}
