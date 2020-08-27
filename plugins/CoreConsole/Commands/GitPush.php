<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GitPush extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled() && SettingsPiwik::isGitDeployment();
    }

    protected function configure()
    {
        $this->setName('git:push');
        $this->setDescription('Push Piwik repo and all submodules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = sprintf('cd %s && git push --recurse-submodules=on-demand', PIWIK_DOCUMENT_ROOT);
        $output->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }

    private function hasUnpushedCommits()
    {
        $cmd = sprintf('cd %s && git log @{u}..',PIWIK_DOCUMENT_ROOT);
        $hasUnpushedCommits = shell_exec($cmd);
        $hasUnpushedCommits = trim($hasUnpushedCommits);

        return !empty($hasUnpushedCommits);
    }
}
