<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Db;

class RawLogUpdater
{
    /**
     * @param array $values
     * @param string $idVisit
     */
    public function updateVisits(array $values, $idVisit)
    {
        $sql = array(
            "UPDATE " . Common::prefixTable('log_visit'),
            "SET " . $this->getColumnBinds(array_keys($values)),
            "WHERE idvisit = ?"
        );

        $this->executeQuery($sql, $values, $idVisit);
    }

    /**
     * @param array $values
     * @param string $idVisit
     */
    public function updateConversions(array $values, $idVisit)
    {
        $sql = array(
            "UPDATE " . Common::prefixTable('log_conversion'),
            "SET " . $this->getColumnBinds(array_keys($values)),
            "WHERE idvisit = ?"
        );

        $this->executeQuery($sql, $values, $idVisit);
    }

    /**
     * @param array $columnsToSet
     * @return string
     */
    protected function getColumnBinds(array $columnsToSet)
    {
        $columnsToSet = array_map(
            function ($column) {
                return sprintf('%s = ?', $column);
            },
            $columnsToSet
        );

        return implode(', ', $columnsToSet);
    }

    /**
     * @param array $values
     * @param $idVisit
     * @param $sql
     * @return \Zend_Db_Statement
     * @throws \Exception
     */
    protected function executeQuery($sql, array $values, $idVisit)
    {
        return Db::query(
            implode(' ', $sql),
            array_merge(
                array_values($values),
                array($idVisit)
            )
        );
    }
}