<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Mail;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\ConsoleCommand\ConsoleCommandConsoleOutput;
use Piwik\Plugin\ConsoleCommand\ConsoleCommandBufferedOutput;

/**
 * Diagnostic command that returns consolidated information about the status of archiving
 */
class ArchivingStatus extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('diagnostics:archiving-status');
        $this->addNoValueOption(
            'with-stats',
            null,
            "If supplied, the command will include instance statistics such as monthly hits and site count"
        );
        $this->addOptionalValueOption(
            'email',
            null,
            "If supplied, the command will email the output to the supplied email address"
        );
        $this->setDescription('');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();

        // If using email option then buffer output
        if ($input->getOption('email')) {
            $output = new ConsoleCommandBufferedOutput();
            $this->setOutput($output);
        } else {
            $output = $this->getOutput();
        }

        // Queue
        $this->outputSectionHeader($output, 'Invalidation Queue');
        $archiveTableDao = StaticContainer::get('Piwik\DataAccess\ArchiveTableDao');
        $headers = ['Invalidation', 'Segment', 'Site', 'Period', 'Date', 'Time Queued', 'Waiting', 'Started', 'Processing', 'Status'];
        $queue = $archiveTableDao->getInvalidationQueueData(true);
        $this->renderTable($headers, $queue);

        // Metrics
        $this->outputSectionHeader($output, 'Archiving Metrics');
        $am = new ArchivingMetrics();
        $this->renderTable(['Metric', 'Value'], $am->getMetrics());

        // Optional instance stats
        if ($input->getOption('with-stats')) {
            $this->outputSectionHeader($output, 'Instance Statistics');
            $ais = new ArchivingInstanceStatistics();
            $this->renderTable(['Statistic Name', 'Value'], $ais->getArchivingInstanceStatistics());
        }

        // Config
        $this->outputSectionHeader($output, 'Archiving Configuration Settings');
        $am = new ArchivingConfig();
        $this->renderTable(['Section', 'Setting', 'Value'], $am->getArchivingConfig());

        if ($input->getOption('email')) {
            $address = $input->getOption('email');
            $content = 'This email was sent via the Matomo diagnostic:archiving-status command';
            $content .= '<pre>';
            $content .= $output->fetch();
            $content .= '</pre>';
            $mail = new Mail();
            $mail->setDefaultFromPiwik();
            $mail->addTo($address);
            $mail->setSubject('Matomo Archiving Diagnostics');
            $mail->setWrappedHtmlBody($content);
            $output = new ConsoleCommandConsoleOutput();
            $this->setOutput($output);
            try {
                $mail->send();
                $output->writeln("Archiving diagnostic email successfully sent to " . $address);
            } catch (\Exception $e) {
                $output->writeln("Failed to send email to " . $address . ", error: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Output a styled header string
     *
     * @param mixed     $output
     * @param string    $title
     *
     * @return void
     */
    private function outputSectionHeader($output, string $title): void
    {
        $output->writeln("\n<info>" . $title . "</info>");
    }
}
