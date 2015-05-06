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
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO
 */
class DeleteLogs extends ConsoleCommand
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

    public function __construct()
    {
        parent::__construct();

        $this->rawLogDao = StaticContainer::get('Piwik\DataAccess\RawLogDao');
    }

    protected function configure()
    {
        $this->setName('logs:delete');
        $this->setDescription('Delete data from one of the log tables: ' . implode(', ', self::$logTables) . '.');
        $this->addOption('table', null, InputOption::VALUE_REQUIRED, "The table to delete from.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->getTableToDeleteFrom($input);

        $logsDeleted = 0;

        $rawLogIterator = $this->rawLogDao->makeLogIterator(); // TODO [ should contain delete by params ]
        foreach ($rawLogIterator->getChunks() as $chunk) {
            if ($table == 'log_visit') { // TODO: move to private message
                $logsDeleted += $this->rawLogDao->deleteVisits($chunk->getIds());
            } else if ($table == 'log_link_visit_action') {
                $logsDeleted += $this->rawLogDao->deleteVisitActions($chunk->getIds());
            } else if ($table == 'log_conversion') {
                $logsDeleted += $this->rawLogDao->deleteConversions($chunk->getIds());
            } else if ($table == 'log_conversion_item') {
                $logsDeleted += $this->rawLogDao->deleteConversionItems($chunk->getIds());
            } else if ($table == 'log_action') {
                $logsDeleted += $this->rawLogDao->deleteActions($chunk->getIds());
            }
        }

        $this->writeSuccessMessage($output, "Successfully deleted $logsDeleted rows from $table.");
    }

    // TODO: in usercountry:attribute, use makeLogIterator above

    private function getTableToDeleteFrom(InputInterface $input)
    {
        $table = $input->getOption('table');

        if (!in_array($table, self::$logTables)) {
            throw new \InvalidArgumentException("Invalid table name '$table'. Supported values are: " . implode(', ', self::$logTables));
        }

        return $table;
    }
}
