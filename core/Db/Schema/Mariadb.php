<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

/**
 * Mariadb schema
 */
class Mariadb extends Mysql
{
    /**
     * Adds a max_statement_time hint into a SELECT query if $limit is bigger than 0
     *
     * @param string $sql  query to add hint to
     * @param float $limit  time limit in seconds
     * @return string
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        if ($limit <= 0) {
            return $sql;
        }

        $sql = trim($sql);
        $pos = stripos($sql, 'SELECT');
        $isMaxExecutionTimeoutAlreadyPresent = (stripos($sql, 'max_statement_time=') !== false);
        if ($pos !== false && !$isMaxExecutionTimeoutAlreadyPresent) {
            $maxExecutionTimeHint = 'SET STATEMENT max_statement_time=' . ceil($limit) . ' FOR ';
            $sql = substr_replace($sql, $maxExecutionTimeHint . 'SELECT', $pos, strlen('SELECT'));
        }

        return $sql;
    }

    public function isOptimizeInnoDBSupported(): bool
    {
        $version = strtolower($this->getVersion());
        $semanticVersion = strstr($version, '-', $beforeNeedle = true);
        return version_compare($semanticVersion, '10.1.1', '>=');
    }
}
