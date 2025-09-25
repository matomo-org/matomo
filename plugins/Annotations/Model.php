<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Annotations;

use Piwik\Common;
use Piwik\Db;

class Model
{
    private const TABLE_NAME = 'annotations';
    private const EDITABLE_COLS = ['note', 'date', 'starred'];
    /** @var string */
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::TABLE_NAME);
    }

    /**
     * @param array<string,mixed> $annotation
     * @throws \Exception
     */
    public function createAnnotation(array $annotation): int
    {
        $db = $this->getDb();
        $db->insert($this->table, $annotation);

        return (int) $db->lastInsertId();
    }

    /**
     * @return array<string,string|int|bool>
     * @throws \Exception
     */
    public function getAnnotation(int $annotationId, int $idSite): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE id = ? AND idsite = ?";
        $bind = [$annotationId, $idSite];

        return $db->fetchRow($query, $bind) ?: [];
    }

    /**
     * @return array<int,array<string,string|int|bool>>
     * @throws \Exception
     */
    public function getAllAnnotationsForSiteInRange(int $idSite, ?string $startDate = null, ?string $endDate = null, ?int $limit = null): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE idsite = ?";
        $bind = [
            $idSite,
        ];

        if (null !== $startDate) {
            $query .= " AND date >= ?";
            $bind[] = $startDate;
        }
        if (null !== $endDate) {
            $query .= " AND date <= ?";
            $bind[] = $endDate;
        }
        if (null !== $limit) {
            $query .= " LIMIT $limit";
        }

        return $db->fetchAll($query, $bind);
    }

    /**
     * @return array{int, int}
     * @throws \Exception
     */
    public function getCountAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate): array
    {
        $db = $this->getDb();
        $query = "SELECT
                    SUM(1) AS cnt_total,
                    SUM(CASE WHEN starred = 1 THEN 1 ELSE 0 END) AS cnt_starred
                FROM $this->table
                WHERE idsite = ? AND date >= ? AND date < ?";
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        $result = $db->fetchRow($query, $bind);

        return [intval($result['cnt_total'] ?? 0), intval($result['cnt_starred'] ?? 0)];
    }

    /**
     * Update existing annotation with provided data
     *
     * @param int $annotationId id of the annotation being updated
     * @param int $idSite the site of the annotation
     * @param array<string,string|int|bool> $updatedColumns an associative array containing columns to update,
     *              only columns matching $this->getEditableColumns() are used.
     * @return array<string,string|int|bool> the updated annotation
     * @throws \Exception
     */
    public function updateAnnotation(int $annotationId, int $idSite, array $updatedColumns): array
    {
        $db = $this->getDb();
        $query = "UPDATE $this->table SET";
        $bind = [];
        foreach (self::EDITABLE_COLS as $columnName) {
            if (isset($updatedColumns[$columnName])) {
                $query .= " $columnName = ?,";
                $bind[] = $updatedColumns[$columnName];
            }
        }
        $query = rtrim($query, ',');
        $query .= " WHERE id = ? AND idsite = ?";
        $bind[] = $annotationId;
        $bind[] = $idSite;
        $db->query($query, $bind);

        return $this->getAnnotation($annotationId, $idSite);
    }

    /**
     * @throws \Exception
     */
    public function deleteAnnotation(int $annotationId, int $idsite): void
    {
        $db = $this->getDb();
        $query = "DELETE FROM $this->table WHERE id = ? AND idsite = ?";
        $bind = [$annotationId, $idsite];
        $db->query($query, $bind);
    }

    /**
     * @throws \Exception
     */
    public function deleteAllAnnotationsForSite(int $idSite): void
    {
        $db = $this->getDb();
        $query = "DELETE FROM $this->table WHERE idsite = ?";
        $bind = [$idSite];
        $db->query($query, $bind);
    }

    /**
     * @return Db|Db\AdapterInterface|\Zend_Db_Adapter_Abstract
     */
    private function getDb()
    {
        return Db::get();
    }
}
