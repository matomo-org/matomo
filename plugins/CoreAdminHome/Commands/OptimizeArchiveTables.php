<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Administration command that optimizes archive tables (even if they use InnoDB).
 */
class OptimizeArchiveTables extends ConsoleCommand
{
    const ALL_TABLES_STRING = 'all';
    const CURRENT_MONTH_STRING = 'now';

    protected function configure()
    {
        $this->setName('database:optimize-archive-tables');
        $this->setDescription("Runs an OPTIMIZE TABLE query on the specified archive tables.");
        $this->addArgument("dates", InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            "The months of the archive tables to optimize. Use '" . self::ALL_TABLES_STRING. "' for all dates or '" .
            self::CURRENT_MONTH_STRING . "' to optimize the current month only.");
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'For testing purposes.');
        $this->setHelp("This command can be used to ease or automate maintenance. Instead of manually running "
            . "OPTIMIZE TABLE queries, the command can be used.\n\nYou should run the command if you find your "
            . "archive tables grow and do not shrink after purging. Optimizing them will reclaim some space.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');

        $tableMonths = $this->getTableMonthsToOptimize($input);

        foreach ($tableMonths as $month) {
            $this->optimizeTable($output, $dryRun, 'archive_numeric_' . $month);
            $this->optimizeTable($output, $dryRun, 'archive_blob_' . $month);
        }
    }

    private function optimizeTable(OutputInterface $output, $dryRun, $table)
    {
        $output->write("Optimizing table '$table'...");

        if ($dryRun) {
            $output->write("[dry-run, not optimising table]");
        } else {
            Db::optimizeTables(Common::prefixTable($table), $force = true);
        }

        $output->writeln("Done.");
    }

    private function getTableMonthsToOptimize(InputInterface $input)
    {
        $dateSpecifiers = $input->getArgument('dates');
        if (count($dateSpecifiers) === 1) {
            $dateSpecifier = reset($dateSpecifiers);

            if ($dateSpecifier == self::ALL_TABLES_STRING) {
                return $this->getAllArchiveTableMonths();
            } else if ($dateSpecifier == self::CURRENT_MONTH_STRING) {
                $now = Date::factory('now');
                return array(ArchiveTableCreator::getTableMonthFromDate($now));
            } else if (strpos($dateSpecifier, 'last') === 0) {
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
