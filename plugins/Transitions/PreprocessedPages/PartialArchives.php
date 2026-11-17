<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Db;
use Piwik\Period;
use Piwik\Period\Day;
use Piwik\Segment;

/**
 * The partial archives holding pre-processed Transitions records, `done<segment hash>.Transitions`: written by the
 * rebuild of an added page and by builds within report requests. Like other plugins' partial archives, they are not
 * invalidated by core; a newer archive of the period replaces them.
 */
final class PartialArchives
{
    public function __construct(
        private readonly Model $model
    ) {
    }

    /**
     * Deletes the page's older partial archives of the day and segment, which hold only the page's records, once a
     * newer one is written.
     */
    public function deleteOlderPartialArchivesOfPage(int $idSite, Period $day, Segment $segment, int $idPage, int $idArchive): void
    {
        $table = ArchiveTableCreator::getNumericTable($day->getDateStart(), false);
        if (empty($table)) {
            return;
        }

        $pageviews = Records::getRecordName($idPage, Records::PAGEVIEWS);
        $rows = Db::fetchAll(
            'SELECT p.idarchive FROM `' . $table . '` p WHERE p.idsite = ? AND p.period = ? AND p.date1 = ? AND p.date2 = ?'
            . ' AND p.name = ? AND p.value = ? AND p.idarchive < ?'
            . ' AND EXISTS (SELECT 1 FROM `' . $table . '` r WHERE r.idarchive = p.idarchive AND r.name = ?)'
            . ' AND NOT EXISTS (SELECT 1 FROM `' . $table . '` o WHERE o.idarchive = p.idarchive AND o.name LIKE ? AND o.name <> ?)',
            [
                $idSite,
                Day::PERIOD_ID,
                $day->getDateStart()->toString(),
                $day->getDateEnd()->toString(),
                Rules::getDoneFlagArchiveContainsOnePlugin($segment, 'Transitions'),
                ArchiveWriter::DONE_PARTIAL,
                $idArchive,
                $pageviews,
                'Transitions\_%\_' . Records::PAGEVIEWS,
                $pageviews,
            ]
        );

        $this->deleteArchives($day, array_map(fn(array $row): int => (int) $row['idarchive'], $rows));
    }

    /**
     * @param list<int> $idArchives
     */
    private function deleteArchives(Period $period, array $idArchives): void
    {
        $table = ArchiveTableCreator::getNumericTable($period->getDateStart(), false);
        if (empty($table) || empty($idArchives)) {
            return;
        }

        $this->model->deleteArchiveIds($table, ArchiveTableCreator::getBlobTable($period->getDateStart(), false) ?: null, $idArchives);
    }
}
