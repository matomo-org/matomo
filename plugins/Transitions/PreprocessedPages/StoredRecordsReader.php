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
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Plugins\SegmentEditor\UnprocessedSegmentException;
use Piwik\Segment;
use Piwik\Site;

/**
 * Reads the stored records of a pre-processed page from existing archives. It never launches archiving.
 *
 * A day is covered when the page's records exist. A longer period is covered when, in its own archive, the page's
 * `days` counter equals {@link Records::DAYS_WITH_VISITS}, the days of the period that had visits, both recorded when
 * the period was archived. Several periods, like the subperiods of a range, are covered when each of them is.
 */
final class StoredRecordsReader
{
    private const PERIODS_PER_READ = 31;

    /**
     * Sums the page's records of several periods in memory, for example the subperiods of a range or the days of a
     * longer period. The sum is not stored.
     *
     * @param list<Period> $periods periods other than ranges
     * @param list<string> $tableParts the lists and the referrers to read, see {@link Records}
     * @return StoredRecords|null null unless the records cover every period
     */
    public function readSum(int $idSite, array $periods, Segment $segment, int $idPage, array $tableParts): ?StoredRecords
    {
        try {
            return $this->readSumOfPeriods($idSite, $periods, $segment, $idPage, $tableParts);
        } catch (UnprocessedSegmentException $e) {
            // posted for an HTTP API request of a segment to pre-process that has no archives yet
            return null;
        }
    }

    /**
     * Tells which of the periods the page's records cover, without reading their lists.
     *
     * @param list<Period> $periods periods other than ranges
     * @return array<string, string|null> by period range string, for the covered periods: the UTC time the oldest of
     *                                    their archives was built, or null for a period without visits as it lies
     *                                    outside the website's lifetime
     */
    public function getCoverage(int $idSite, array $periods, Segment $segment, int $idPage): array
    {
        try {
            return $this->readNumerics($idSite, $periods, $segment, $idPage)['coverage'] ?? [];
        } catch (UnprocessedSegmentException $e) {
            return [];
        }
    }

    /**
     * Returns the visits of the days from their archives, without launching archiving.
     *
     * @param list<Period> $days
     * @return array<string, int|null> by period range string; null for a day without an archive
     */
    public function getVisits(int $idSite, array $days, Segment $segment): array
    {
        try {
            $archive = $this->buildArchive($idSite, $days, $segment);
            $rows = $archive === null ? [] : $this->getNumericRows($archive, ['nb_visits']);
        } catch (UnprocessedSegmentException $e) {
            $rows = [];
        }

        $visits = [];
        foreach ($days as $day) {
            $value = $rows[$day->getRangeString()]['nb_visits'] ?? null;
            $visits[$day->getRangeString()] = is_numeric($value) ? (int) $value : null;
        }

        return $visits;
    }

    /**
     * @param list<Period> $periods
     * @param list<string> $tableParts
     */
    private function readSumOfPeriods(int $idSite, array $periods, Segment $segment, int $idPage, array $tableParts): ?StoredRecords
    {
        $numerics = $this->readNumerics($idSite, $periods, $segment, $idPage);
        if ($numerics === null || count($numerics['coverage']) !== count($periods)) {
            return null;
        }

        $tsArchivedByPeriod = array_filter($numerics['coverage'], 'is_string');
        if (empty($tsArchivedByPeriod)) {
            return null;
        }

        $sums = array_fill_keys(Records::NUMERICS, 0);
        foreach (array_keys($tsArchivedByPeriod) as $rangeString) {
            foreach (Records::NUMERICS as $part) {
                $value = $numerics['pageRows'][$rangeString][Records::getRecordName($idPage, $part)] ?? 0;
                $sums[$part] += is_numeric($value) ? (int) $value : 0;
            }
        }

        $coveredPeriods = [];
        foreach ($periods as $period) {
            if (isset($tsArchivedByPeriod[$period->getRangeString()])) {
                $coveredPeriods[] = $period;
            }
        }

        $tables = [];
        foreach ($tableParts as $part) {
            $table = $this->readTableSum($idSite, $coveredPeriods, $segment, Records::getRecordName($idPage, $part), $part === Records::REFERRERS, $numerics['archives']);
            // reports purged from old archives can leave the numeric records behind
            if ($table === null) {
                return null;
            }

            $tables[$part] = $table;
        }

        return new StoredRecords($sums, $tables, $tsArchivedByPeriod);
    }

    /**
     * @param list<Period> $periods
     * @return array{archives: array<string, Archive>, pageRows: array<string, array<mixed>>, coverage: array<string, string|null>}|null
     */
    private function readNumerics(int $idSite, array $periods, Segment $segment, int $idPage): ?array
    {
        $numericNames = [];
        foreach (Records::NUMERICS as $part) {
            $numericNames[$part] = Records::getRecordName($idPage, $part);
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
        $coverage = [];
        foreach ($periods as $period) {
            $rangeString = $period->getRangeString();
            if (!isset($pageRows[$rangeString][$numericNames[Records::PAGEVIEWS]])) {
                if ($this->isOutsideOfSiteLifetime($site, $period)) {
                    // never archived, as there can't be visits
                    $coverage[$rangeString] = null;
                }
                continue;
            }

            $tsArchived = $this->getOlderTsArchived($pageRows[$rangeString], null);
            if ($period->getLabel() !== 'day') {
                if (
                    !isset($siteRows[$rangeString][Records::DAYS_WITH_VISITS])
                    || $this->getValue($siteRows, $period, Records::DAYS_WITH_VISITS) !== $this->getValue($pageRows, $period, $numericNames[Records::DAYS])
                ) {
                    continue;
                }

                $tsArchived = $this->getOlderTsArchived($siteRows[$rangeString], $tsArchived);
            }

            if ($tsArchived !== null) {
                $coverage[$rangeString] = $tsArchived;
            }
        }

        return ['archives' => $archives, 'pageRows' => $pageRows, 'coverage' => $coverage];
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
     * Sums a list of the periods. Many periods are read a few at a time, so their tables don't all stay in memory.
     *
     * @param list<Period> $periods
     * @param array<string, Archive> $archives the archives of all the periods by label, used when few enough
     * @return DataTable|null null unless every period has the list
     */
    private function readTableSum(int $idSite, array $periods, Segment $segment, string $name, bool $isExpanded, array $archives): ?DataTable
    {
        $sum = null;
        $found = 0;
        foreach ($this->groupByLabel($periods) as $label => $periodsOfLabel) {
            foreach (array_chunk($periodsOfLabel, self::PERIODS_PER_READ) as $chunk) {
                $archive = count($periodsOfLabel) <= self::PERIODS_PER_READ && isset($archives[$label])
                    ? $archives[$label]
                    : $this->buildArchive($idSite, $chunk, $segment);
                if ($archive === null) {
                    return null;
                }

                $result = $isExpanded ? $archive->getDataTableExpanded($name) : $archive->getDataTable($name);
                $rangeStrings = array_flip(array_map(fn(Period $period) => $period->getRangeString(), $chunk));
                $tables = array_intersect_key($this->getTablesByPeriod($result), $rangeStrings);
                $found += count($tables);

                if (count($periods) === 1) {
                    return array_values($tables)[0] ?? null;
                }

                $sum = $sum ?? new DataTable();
                foreach ($tables as $table) {
                    $sum->addDataTable($table);
                }

                // as archiving truncates the lists it sums, so many periods don't make the sum grow without a bound
                $column = $isExpanded ? Metrics::INDEX_NB_VISITS : Metrics::INDEX_NB_ACTIONS;
                Records::truncate($sum, Records::getMaxRows($idSite), $column);

                foreach ($result instanceof DataTable\Map ? $result->getDataTables() : [$result] as $table) {
                    Common::destroy($table);
                }
            }
        }

        return $found === count($periods) ? $sum : null;
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
