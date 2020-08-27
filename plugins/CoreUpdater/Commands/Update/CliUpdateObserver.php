<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater\Commands\Update;

use Piwik\Updater\Migration;
use Piwik\Updater\UpdateObserver;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpdateObserver used to output progress of an update initiated on the command line. Prints the currently
 * executing query and the total number of queries to run.
 *
 * @package CoreUpdater
 */
class CliUpdateObserver extends UpdateObserver
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $totalMigrationQueryCount;

    /**
     * @var int
     */
    private $currentMigrationQueryExecutionCount = 0;

    public function __construct(OutputInterface $output, $totalMigrationQueryCount)
    {
        $this->output = $output;
        $this->totalMigrationQueryCount = $totalMigrationQueryCount;
    }

    public function onStartExecutingMigration($updateFile, Migration $migration)
    {
        $string = $migration->__toString();
        $this->output->write("  Executing <comment>$string</comment>... ");

        ++$this->currentMigrationQueryExecutionCount;
    }

    public function onFinishedExecutingMigration($updateFile, Migration $migration)
    {
        $this->output->writeln("Done. <info>[{$this->currentMigrationQueryExecutionCount} / {$this->totalMigrationQueryCount}]</info>");
    }
}