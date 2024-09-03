<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;

/**
 * Administration command that optimizes archive tables (even if they use InnoDB).
 */
class OptimizeArchiveTables extends ConsoleCommand
{
    public const ALL_TABLES_STRING = 'all';
    public const CURRENT_MONTH_STRING = 'now';

    protected function configure()
    {
        $this->setName('database:optimize-archive-tables');
        $this->setDescription("Runs an OPTIMIZE TABLE query on the specified archive tables.");
        $this->addRequiredArgument(
            "dates",
            "The months of the archive tables to optimize. Use '" . self::ALL_TABLES_STRING . "' for all dates or '" .
            self::CURRENT_MONTH_STRING . "' to optimize the current month only.",
            null,
            true
        );
        $this->addNoValueOption('dry-run', null, 'For testing purposes.');
        $this->setHelp("This command can be used to ease or automate maintenance. Instead of manually running "
            . "OPTIMIZE TABLE queries, the command can be used.\n\nYou should run the command if you find your "
            . "archive tables grow and do not shrink after purging. Optimizing them will reclaim some space.");
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $dryRun = $input->getOption('dry-run');

        $tableMonths = $this->getTableMonthsToOptimize();

        foreach ($tableMonths as $month) {
            $this->optimizeTable($dryRun, 'archive_numeric_' . $month);
            $this->optimizeTable($dryRun, 'archive_blob_' . $month);
        }

        return self::SUCCESS;
    }

    private function optimizeTable($dryRun, $table)
    {
        $output = $this->getOutput();

        $output->write("Optimizing table '$table'...");

        if ($dryRun) {
            $output->write("[dry-run, not optimising table]");
        } else {
            Db\Schema::getInstance()->optimizeTables([Common::prefixTable($table)], $force = true);
        }

        $output->writeln("Done.");
    }

    private function getTableMonthsToOptimize()
    {
        $dateSpecifiers = $this->getInput()->getArgument('dates');
        if (count($dateSpecifiers) === 1) {
            $dateSpecifier = reset($dateSpecifiers);

            if ($dateSpecifier == self::ALL_TABLES_STRING) {
                return $this->getAllArchiveTableMonths();
            } elseif ($dateSpecifier == self::CURRENT_MONTH_STRING) {
                $now = Date::factory('now');
                return array(ArchiveTableCreator::getTableMonthFromDate($now));
            } elseif (strpos($dateSpecifier, 'last') === 0) {
                $lastN = substr($dateSpecifier, 4);
                if (!ctype_digit($lastN)) {
                    throw new \Exception("Invalid lastN specifier '$lastN'. The end must be an integer, eg, last1 or last2.");
                }

                if ($lastN <= 0) {
                    throw new \Exception("Invalid lastN value '$lastN'.");
                }

                return $this->getLastNTableMonths((int)$lastN);
            }
        }

        $tableMonths = array();
        foreach ($dateSpecifiers as $date) {
            $date = Date::factory($date);
            $tableMonths[] = ArchiveTableCreator::getTableMonthFromDate($date);
        }
        return $tableMonths;
    }

    private function getAllArchiveTableMonths()
    {
        $tableMonths = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled() as $table) {
            $tableMonths[] = ArchiveTableCreator::getDateFromTableName($table);
        }
        return $tableMonths;
    }

    /**
     * @param int $lastN
     * @return string[]
     */
    private function getLastNTableMonths($lastN)
    {
        $now = Date::factory('now');

        $result = array();
        for ($i = 0; $i < $lastN; ++$i) {
            $date = $now->subMonth($i + 1);
            $result[] = ArchiveTableCreator::getTableMonthFromDate($date);
        }
        return $result;
    }
}
