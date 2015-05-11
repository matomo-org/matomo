<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\LogPurger;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * TODO
 */
class DeleteLogs extends ConsoleCommand
{
    // TODO: move this to RawLogDao and make it public. use it there and here.
    private static $logTables = array(
        'log_visit' => 'idvisit',
        'log_link_visit_action' => 'idlink_va',
        'log_conversion' => 'idvisit',
        'log_conversion_item' => 'idvisit',
        'log_action' => 'idaction'
    );

    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * @var LogPurger
     */
    private $logPurger;

    public function __construct(LogPurger $logPurger = null, RawLogDao $rawLogDao = null)
    {
        parent::__construct();

        $this->logPurger = $logPurger ?: StaticContainer::get('Piwik\LogPurger');
        $this->rawLogDao = $rawLogDao ?: StaticContainer::get('Piwik\DataAccess\RawLogDao');
    }

    protected function configure()
    {
        $this->setName('logs:delete');
        $this->setDescription('Delete data from one of the log tables: ' . implode(', ', array_keys(self::$logTables)) . '.');
        $this->addOption('dates', null, InputOption::VALUE_REQUIRED, 'Delete log data with a date within this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOption('site', null, InputOption::VALUE_REQUIRED,
            'Delete log data belonging to the site with this ID. Eg, 1, 2, 3, etc. By default log data from all sites is purged.');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, "The number of rows to delete at a time. The larger the number, "
            . "the more time is spent deleting logs, and the less progress will be printed to the screen.", 1000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($from, $to) = $this->getDateRangeToDeleteFrom($input);
        $idSite = $this->getSiteToDeleteFrom($input);
        $step = $this->getRowIterationStep($input);

        $output->writeln("Preparing to delete all visits between $from and $to belonging to site $idSite.");

        $confirm = $this->askForDeleteConfirmation($input, $output);
        if (!$confirm) {
            return;
        }

        try {
            $logsDeleted = $this->logPurger->deleteVisitsFor($from, $to, $idSite, $step, function () use ($output) {
                $output->write('.');
            });
        } catch (\Exception $ex) {
            $output->writeln("");

            throw $ex;
        }

        $this->writeSuccessMessage($output, array("Successfully deleted $logsDeleted visits."));
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
        $idSite = $input->getOption('site');

        // validate the site ID
        try {
            new Site($idSite); // TODO: check error message returned from invalid site
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
        $question = new ConfirmationQuestion('<comment>You are about to delete log data. This action cannot be undone, are you sure you want to continue?</comment>', false);

        return $helper->ask($input, $output, $question);
    }
}
