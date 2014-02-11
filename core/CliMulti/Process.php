<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\Filesystem;
use Piwik\SettingsServer;

class Process
{
    private $pidFile = '';

    public function __construct($pid)
    {
        $pidDir = PIWIK_INCLUDE_PATH . '/tmp/pids';
        Filesystem::mkdir($pidDir, true);

        $this->pidFile = $pidDir . '/' . $pid . '.pid';

        $this->markAsNotStarted();
    }

    private function markAsNotStarted()
    {
        if ($this->doesPidFileExist()) {
            return;
        }

        $this->writePidFileContent('');
    }

    public function hasStarted()
    {
        if (!$this->doesPidFileExist()) {
            // process is finished, this means there was a start before
            return true;
        }

        if ('' === trim($this->getPidFileContent())) {
            // pid file is overwritten by startProcess()
            return false;
        }

        // process is probably running or pid file was not removed
        return true;
    }

    public function startProcess()
    {
        $this->writePidFileContent(getmypid());
    }

    public function isRunning()
    {
        if (!$this->doesPidFileExist()) {
            return false;
        }

        if ($this->isProcessStillRunning()) {
            return true;
        }

        if ($this->hasStarted()) {
            $this->finishProcess();
        }

        return false;
    }

    public function finishProcess()
    {
        Filesystem::deleteFileIfExists($this->pidFile);
    }

    private function doesPidFileExist()
    {
        return file_exists($this->pidFile);
    }

    private function isProcessStillRunning()
    {
        if (SettingsServer::isWindows()) {
            return true;
        }

        $lockedPID   = trim($this->getPidFileContent());
        $runningPIDs = explode("\n", trim( `ps -e | awk '{print $1}'` ));

        return in_array($lockedPID, $runningPIDs);
    }

    private function getPidFileContent()
    {
        return file_get_contents($this->pidFile);
    }

    private function writePidFileContent($content)
    {
        file_put_contents($this->pidFile, $content);
    }
}