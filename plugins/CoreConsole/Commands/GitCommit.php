<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GitCommit extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled() && SettingsPiwik::isGitDeployment();
    }

    protected function configure()
    {
        $this->setName('git:commit')
             ->setDescription('Commit')
             ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Commit Message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $submodules = $this->getSubmodulePaths();

        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            $status = $this->getStatusOfSubmodule($submodule);
            if (false !== strpos($status, '?? ')) {
                $output->writeln(sprintf('<error>%s has untracked files or folders. Delete or add them and try again.</error>', $submodule));
                $output->writeln('<error>Status:</error>');
                $output->writeln(sprintf('<comment>%s</comment>', $status));
                return;
            }
        }

        $commitMessage = $input->getOption('message');

        if (empty($commitMessage)) {
            $output->writeln('No message specified. Use option -m or --message.');
            return;
        }

        if (!$this->hasChangesToBeCommitted()) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $question = '<question>There are no changes to be commited in the super repo, do you just want to commit and converge submodules?</question>';
            if (!$dialog->askConfirmation($output, $question, false)) {
                $output->writeln('<info>Cool, nothing done. Stage files using "git add" and try again.</info>');
                return;
            }
        }

        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            $status = $this->getStatusOfSubmodule($submodule);
            if (empty($status)) {
                $output->writeln(sprintf('%s has no changes, will ignore', $submodule));
                continue;
            }

            $cmd = sprintf('cd %s/%s && git pull && git add . && git commit -am "%s"', PIWIK_DOCUMENT_ROOT, $submodule, $commitMessage);
            $this->passthru($cmd, $output);
        }

        if ($this->hasChangesToBeCommitted()) {
            $cmd = sprintf('cd %s && git commit -m "%s"', PIWIK_DOCUMENT_ROOT, $commitMessage);
            $this->passthru($cmd, $output);
        }

        foreach ($submodules as $submodule) {
            if (empty($submodule)) {
                continue;
            }

            $cmd = sprintf('cd %s && git add %s', PIWIK_DOCUMENT_ROOT, $submodule);
            $this->passthru($cmd, $output);
        }

        if ($this->hasChangesToBeCommitted()) {
            $cmd = sprintf('cd %s && git commit -m "Updating submodules"', PIWIK_DOCUMENT_ROOT);
            $this->passthru($cmd, $output);
        }
    }

    private function passthru($cmd, OutputInterface $output)
    {
        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }

    private function hasChangesToBeCommitted()
    {
        $cmd    = sprintf('cd %s && git status --porcelain', PIWIK_DOCUMENT_ROOT);
        $result = shell_exec($cmd);
        $result = trim($result);

        if (false !== strpos($result, 'M  ')) {
            // stages
            return true;
        }

        if (false !== strpos($result, 'MM ')) {
            // staged and modified
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getSubmodulePaths()
    {
        $cmd        = sprintf("grep path .gitmodules | sed 's/.*= //'");
        $submodules = shell_exec($cmd);
        $submodules = explode("\n", $submodules);

        return $submodules;
    }

    protected function getStatusOfSubmodule($submodule)
    {
        $cmd    = sprintf('cd %s/%s && git status --porcelain', PIWIK_DOCUMENT_ROOT, $submodule);
        $status = trim(shell_exec($cmd));

        return $status;
    }
}
