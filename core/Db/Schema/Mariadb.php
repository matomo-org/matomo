<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Piwik\Date;

/**
 * Mariadb schema
 */
class Mariadb extends Mysql
{
    public function getDatabaseType(): string
    {
        return 'MariaDB';
    }

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

    public function supportsRankingRollupWithoutExtraSorting(): bool
    {
        return false;
    }

    public function hasReachedEOL(): bool
    {
        $currentVersion = $this->getVersion();

        // End of security update for certain MariaDb versions as of https://mariadb.org/about/#maintenance-policy

        // Support for 10.6 LTS ends on 6th July 2026
        if (
            version_compare($currentVersion, '10.6', '>=') &&
            version_compare($currentVersion, '10.7', '<') &&
            Date::today()->isEarlier(Date::factory('2026-07-07'))
        ) {
            return false;
        }

        // Support for 10.11 LTS ends on 16th February 2028
        if (
            version_compare($currentVersion, '10.11', '>=') &&
            version_compare($currentVersion, '10.12', '<') &&
            Date::today()->isEarlier(Date::factory('2028-02-17'))
        ) {
            return false;
        }

        // Support for 11.4 LTS ends on 29th May 2029
        if (
            version_compare($currentVersion, '11.4', '>=') &&
            version_compare($currentVersion, '11.5', '<') &&
            Date::today()->isEarlier(Date::factory('2029-05-30'))
        ) {
            return false;
        }

        // Support for all versions prior to 11.8 (not covered by conditions above) already ended
        if (version_compare($currentVersion, '11.8', '<')) {
            return true;
        }

        return false;
    }
}
