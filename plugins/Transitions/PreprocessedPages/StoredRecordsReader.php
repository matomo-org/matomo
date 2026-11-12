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
use Piwik\Period\Day;
use Piwik\Period\Range;
use Piwik\Plugins\SegmentEditor\UnprocessedSegmentException;
use Piwik\Segment;
use Piwik\Site;

/**
 * Reads the stored records of a pre-processed page from existing archives. It never launches archiving.
 *
 * The records of a period are used only if they cover every day of it that has visits: the page's `days` counter must
 * equal the number of days whose `nb_visits` is above 0, read from the day archives of the same segment. A day without
 * an archive counts as a day without visits, as archiving skips those, so the visits of the days must add up to the
 * period's `nb_visits`. Otherwise a day archive is missing or outdated, and the records are not used.
 */
final class StoredRecordsReader
{
    private const VISITS = 'nb_visits';

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
        foreach ($this->groupByLabel($periods) as $label => $periodsOfLabel) {
            $archive = $this->buildArchive($idSite, $periodsOfLabel, $segment);
            if ($archive === null) {
                return null;
            }

            $archives[$label] = $archive;
            $pageRows += $this->getNumericRows($archive, array_values($numericNames));
        }

        $coveredPeriods = [];
        foreach ($periods as $period) {
            if ($this->getValue($pageRows, $period, $numericNames[Records::DAYS]) > 0) {
                $coveredPeriods[$period->getRangeString()] = $period;
            }
        }

        if (empty($coveredPeriods) || !$this->isEveryDayWithVisitsCovered($idSite, $periods, $segment, $archives, $pageRows, $numericNames[Records::DAYS])) {
            return null;
        }

        $numerics = array_fill_keys(Records::NUMERICS, 0);
        $tsArchived = null;
        foreach ($coveredPeriods as $period) {
            foreach ($numericNames as $part => $name) {
                $numerics[$part] += (int) $this->getValue($pageRows, $period, $name);
            }

            $metadata = $pageRows[$period->getRangeString()][DataCollection::METADATA_CONTAINER_ROW_KEY] ?? [];
            $ts = is_array($metadata) ? ($metadata[DataTable::ARCHIVED_DATE_METADATA_NAME] ?? null) : null;
            if (is_string($ts) && ($tsArchived === null || strcmp($ts, $tsArchived) < 0)) {
                $tsArchived = $ts;
            }
        }

        if ($tsArchived === null) {
            return null;
        }

        $tables = [];
        foreach ($tableParts as $part) {
            $name = Records::getRecordName($idAction, $part);
            $tablesOfPeriods = [];
            foreach ($archives as $archive) {
                $result = $part === Records::REFERRERS ? $archive->getDataTableExpanded($name) : $archive->getDataTable($name);
                foreach ($this->getTablesByPeriod($result) as $rangeString => $table) {
                    if (isset($coveredPeriods[$rangeString])) {
                        $tablesOfPeriods[] = $table;
                    }
                }
            }

            $tables[$part] = $this->sumTables($tablesOfPeriods);
        }

        return new StoredRecords($numerics, $tables, $tsArchived);
    }

    /**
     * @param list<Period> $periods
     * @param array<string, Archive> $archives by period label
     * @param array<string, array<mixed>> $pageRows
     */
    private function isEveryDayWithVisitsCovered(int $idSite, array $periods, Segment $segment, array $archives, array $pageRows, string $daysName): bool
    {
        $periodVisits = [];
        foreach ($archives as $archive) {
            $periodVisits += $this->getNumericRows($archive, [self::VISITS]);
        }

        $days = [];
        foreach ($periods as $period) {
            foreach ($this->getDays($period) as $day) {
                $days[$day->getRangeString()] = $day;
            }
        }

        if (count($archives) === 1 && isset($archives['day'])) {
            $dayVisits = $periodVisits;
        } else {
            $dayArchive = $this->buildArchive($idSite, array_values($days), $segment);
            if ($dayArchive === null) {
                return false;
            }
            $dayVisits = $this->getNumericRows($dayArchive, [self::VISITS]);
        }

        $site = new Site($idSite);
        foreach ($periods as $period) {
            if (!isset($periodVisits[$period->getRangeString()][self::VISITS])) {
                if ($this->isOutsideOfSiteLifetime($site, $period)) {
                    // never archived, as there can't be visits
                    continue;
                }

                return false;
            }

            $visitDays = 0;
            $visitsOfDays = 0;
            foreach ($this->getDays($period) as $day) {
                $visits = (int) $this->getValue($dayVisits, $day, self::VISITS);
                if ($visits > 0) {
                    $visitDays++;
                    $visitsOfDays += $visits;
                }
            }

            // a day archive is missing, or was built after the period's archive
            if ($visitsOfDays !== (int) $this->getValue($periodVisits, $period, self::VISITS)) {
                return false;
            }

            if ((int) $this->getValue($pageRows, $period, $daysName) !== $visitDays) {
                return false;
            }
        }

        return true;
    }

    /**
     * Archive skips the same periods when it launches archiving.
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
     * @return array<string, DataTable> by period range string
     */
    private function getTablesByPeriod($result): array
    {
        $tables = [];
        $candidates = $result instanceof DataTable\Map ? $result->getDataTables() : [$result];
        foreach ($candidates as $table) {
            $period = $table instanceof DataTable ? $table->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX) : null;
            if ($table instanceof DataTable && $period instanceof Period) {
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

    /**
     * @return list<Period>
     */
    private function getDays(Period $period): array
    {
        if ($period->getLabel() === 'day') {
            return [$period];
        }

        $days = [];
        $end = $period->getDateEnd();
        for ($date = $period->getDateStart(); !$date->isLater($end); $date = $date->addDay(1)) {
            $days[] = new Day($date);
        }

        return $days;
    }
}
