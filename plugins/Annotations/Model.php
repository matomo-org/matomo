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

    public function createAnnotation(array $annotation): int
    {
        /** @var \Zend_Db_Adapter_Abstract $db */
        $db = $this->getDb();
        $db->insert($this->table, $annotation);
        return $db->lastInsertId();
    }

    public function getAnnotation(int $annotationId): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE id = ?";
        $bind = [$annotationId];
        return $db->fetchRow($query, $bind);
    }

    public function getAllAnnotations(): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table";
        return $db->fetchAll($query);
    }

    public function getAllAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate): array
    {
        $db = $this->getDb();
        $query = "SELECT * FROM $this->table WHERE idsite = ? AND date >= ? AND date <= ?";
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        return $db->fetchAll($query, $bind);
    }

    public function getCountAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate): int
    {
        $db = $this->getDb();
        $query = "SELECT count(id) FROM $this->table WHERE idsite = ? AND date >= ? AND date <= ?";
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        return $db->fetchRow($query, $bind)[0];
    }
    
    public function getCountStarredAnnotationsForSiteInRange(int $idSite, string $startDate, string $endDate): int
    {
        $db = $this->getDb();
        $query = "SELECT count(id) FROM $this->table WHERE idsite = ? AND starred = 1 AND date >= ? AND date <= ?";
        $bind = [
            $idSite,
            $startDate,
            $endDate,
        ];
        return $db->fetchRow($query, $bind)[0];
    }

    public function updateAnnotation(int $annotationId, array $updatedColumns): array
    {
        $db = $this->getDb();
        $query = "UPDATE $this->table SET ";
        $bind = [];
        foreach ($this->getEditableColumns() as $columnName) {
            if (isset($updatedColumns[$columnName])) {
                $query .= "$columnName as ? ";
                $bind[] = $updatedColumns[$columnName];
            }
        }
        $query .= "WHERE id = ?";
        $bind[] = $annotationId;
        $updatedAnnotation = $db->query($query, $bind)->fetch();
        return $updatedAnnotation;
    }

    public function deleteAnnotation(int $annotationId): void
    {
        $db = $this->getDb();
        $query = "DELETE FROM $this->table WHERE id = ?";
        $bind = [$annotationId];
        $db->query($query, $bind);
    }

    public function deleteAllAnnotationsForSite(int $idSite): void
    {
        $db = $this->getDb();
        $query = "DELETE FROM $this->table WHERE idsite = ?";
        $bind = [$idSite];
        $db->query($query, $bind);
    }

    private function getDb()
    {
        return Db::get();
    }

    private function getEditableColumns(): array
    {
        return ['note', 'date', 'starred'];
    }
}
