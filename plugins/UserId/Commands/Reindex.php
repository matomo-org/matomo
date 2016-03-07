<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UserId\API;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a CLI command userid:reindex, that allows to reindex unique user IDs and some statistics
 * from log_visit table to user_ids table.
 */
class Reindex extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('userid:reindex');
        $this->setDescription('Index unique user IDs and some statistics from log_visit table to user_ids table.');
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'If set, user IDs index will be cleaned and rebuilt. Otherwise an incremental index update will be performed.', null);
    }

    /**
     * To run incremental reindex: ./console userid:reindex
     * To clean index and reindex all visits: ./console userid:reindex --clean
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = API::getInstance();

        if ($input->getOption('clean')) {
            $api->cleanIndex();
        }

        if ($api->reindex()) {
            $this->writeSuccessMessage($output, array('User IDs reindex finished successfully.'));
        } else {
            $output->writeln('User IDs reindex finished with errors. See logs for details.');
        }
    }
}
