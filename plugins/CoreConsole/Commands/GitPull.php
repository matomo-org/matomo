<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;

/**
 */
class GitPull extends ConsoleCommand
{
    public function isEnabled()
    {
        return SettingsPiwik::isGitDeployment();
    }

    protected function configure()
    {
        $this->setName('git:pull');
        $this->setDescription('Pull Piwik repo and all submodules');
    }

    protected function getBranchName()
    {
        $cmd    = sprintf('cd %s && git rev-parse --abbrev-ref HEAD', PIWIK_DOCUMENT_ROOT);
        $branch = shell_exec($cmd);

        return trim($branch);
    }

    protected function doExecute(): int
    {
        if ('master' != $this->getBranchName()) {
            $this->getOutput()->writeln('<info>Doing nothing because you are not on the master branch in super repo.</info>');
            return self::SUCCESS;
        }

        $cmd = sprintf('cd %s && git checkout master && git pull && git submodule update --init --recursive --remote', PIWIK_DOCUMENT_ROOT);
        $this->passthru($cmd);

        $cmd = 'git submodule foreach "(git checkout master; git pull)&"';
        $this->passthru($cmd);

        return self::SUCCESS;
    }

    private function passthru($cmd)
    {
        $this->getOutput()->writeln('Executing command: ' . $cmd);
        passthru($cmd);
    }
}
