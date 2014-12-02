<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Repository;

interface LogsRepository
{
    /**
     * @param string $from
     * @param string $to
     * @param array $locationFields
     * @return array
     */
    public function getVisitsWithDatesLimit($from, $to, $locationFields = array());

    /**
     * @param string $from
     * @param string $to
     * @return int
     */
    public function countVisitsWithDatesLimit($from, $to);

    /**
     * @param array $columnsToSet
     * @param array $bind
     */
    public function updateVisits(array $columnsToSet, array $bind);

    /**
     * @param array $columnsToSet
     * @param array $bind
     */
    public function updateConversions(array $columnsToSet, array $bind);
} 
