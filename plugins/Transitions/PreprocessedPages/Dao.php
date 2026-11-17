<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Db\AdapterInterface;
use Piwik\Tracker\Action;
use Piwik\Updater\Migration;

/**
 * Stores the page URLs of each website whose Transitions report is pre-processed. A removed page keeps its row, with
 * `removed_date` set, so its stored records can still be found by URL, until the deletion of old reports has deleted
 * them, see {@link Service::deleteRemovedPagesOfDeletedReports()}. Rows are only added by admins, one per URL, and
 * the reads of a website's pages use the (idsite, removed_date) index, so removed rows don't slow them down.
 *
 * @phpstan-type PreprocessedPage array{idpage: int, idsite: int, url: string, url_hash: string, created_date: string, created_by: string|null, removed_date: string|null}
 */
class Dao
{
    public const TABLE_NAME = 'transitions_preprocessed_page';

    /**
     * Reads run in archiving and reports, which can happen before the 6.1.0-b3 update has created the table; they find
     * no pages then.
     *
     * @return list<PreprocessedPage> the pages not removed, newest first
     */
    public function getPagesForSite(int $idSite): array
    {
        $rows = $this->readIfTableExists(fn() => Db::fetchAll(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by, removed_date FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND removed_date IS NULL ORDER BY created_date DESC, idpage DESC',
            [$idSite]
        ), []);

        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * @param list<int> $idPages
     * @return list<PreprocessedPage> the website's pages among them, also removed ones, newest first
     */
    public function getPagesByIds(int $idSite, array $idPages): array
    {
        if (empty($idPages)) {
            return [];
        }

        $rows = $this->readIfTableExists(fn() => Db::fetchAll(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by, removed_date FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND idpage IN (' . implode(',', array_map('intval', $idPages)) . ') ORDER BY created_date DESC, idpage DESC',
            [$idSite]
        ), []);

        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * @return string|null the UTC time the website's oldest page that isn't removed was added
     */
    public function getOldestPageCreatedDate(int $idSite): ?string
    {
        $date = $this->readIfTableExists(
            fn() => Db::fetchOne('SELECT MIN(created_date) FROM ' . $this->getTable() . ' WHERE idsite = ? AND removed_date IS NULL', [$idSite]),
            null
        );

        return is_string($date) ? $date : null;
    }

    /**
     * @param list<int> $idSites
     * @return list<int> the websites among them with pages, also removed ones
     */
    public function getSitesWithAnyPages(array $idSites): array
    {
        $result = [];
        foreach (array_chunk(array_map('intval', $idSites), 1000) as $chunk) {
            $rows = $this->readIfTableExists(fn() => Db::fetchAll(
                'SELECT DISTINCT idsite FROM ' . $this->getTable() . ' WHERE idsite IN (' . implode(',', $chunk) . ')'
            ), []);
            foreach ($rows as $row) {
                $result[] = (int) $row['idsite'];
            }
        }

        return $result;
    }

    /**
     * @return PreprocessedPage|null null also for a removed page
     */
    public function getPage(int $idSite, int $idPage): ?array
    {
        $row = Db::fetchRow(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by, removed_date FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND idpage = ? AND removed_date IS NULL',
            [$idSite, $idPage]
        );

        return empty($row) ? null : $this->castRow($row);
    }

    /**
     * @return PreprocessedPage|null also a removed page
     * @phpstan-impure
     */
    public function getPageByUrlHash(int $idSite, string $urlHash): ?array
    {
        $row = $this->readIfTableExists(fn() => Db::fetchRow(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by, removed_date FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND url_hash = ?',
            [$idSite, $urlHash]
        ), false);

        return empty($row) ? null : $this->castRow($row);
    }

    public function getNumPagesForSite(int $idSite): int
    {
        return (int) $this->readIfTableExists(
            fn() => Db::fetchOne('SELECT COUNT(*) FROM ' . $this->getTable() . ' WHERE idsite = ? AND removed_date IS NULL', [$idSite]),
            0
        );
    }

    /**
     * Runs on every page load, also before the 6.1.0-b3 update has created the table.
     */
    public function hasPagesForSite(int $idSite): bool
    {
        return (bool) $this->readIfTableExists(
            fn() => Db::fetchOne('SELECT 1 FROM ' . $this->getTable() . ' WHERE idsite = ? AND removed_date IS NULL LIMIT 1', [$idSite]),
            false
        );
    }

    /**
     * Like hasPagesForSite(), but also true when all of the website's pages were removed, whose stored records are
     * still served.
     */
    public function hasAnyPagesForSite(int $idSite): bool
    {
        return (bool) $this->readIfTableExists(
            fn() => Db::fetchOne('SELECT 1 FROM ' . $this->getTable() . ' WHERE idsite = ? LIMIT 1', [$idSite]),
            false
        );
    }

    /**
     * @return list<int> the idpages of the website's pages removed at or after the given UTC time
     */
    public function getPageIdsRemovedSince(int $idSite, string $removedSince): array
    {
        $rows = $this->readIfTableExists(fn() => Db::fetchAll(
            'SELECT idpage FROM ' . $this->getTable() . ' WHERE idsite = ? AND removed_date >= ?',
            [$idSite, $removedSince]
        ), []);

        return array_map(fn(array $row): int => (int) $row['idpage'], $rows);
    }

    /**
     * Returns the `log_action` name of an action, for a page URL the URL without protocol.
     */
    public function getActionName(int $idAction): ?string
    {
        $name = Db::fetchOne('SELECT name FROM ' . Common::prefixTable('log_action') . ' WHERE idaction = ?', [$idAction]);

        return $name === false || $name === null ? null : (string) $name;
    }

    /**
     * @return bool false when the website already has a page with this hash
     */
    public function addPage(int $idSite, string $url, string $urlHash, string $createdDate, ?string $createdBy): bool
    {
        try {
            Db::query(
                'INSERT INTO ' . $this->getTable() . ' (idsite, url, url_hash, created_date, created_by) VALUES (?, ?, ?, ?, ?)',
                [$idSite, $url, $urlHash, $createdDate, $createdBy]
            );
        } catch (\Exception $e) {
            if ($this->isErrNo($e, Migration\Db::ERROR_CODE_DUPLICATE_ENTRY)) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Selects a removed page again, as added now; it keeps its idpage and so its stored records.
     *
     * @return bool false when the page isn't removed
     */
    public function restorePage(int $idPage, string $createdDate, ?string $createdBy): bool
    {
        $statement = Db::query(
            'UPDATE ' . $this->getTable() . ' SET removed_date = NULL, created_date = ?, created_by = ? WHERE idpage = ? AND removed_date IS NOT NULL',
            [$createdDate, $createdBy, $idPage]
        );

        return $statement->rowCount() > 0;
    }

    /**
     * Looks up page URL actions by their `log_action` name in one query.
     *
     * @param list<string> $names
     * @return array<string, int> the lowest idaction by name, for the names that exist
     */
    public function getPageUrlIdActions(array $names): array
    {
        $names = array_values(array_unique($names));
        if (empty($names)) {
            return [];
        }

        // MySQL computes the hash as the tracker stores it; PHP's crc32() is signed on 32-bit builds
        $rows = Db::fetchAll(
            'SELECT idaction, name FROM ' . Common::prefixTable('log_action') . ' WHERE type = ? AND hash IN ('
            . implode(', ', array_fill(0, count($names), 'CRC32(?)')) . ') ORDER BY idaction ASC',
            array_merge([Action::TYPE_PAGE_URL], $names)
        );

        $wanted = array_flip($names);
        $idActions = [];
        foreach ($rows as $row) {
            $name = (string) $row['name'];
            if (isset($wanted[$name]) && !isset($idActions[$name])) {
                $idActions[$name] = (int) $row['idaction'];
            }
        }

        return $idActions;
    }

    public function removePage(int $idSite, int $idPage, string $removedDate): void
    {
        Db::query(
            'UPDATE ' . $this->getTable() . ' SET removed_date = ? WHERE idsite = ? AND idpage = ? AND removed_date IS NULL',
            [$removedDate, $idSite, $idPage]
        );
    }

    /**
     * Reads the site table directly, bypassing the site cache.
     */
    public function siteExists(int $idSite): bool
    {
        return (bool) Db::fetchOne('SELECT 1 FROM ' . Common::prefixTable('site') . ' WHERE idsite = ?', [$idSite]);
    }

    /**
     * @return list<array{idpage: int, idsite: int, removed_date: string}> the pages removed before the time (UTC)
     */
    public function getPagesRemovedBefore(string $removedBefore): array
    {
        $rows = Db::fetchAll(
            'SELECT idpage, idsite, removed_date FROM ' . $this->getTable() . ' WHERE removed_date IS NOT NULL AND removed_date < ?',
            [$removedBefore]
        );

        return array_map(fn(array $row): array => [
            'idpage' => (int) $row['idpage'],
            'idsite' => (int) $row['idsite'],
            'removed_date' => (string) $row['removed_date'],
        ], $rows);
    }

    /**
     * Tells whether the numeric archive tables of the months hold any of the page's records.
     *
     * @param list<Date> $months
     */
    public function hasStoredRecords(int $idSite, int $idPage, array $months): bool
    {
        foreach ($months as $month) {
            $table = ArchiveTableCreator::getNumericTable($month, false);
            if (
                !empty($table) && Db::fetchOne(
                    'SELECT 1 FROM `' . $table . '` WHERE idsite = ? AND name = ? LIMIT 1',
                    [$idSite, Records::getRecordName($idPage, Records::PAGEVIEWS)]
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a page removed before the time (UTC); a selected page, or one removed again since, is kept.
     */
    public function deleteRemovedPage(int $idPage, string $removedBefore): bool
    {
        return Db::query(
            'DELETE FROM ' . $this->getTable() . ' WHERE idpage = ? AND removed_date IS NOT NULL AND removed_date < ?',
            [$idPage, $removedBefore]
        )->rowCount() > 0;
    }

    public function deletePagesForSite(int $idSite): void
    {
        $this->writeIfTableExists('DELETE FROM ' . $this->getTable() . ' WHERE idsite = ?', [$idSite]);
    }

    public function anonymizeCreatedBy(string $login): void
    {
        $this->writeIfTableExists('UPDATE ' . $this->getTable() . ' SET created_by = NULL WHERE created_by = ?', [$login]);
    }

    /**
     * Clears the login that added the page if the user was deleted meanwhile, whose deletion may have cleaned up before
     * the page was written.
     *
     * @return bool whether it was cleared
     */
    public function clearCreatedByOfDeletedUser(int $idPage): bool
    {
        $createdBy = Db::fetchOne('SELECT created_by FROM ' . $this->getTable() . ' WHERE idpage = ?', [$idPage]);
        if (!is_string($createdBy) || $this->getRegistrationDates([$createdBy]) !== []) {
            return false;
        }

        $statement = Db::query(
            'UPDATE ' . $this->getTable() . ' SET created_by = NULL WHERE idpage = ? AND created_by = ?',
            [$idPage, $createdBy]
        );

        return $statement->rowCount() > 0;
    }

    /**
     * Catches up on users and websites deleted while the Transitions plugin was deactivated.
     */
    public function cleanUpDeletedUsersAndSites(): void
    {
        try {
            $logins = array_map('strval', array_column(
                Db::fetchAll('SELECT DISTINCT created_by FROM ' . $this->getTable() . ' WHERE created_by IS NOT NULL'),
                'created_by'
            ));
        } catch (\Exception $e) {
            if (!$this->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }
            $logins = [];
        }

        $registrationDates = $this->getRegistrationDates($logins);
        foreach ($logins as $login) {
            if (!array_key_exists($login, $registrationDates)) {
                $this->writeIfTableExists('UPDATE ' . $this->getTable() . ' SET created_by = NULL WHERE created_by = ?', [$login]);
            } elseif ($registrationDates[$login] !== null) {
                // a login created again after the page was added belongs to someone else; both dates are UTC strings
                $this->writeIfTableExists(
                    'UPDATE ' . $this->getTable() . ' SET created_by = NULL WHERE created_by = ? AND created_date < ?',
                    [$login, $registrationDates[$login]]
                );
            }
        }

        $this->writeIfTableExists(
            'DELETE FROM ' . $this->getTable() . ' WHERE idsite NOT IN (SELECT idsite FROM ' . Common::prefixTable('site') . ')',
            []
        );
    }

    /**
     * Looks the logins up in the user table alone, so no comparison depends on the collations of both tables, which
     * may differ on upgraded installs. Logins match as the user table matches them.
     *
     * @param list<string> $logins
     * @return array<string, string|null> by given login of an existing user, its registration date (UTC)
     */
    private function getRegistrationDates(array $logins): array
    {
        $registrationDates = [];
        foreach (array_unique($logins) as $login) {
            $row = Db::fetchRow('SELECT date_registered FROM ' . Common::prefixTable('user') . ' WHERE login = ?', [$login]);
            if (!empty($row)) {
                $registrationDates[$login] = isset($row['date_registered']) ? (string) $row['date_registered'] : null;
            }
        }

        return $registrationDates;
    }

    /**
     * Cleanup runs from events, which can fire before the 6.1.0-b3 update has created the table.
     *
     * @param list<int|string> $bind
     */
    private function writeIfTableExists(string $sql, array $bind): void
    {
        try {
            Db::query($sql, $bind);
        } catch (\Exception $e) {
            if (!$this->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }
        }
    }

    /**
     * @template T
     * @param callable(): T $read
     * @param T $default
     * @return T
     */
    private function readIfTableExists(callable $read, $default)
    {
        try {
            return $read();
        } catch (\Exception $e) {
            if ($this->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                return $default;
            }

            throw $e;
        }
    }

    private function isErrNo(\Exception $e, int $errno): bool
    {
        $db = Db::get();

        return $db instanceof AdapterInterface && $db->isErrNo($e, $errno);
    }

    /**
     * @param array<array-key, string|int|float|null> $row
     * @return PreprocessedPage
     */
    private function castRow(array $row): array
    {
        return [
            'idpage' => (int) $row['idpage'],
            'idsite' => (int) $row['idsite'],
            'url' => (string) $row['url'],
            'url_hash' => (string) $row['url_hash'],
            'created_date' => (string) $row['created_date'],
            'created_by' => isset($row['created_by']) ? (string) $row['created_by'] : null,
            'removed_date' => isset($row['removed_date']) ? (string) $row['removed_date'] : null,
        ];
    }

    private function getTable(): string
    {
        return Common::prefixTable(self::TABLE_NAME);
    }
}
