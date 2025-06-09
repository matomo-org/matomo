<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function __construct(?LogDeleter $logDeleter = null, ?RawLogDao $rawLogDao = null)
    {
        parent::__construct();

        $this->logDeleter = $logDeleter ?: StaticContainer::get('Piwik\LogDeleter');
        $this->rawLogDao = $rawLogDao ?: StaticContainer::get('Piwik\DataAccess\RawLogDao');
    }

    protected function configure()
    {
        $this->setName('core:delete-logs-data');
        $this->setDescription('Delete data from the user log tables: ' . implode(', ', self::$logTables) . '.');
        $this->addRequiredValueOption('dates', null, 'Delete log data with a date within this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOptionalValueOption(
            'idsite',
            null,
            'Delete log data belonging to the site with this ID. Comma separated list of website id. Eg, 1, 2, 3, etc. By default log data from all sites is purged.'
        );
        $this->addRequiredValueOption('limit', null, "The number of rows to delete at a time. The larger the number, "
            . "the more time is spent deleting logs, and the less progress will be printed to the screen.", 1000);
        $this->addNoValueOption(
            'optimize-tables',
            null,
            "If supplied, the command will optimize log tables after deleting logs. Note: this can take a very long time."
        );
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        [$from, $to] = $this->getDateRangeToDeleteFrom();
        $idSite = $this->getSiteToDeleteFrom();
        $step = $this->getRowIterationStep();

        $output->writeln(sprintf(
            "<info>Preparing to delete all visits belonging to %s between $from and $to.</info>",
            $idSite ? "website $idSite" : "ALL websites"
        ));

        $confirm = $this->askForDeleteConfirmation();
        if (!$confirm) {
            return self::FAILURE;
        }

        $timer = new Timer();

        try {
            $logsDeleted = $this->logDeleter->deleteVisitsFor($from, $to, $idSite, $step, function () use ($output) {
                $output->write('.');
            });
        } catch (\Exception $ex) {
            $output->writeln('');

            throw $ex;
        }

        $this->writeSuccessMessage("Successfully deleted {$logsDeleted} visits. <comment>{$timer}</comment>");

        if ($input->getOption('optimize-tables')) {
            $this->optimizeTables();
        }

        return self::SUCCESS;
    }

    /**
     * @return Date[]
     */
    private function getDateRangeToDeleteFrom()
    {
        $dates = $this->getInput()->getOption('dates');
        if (empty($dates)) {
            throw new \InvalidArgumentException("No date range supplied in --dates option. Deleting all logs by default is not allowed, you must specify a date range.");
        }

        $parts = explode(',', $dates);
        $parts = array_map('trim', $parts);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates");
        }

        [$start, $end] = $parts;

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

    private function getSiteToDeleteFrom()
    {
        $idSite = $this->getInput()->getOption('idsite');

        if (is_null($idSite)) {
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

    private function getRowIterationStep()
    {
        $step = (int) $this->getInput()->getOption('limit');

        if ($step <= 0) {
            throw new \InvalidArgumentException("Invalid row limit supplied: $step. Must be a number greater than 0.");
        }

        return $step;
    }

    private function askForDeleteConfirmation()
    {
        if (!$this->getInput()->isInteractive()) {
            return true;
        }

        return $this->askForConfirmation(
            '<comment>You are about to delete log data. This action cannot be undone, are you sure you want to continue? (Y/N)</comment> ',
            false
        );
    }

    private function optimizeTables()
    {
        foreach (self::$logTables as $table) {
            $this->getOutput()->write("Optimizing table $table... ");

            $timer = new Timer();

            $prefixedTable = Common::prefixTable($table);

            $done = Db\Schema::getInstance()->optimizeTables([$prefixedTable]);

            if ($done) {
                $this->getOutput()->writeln("done. <comment>" . $timer . "</comment>");
            } else {
                $this->getOutput()->writeln("skipped! <comment>" . $timer . "</comment>");
            }
        }

        $this->writeSuccessMessage('Table optimization finished.');
    }
}
