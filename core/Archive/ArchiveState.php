<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Archive;

use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Site;

class ArchiveState
{
    public const COMPLETE = 'complete';
    public const INCOMPLETE = 'incomplete';
    public const INVALIDATED = 'invalidated';

    /**
     * @param array{date1: string, date2: string, idsite: string, ts_archived: string} $archiveData
     * @param array<string, array<int>> $archiveIds archives ids indexed by period
     * @param array<int, array<string, array<int, int>>> $archiveStates archive states indexed by site and period
     */
    public function addMetadataToResultCollection(
        DataCollection $collection,
        array $archiveData,
        array $archiveIds,
        array $archiveStates
    ): void {
        $periodsEndDays = [];
        $periodsTsArchived = [];
        $archiveIdsFlipped = [];

        foreach ($archiveData as $archive) {
            $idSite = (int) $archive['idsite'];
            $period = $archive['date1'] . ',' . $archive['date2'];

            $periodsEndDays[$idSite][$period] = $archive['date2'];
            $periodsTsArchived[$idSite][$period] = $archive['ts_archived'];
        }

        foreach ($archiveIds as $period => $periodArchiveIds) {
            $archiveIdsFlipped[$period] = array_flip($periodArchiveIds);
        }

        foreach ($periodsTsArchived as $idSite => $periods) {
            $siteTimezone = Site::getTimezoneFor($idSite);

            foreach ($periods as $period => $tsArchived) {
                $periodEndDay = $periodsEndDays[$idSite][$period];

                $state = $this->checkArchiveStates($idSite, $period, $archiveIdsFlipped, $archiveStates);
                $state = $this->checkTsArchived($state, $siteTimezone, $periodEndDay, $tsArchived);

                if (null === $state) {
                    // do not set metadata, if no state was determined,
                    // to avoid generating unexpected default rows
                    continue;
                }

                $collection->addMetadata(
                    $idSite,
                    $period,
                    DataTable::ARCHIVE_STATE_METADATA_NAME,
                    $state
                );
            }
        }
    }

    /**
     * @param array<string, array<int, bool>> $archiveIdsFlipped
     * @param array<int, array<string, array<int, int>>> $archiveStates
     */
    private function checkArchiveStates(
        int $idSite,
        string $period,
        array $archiveIdsFlipped,
        array $archiveStates
    ): ?string {
        if (!isset($archiveStates[$idSite][$period]) || !isset($archiveIdsFlipped[$period])) {
            // do not determine state if no archives were used
            return null;
        }

        $availableStates = array_intersect_key(
            $archiveStates[$idSite][$period],
            $archiveIdsFlipped[$period]
        );

        if ([] === $availableStates) {
            // do not determine state if no archives were used
            return null;
        }

        if (in_array(ArchiveWriter::DONE_INVALIDATED, $availableStates)) {
            // archive has been invalidated
            return self::INVALIDATED;
        }

        // all archives not invalidated should be complete
        // includes DONE_OK, DONE_OK_TEMPORARY and DONE_PARTIAL
        return self::COMPLETE;
    }

    private function checkTsArchived(
        ?string $state,
        string $siteTimezone,
        string $periodEndDay,
        string $tsArchived
    ): ?string {
        if (self::COMPLETE !== $state) {
            // only archives detected as complete can be archived before range end
            return $state;
        }

        $datePeriodEnd = Date::factory($periodEndDay . ' 23:59:59')->setTimezone($siteTimezone);
        $dateArchived = Date::factory($tsArchived);

        if (!$datePeriodEnd->isEarlier($dateArchived)) {
            return self::INCOMPLETE;
        }

        return $state;
    }
}
