<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */

namespace Piwik\Plugins\Annotations;

use Piwik\Common;
use Piwik\Db;

class Model
{
    private static $rawPrefix = 'annotations';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    /**
     * @throws \Exception
     */
    public function createAnnotation(array $annotation): int
    {
        $db = $this->getDb();
        $db->insert($this->table, $annotation);
        return (int) $db->lastInsertId();
    }

    /**
     * @throws \Exception
     */
    public function getAnnotation(int $annotationId): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE id = ?";
        $bind = [$annotationId];
        $result = $db->fetchRow($query, $bind);
        return $result ? $result : [];
    }

    /**
     * @throws \Exception
     */
    public function getAllAnnotations(): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table";
        $result = $db->fetchAll($query);
        return $result ? $result : [];
    }

    /**
     * @throws \Exception
     */
    public function getAllAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE idsite = ? AND date >= ? AND date <= ?";
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        $result = $db->fetchAll($query, $bind);
        return $result ? $result : [];
    }

    /**
     * @throws \Exception
     */
    public function getCountAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate, bool $countStarred = false): int
    {
        $db = $this->getDb();
        $query = "SELECT count(id) as count FROM $this->table WHERE idsite = ? AND date >= ? AND date < ?";
        if ($countStarred) {
            $query .= " AND starred = 1";
        }
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        $result = $db->fetchRow($query, $bind);
        return $result['count'] ?? 0;
    }

    /**
     * Update existing annotation with provided data
     *
     * @param int $annotationId id of the annotation being updated
     * @param array $updatedColumns an associative array containing columns to update,
     *              only columns matching $this->getEditableColumns() are used.
     * @return array the updated annotation
     * @throws \Exception
     */
    public function updateAnnotation(int $annotationId, array $updatedColumns): array
    {
        $db = $this->getDb();
        $query = "UPDATE $this->table SET";
        $bind = [];
        foreach ($this->getEditableColumns() as $columnName) {
            if (isset($updatedColumns[$columnName])) {
                $query .= " $columnName = ?,";
                $bind[] = $updatedColumns[$columnName];
            }
        }
        $query = rtrim($query, ',');
        $query .= " WHERE id = ?";
        $bind[] = $annotationId;
        $db->query($query, $bind);
        return $this->getAnnotation($annotationId);
    }

    /**
     * @throws \Exception
     */
    public function deleteAnnotation(int $annotationId): void
    {
        $db = $this->getDb();
        $query = "DELETE FROM $this->table WHERE id = ?";
        $bind = [$annotationId];
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

    /**
     * @return array of columns which are permitted to be modified
     */
    private function getEditableColumns(): array
    {
        return ['note', 'date', 'starred'];
    }
}
