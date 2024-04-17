<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;

/**
 * Diagnostic command that returns a snapshot of the archive invalidation queue
 */
class ArchivingQueue extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:archiving-queue');
        $this->addNoValueOption(
            'json',
            null,
            "If supplied, the command will return table data in json format"
        );
        $this->setDescription('Show the current state of the archive invalidations queue as a table');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $archiveTableDao = StaticContainer::get('Piwik\DataAccess\ArchiveTableDao');

        if ($input->getOption('json')) {
            $queue = $archiveTableDao->getInvalidationQueueData();
            $output->write(json_encode($queue));
        } else {
            $headers = ['Invalidation', 'Segment', 'Site', 'Period', 'Date', 'Time Queued', 'Waiting', 'Started', 'Processing', 'Status'];
            $queue = $archiveTableDao->getInvalidationQueueData(true);
            $this->renderTable($headers, $queue);
        }

        return self::SUCCESS;
    }
}
