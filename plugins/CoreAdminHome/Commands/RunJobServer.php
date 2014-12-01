<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Jobs\Impl\CliProcessor;
use Piwik\Jobs\Helper as JobsHelper;
use Piwik\Jobs\Impl\DistributedJobsQueue;
use Piwik\Jobs\Job;
use Piwik\Jobs\Queue;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunJobServer extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:jobs-server');
        $this->setDescription('Runs a job server that will continually look for jobs from the specified queue and execute them.');
        $this->addOption('queue', null, InputOption::VALUE_REQUIRED, "The un-prefixed ID of a named queue in the DI container. If 'redis_queue' is used, then"
            . " the DI object with the key 'jobs.queues.redis_queue' is used. If nothing is supplied, the default MySQL based queue is used.");
        $this->addOption('processor', null, InputOption::VALUE_REQUIRED, "The un-prefixed ID of a processor in the DI container. If 'gearman_processor' is used, then"
            . " the DI object with the key 'jobs.processors.gearman_processor' is used. If nothing is supplied, the default CliMulti based processor is used"
            . " to process jobs.");
        $this->addOption('max-processes', null, InputOption::VALUE_REQUIRED, "If the default processor is used, this determines the maximum number of child"
            . " processes to create at a time.", CliProcessor::DEFAULT_MAX_SPAWNED_PROCESS_COUNT);
        $this->addOption('sleep-time', null, InputOption::VALUE_REQUIRED, "If the default processor is used, this determines the amount of seconds to wait before"
            . " checking the job queue, if the queue is found to be empty. Defaults to " . CliProcessor::DEFAULT_SLEEP_TIME . " seconds.",
            CliProcessor::DEFAULT_SLEEP_TIME);
        $this->addOption("exit-when-no-jobs", null, InputOption::VALUE_NONE, "If supplied, the process will exit when the jobs queue is found to be empty."
            . " If not supplied, the process will continually check for jobs.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->getQueue($input);
        $processor = $this->getProcessor($input, $queue);

        $finishWhenNoJobs = $input->getOption('exit-when-no-jobs');
        if ($finishWhenNoJobs) {
            $output->writeln("NOTE: Will exit when no jobs left to process.");
            $output->writeln("");
        }

        $processor->setOnJobsStartingCallback(function ($jobs) use ($output) {
            $output->writeln("Executing <comment>" . count($jobs) . "</comment> jobs...");

            /** @var Job $job */
            foreach ($jobs as $job) {
                $output->writeln("  <comment>" . get_class($job) . "</comment>: " . json_encode($job->getJobData()));
            }
        });

        $processor->setOnJobsFinishedCallback(function ($jobsAndResponses) use ($output) {
            $output->writeln("Finished executing <comment>" . count($jobsAndResponses) . "</comment> jobs...");

            foreach ($jobsAndResponses as $jobAndResponse) {
                /** @var Job $job */
                list($job, $response) = $jobAndResponse;

                $output->writeln("  <comment>" . get_class($job) . "</comment>: " . json_encode($job->getJobData()));

                if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
                    $output->writeln("    " . $response);
                }
            }
        });

        $output->writeln("<info>Running job processor...</info>");

        $processor->startProcessing($finishWhenNoJobs);

        $output->writeln("<info>Job processing finished. Exiting.</info>");

        return 0;
    }

    private function getQueue(InputInterface $input)
    {
        $queueName = $input->getOption('queue');
        if (empty($queueName)) {
            return $this->createDefaultQueue();
        } else {
            return JobsHelper::getNamedQueue($queueName);
        }
    }

    private function createDefaultQueue()
    {
        return new DistributedJobsQueue();
    }

    private function getProcessor(InputInterface $input, Queue $queue)
    {
        $processorName = $input->getOption('processor');
        if (empty($processorName)) {
            return $this->createDefaultProcessor($input, $queue);
        } else {
            return JobsHelper::getNamedProcessor($processorName);
        }
    }

    private function createDefaultProcessor(InputInterface $input, Queue $queue)
    {
        $maxProcesses = (int) $input->getOption('max-processes');
        $sleepTime = (int) $input->getOption('sleep-time');

        return new CliProcessor($queue, $maxProcesses, $sleepTime);
    }
}