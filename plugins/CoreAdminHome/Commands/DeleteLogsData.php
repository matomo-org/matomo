<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Db;
use Piwik\LogDeleter;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Piwik\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to selectively delete visits.
 */
class DeleteLogsData extends ConsoleCommand
{
    private static $logTables = array(
        'log_visit',
        'log_link_visit_action',
        'log_conversion',
        'log_conversion_item',
        'log_action'
    );

    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * @var LogDeleter
     */
    private $logDeleter;

    public function __construct(LogDeleter $logDeleter = null, RawLogDao $rawLogDao = null)
    {
        parent::__construct();

        $this->logDeleter = $logDeleter ?: StaticContainer::get('Piwik\LogDeleter');
        $this->rawLogDao = $rawLogDao ?: StaticContainer::get('Piwik\DataAccess\RawLogDao');
    }

    protected function configure()
    {
        $this->setName('core:delete-logs-data');
        $this->setDescription('Delete data from the user log tables: ' . implode(', ', self::$logTables) . '.');
        $this->addOption('dates', null, InputOption::VALUE_REQUIRED, 'Delete log data with a date within this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOption('idsite', null, InputOption::VALUE_OPTIONAL,
            'Delete log data belonging to the site with this ID. Comma separated list of website id. Eg, 1, 2, 3, etc. By default log data from all sites is purged.');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, "The number of rows to delete at a time. The larger the number, "
            . "the more time is spent deleting logs, and the less progress will be printed to the screen.", 1000);
        $this->addOption('optimize-tables', null, InputOption::VALUE_NONE,
            "If supplied, the command will optimize log tables after deleting logs. Note: this can take a very long time.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($from, $to) = $this->getDateRangeToDeleteFrom($input);
        $idSite = $this->getSiteToDeleteFrom($input);
        $step = $this->getRowIterationStep($input);

        $output->writeln( sprintf(
                "<info>Preparing to delete all visits belonging to %s between $from and $to.</info>",
                $idSite ? "website $idSite" : "ALL websites"
        ));

        $confirm = $this->askForDeleteConfirmation($input, $output);
        if (!$confirm) {
            return;
        }

        $timer = new Timer();

        try {
            $logsDeleted = $this->logDeleter->deleteVisitsFor($from, $to, $idSite, $step, function () use ($output) {
                $output->write('.');
            });
        } catch (\Exception $ex) {
            $output->writeln("");

            throw $ex;
        }

        $this->writeSuccessMessage($output, array(
            "Successfully deleted $logsDeleted visits. <comment>" . $timer . "</comment>"));

        if ($input->getOption('optimize-tables')) {
            $this->optimizeTables($output);
        }
    }

    /**
     * @param InputInterface $input
     * @return Date[]
     */
    private function getDateRangeToDeleteFrom(InputInterface $input)
    {
        $dates = $input->getOption('dates');
        if (empty($dates)) {
            throw new \InvalidArgumentException("No date range supplied in --dates option. Deleting all logs by default is not allowed, you must specify a date range.");
        }

        $parts = explode(',', $dates);
        $parts = array_map('trim', $parts);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates");
        }

        list($start, $end) = $parts;

        try {
            /** @var Date[] $dateObjects */
            $dateObjects = array(Date::factory($start), Date::factory($end));
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates (" . $ex->getMessage() . ")", $code = 0, $ex);
        }

        if ($dateObjects[0]->getTimestamp() > $dateObjects[1]->getTimestamp()) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates (first date is older than the last date)");
        }

        $dateObjects = array($dateObjects[0]->getDatetime(), $dateObjects[1]->getDatetime());

        return $dateObjects;
    }

    private function getSiteToDeleteFrom(InputInterface $input)
    {
        $idSite = $input->getOption('idsite');

        if(is_null($idSite)) {
            return $idSite;
        }
        // validate the site ID
        try {
            new Site($idSite);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException("Invalid site ID: $idSite", $code = 0, $ex);
        }

        return $idSite;
    }

    private function getRowIterationStep(InputInterface $input)
    {
        $step = (int) $input->getOption('limit');

        if ($step <= 0) {
            throw new \InvalidArgumentException("Invalid row limit supplied: $step. Must be a number greater than 0.");
        }

        return $step;
    }

    private function askForDeleteConfirmation(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('<comment>You are about to delete log data. This action cannot be undone, are you sure you want to continue? (Y/N)</comment> ', false);

        return $helper->ask($input, $output, $question);
    }

    private function optimizeTables(OutputInterface $output)
    {
        foreach (self::$logTables as $table) {
            $output->write("Optimizing table $table... ");

            $timer = new Timer();

            $prefixedTable = Common::prefixTable($table);

            $done = Db::optimizeTables($prefixedTable);

            if($done) {
                $output->writeln("done. <comment>" . $timer . "</comment>");
            } else {
                $output->writeln("skipped! <comment>" . $timer . "</comment>");
            }
        }

        $this->writeSuccessMessage($output, array("Table optimization finished."));
    }
}