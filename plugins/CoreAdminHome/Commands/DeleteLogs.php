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
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct()
    {
        parent::__construct();

        $this->rawLogDao = StaticContainer::get('Piwik\DataAccess\RawLogDao');
    }

    protected function configure()
    {
        $this->setName('logs:delete');
        $this->setDescription('Delete data from one of the log tables: ' . implode(', ', array_keys(self::$logTables)) . '.');
        $this->addOption('table', null, InputOption::VALUE_REQUIRED, "The table to delete from.");
        $this->addOption('dates', null, InputOption::VALUE_REQUIRED, 'Delete log data with a date within this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOption('site', null, InputOption::VALUE_REQUIRED,
            'Delete log data belonging to the site with this ID. Eg, 1, 2, 3, etc. By default log data from all sites is purged.');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, "The number of rows to delete at a time. The larger the number, "
            . "the more time is spent deleting logs, and the less progress will be printed to the screen.", 1000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: make sure to confirm to delete w/ warning that action cannot be undone.
        $table = $this->getTableToDeleteFrom($input);
        list($from, $to) = $this->getDateRangeToDeleteFrom($input);
        $idSite = $this->getSiteToDeleteFrom($input);
        $step = $this->getRowIterationStep($input);

        $logsDeleted = 0;

        $fields = array(self::$logTables[$table]);
        $conditions = array(
            array('visit_last_action_time', '>=', $from),
            array('visit_last_action_time', '<', $to),
            array('idsite', '==', $idSite)
        );

        $self = $this;
        $this->rawLogDao->forAllLogs($table, $fields, $conditions, $step, function ($logs) use ($self, $table, &$logsDeleted) {
            $logsDeleted += $self->deleteLogs($table, $logs);
        });

        $this->writeSuccessMessage($output, "Successfully deleted $logsDeleted rows from $table.");
    }

    private function getTableToDeleteFrom(InputInterface $input)
    {
        $table = $input->getOption('table');

        if (empty(self::$logTables[$table])) {
            throw new \InvalidArgumentException("Invalid table name '$table'. Supported values are: " . implode(', ', array_keys(self::$logTables)));
        }

        return $table;
    }

    private function deleteLogs($table, $logs)
    {
        $ids = array_map(function ($row) { return reset($row); }, $logs);

        // TODO: these methods should cascade; deleting visits must delete conversions/conversion items/etc.
        if ($table == 'log_visit') {
            return $this->rawLogDao->deleteVisits($ids);
        } else if ($table == 'log_link_visit_action') {
            return $this->rawLogDao->deleteVisitActions($ids);
        } else if ($table == 'log_conversion') {
            return $this->rawLogDao->deleteConversions($ids);
        } else if ($table == 'log_conversion_item') {
            return $this->rawLogDao->deleteConversionItems($ids);
        } else if ($table == 'log_action') {
            return $this->rawLogDao->deleteActions($ids);
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

        return array(Date::factory($start)->getDatetime(), Date::factory($end)->getDatetime());
    }

    private function getSiteToDeleteFrom(InputInterface $input)
    {
        $idSite = $input->getOption('site');

        // validate the site ID
        new Site($idSite); // TODO: check error message returned from invalid site

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
}
