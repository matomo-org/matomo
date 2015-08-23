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
        $this->setName('core:optimize-archive-tables');
        $this->setDescription("Runs an OPTIMIZE TABLE query on the specified archive tables.");
        $this->addArgument("dates", InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            "The months of the archive tables to optimize. Use '" . self::ALL_TABLES_STRING. "' for all dates or '" .
            self::CURRENT_MONTH_STRING . "' to optimize the current month only.");
        $this->setHelp("This command can be used to ease or automate maintenance. Instead of manually running "
            . "OPTIMIZE TABLE queries, the command can be used.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tableMonths = $this->getTableMonthsToOptimize($input);

        foreach ($tableMonths as $month) {
            $this->optimizeTable($output, 'archive_numeric_' . $month);
            $this->optimizeTable($output, 'archive_blob_' . $month);
        }
    }

    private function optimizeTable(OutputInterface $output, $table)
    {
        $output->write("Optimizing table '$table'...");

        $table = Common::prefixTable($table);
        Db::optimizeTables($table, $force = true);

        $output->writeln("Done.");
    }

    private function getTableMonthsToOptimize(InputInterface $input)
    {
        $dateSpecifier = $input->getArgument('dates');
        if (count($dateSpecifier) === 1) {
            $dateSpecifier = reset($dateSpecifier);

            if ($dateSpecifier == self::ALL_TABLES_STRING) {
                return $this->getAllArchiveTableMonths();
            } else if ($dateSpecifier == self::CURRENT_MONTH_STRING) {
                $now = Date::factory('now');
                return array($now->toString('Y') . '_' . $now->toString('m'));
            }
        }

        $tableMonths = array();
        foreach ($dateSpecifier as $date) {
            $date = Date::factory($date);
            $tableMonths[] = $date->toString('Y') . '_' . $date->toString('m');
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
}
