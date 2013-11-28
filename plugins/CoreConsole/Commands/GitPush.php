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

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class GitPush extends ConsoleCommand
{
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