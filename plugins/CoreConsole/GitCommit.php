<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole;

use Piwik\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class GitCommit extends Command
{
    protected function configure()
    {
        $this->setName('git:commit')
             ->setDescription('Commit')
             ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Commit Message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commitMessage = $input->getOption('message');

        if (empty($commitMessage)) {
            $output->writeln('No message specified');
            return;
        }

        $cmd = sprintf("grep path .gitmodules | sed 's/.*= //'");
        $submodules = shell_exec($cmd);
        $submodules = explode("\n", $submodules);

        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            $cmd    = sprintf('cd %s/%s && git status --porcelain', PIWIK_DOCUMENT_ROOT, $submodule);
            $status = trim(shell_exec($cmd));
            if (false !== strpos($status, '??')) {
                $output->writeln(sprintf('%s has untracked changes, will ignore. Status: %s', $submodule, $status));
                continue;
            }

            if (empty($status)) {
                $output->writeln(sprintf('%s has no changes, will ignore', $submodule));
                continue;
            }

            $cmd = sprintf('cd %s/%s && git pull && git add . && git commit -am "%s"', PIWIK_DOCUMENT_ROOT, $submodule, $commitMessage);
            $this->passthru($cmd, $output);
        }

        $cmd = sprintf('cd %s && git commit -m "%s"', PIWIK_DOCUMENT_ROOT, $commitMessage);
        $this->passthru($cmd, $output);

        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            $cmd = sprintf('cd %s && git add %s', PIWIK_DOCUMENT_ROOT, $submodule);
            $this->passthru($cmd, $output);
        }

        $cmd = sprintf('cd %s && git commit -m "Converged submodules"', PIWIK_DOCUMENT_ROOT);
        $this->passthru($cmd, $output);
    }

    private function passthru($cmd, OutputInterface $output)
    {
        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}