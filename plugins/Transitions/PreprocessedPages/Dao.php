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
use Piwik\Db;
use Piwik\Db\AdapterInterface;
use Piwik\Updater\Migration;

/**
 * Stores the page URLs of each website whose Transitions report is pre-processed.
 *
 * @phpstan-type PreprocessedPage array{idpage: int, idsite: int, url: string, url_hash: string, created_date: string, created_by: string|null}
 */
class Dao
{
    public const TABLE_NAME = 'transitions_preprocessed_page';

    /**
     * @return list<PreprocessedPage> newest first
     */
    public function getPagesForSite(int $idSite): array
    {
        $rows = Db::fetchAll(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by FROM ' . $this->getTable()
            . ' WHERE idsite = ? ORDER BY created_date DESC, idpage DESC',
            [$idSite]
        );

        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * @return PreprocessedPage|null
     */
    public function getPage(int $idSite, int $idPage): ?array
    {
        $row = Db::fetchRow(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND idpage = ?',
            [$idSite, $idPage]
        );

        return empty($row) ? null : $this->castRow($row);
    }

    /**
     * @return PreprocessedPage|null
     * @phpstan-impure
     */
    public function getPageByUrlHash(int $idSite, string $urlHash): ?array
    {
        $row = Db::fetchRow(
            'SELECT idpage, idsite, url, url_hash, created_date, created_by FROM ' . $this->getTable()
            . ' WHERE idsite = ? AND url_hash = ?',
            [$idSite, $urlHash]
        );

        return empty($row) ? null : $this->castRow($row);
    }

    public function getNumPagesForSite(int $idSite): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . $this->getTable() . ' WHERE idsite = ?', [$idSite]);
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

    public function removePage(int $idSite, int $idPage): void
    {
        Db::query('DELETE FROM ' . $this->getTable() . ' WHERE idsite = ? AND idpage = ?', [$idSite, $idPage]);
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
     * Catches up on users and websites deleted while the Transitions plugin was deactivated.
     */
    public function cleanUpDeletedUsersAndSites(): void
    {
        $this->writeIfTableExists(
            'UPDATE ' . $this->getTable() . ' SET created_by = NULL WHERE created_by IS NOT NULL'
            . ' AND created_by NOT IN (SELECT login FROM ' . Common::prefixTable('user') . ')',
            []
        );
        $this->writeIfTableExists(
            'DELETE FROM ' . $this->getTable() . ' WHERE idsite NOT IN (SELECT idsite FROM ' . Common::prefixTable('site') . ')',
            []
        );
    }

    /**
     * Cleanup runs from events, which can fire before the 6.1.0-b1 update has created the table.
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
        ];
    }

    private function getTable(): string
    {
        return Common::prefixTable(self::TABLE_NAME);
    }
}
