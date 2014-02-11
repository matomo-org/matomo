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
    private $timeCreation = null;

    public function __construct($pid)
    {
        if (!Filesystem::isValidFilename($pid)) {
            throw new \Exception('The given pid has an invalid format');
        }

        $pidDir = PIWIK_INCLUDE_PATH . '/tmp/climulti';
        Filesystem::mkdir($pidDir, true);

        $this->pidFile      = $pidDir . '/' . $pid . '.pid';
        $this->timeCreation = time();

        $this->markAsNotStarted();
    }

    private function markAsNotStarted()
    {
        $content = $this->getPidFileContent();

        if ($this->doesPidFileExist($content)) {
            return;
        }

        $this->writePidFileContent('');
    }

    public function hasStarted()
    {
        $content = $this->getPidFileContent();

        if (!$this->doesPidFileExist($content)) {
            // process is finished, this means there was a start before
            return true;
        }

        if ('' === trim($content)) {
            // pid file is overwritten by startProcess()
            return false;
        }

        // process is probably running or pid file was not removed
        return true;
    }

    public function getSecondsSinceCreation()
    {
        return time() - $this->timeCreation;
    }

    public function startProcess()
    {
        $this->writePidFileContent(getmypid());
    }

    public function isRunning()
    {
        $content = $this->getPidFileContent();

        if (!$this->doesPidFileExist($content)) {
            return false;
        }

        if ($this->isProcessStillRunning($content)) {
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

    private function doesPidFileExist($content)
    {
        return false !== $content;
    }

    private function isProcessStillRunning($content)
    {
        $lockedPID = trim($content);

        if (!self::isSupported()) {
            return true;
        }

        $runningPIDs = explode("\n", trim( `ps -e | awk '{print $1}'` ));

        return in_array($lockedPID, $runningPIDs);
    }

    private function getPidFileContent()
    {
        return @file_get_contents($this->pidFile);
    }

    private function writePidFileContent($content)
    {
        file_put_contents($this->pidFile, $content);
    }

    public static function isSupported()
    {
        if (SettingsServer::isWindows()) {
            return false;
        }

        if (static::commandExists('ps') && self::commandExists('awk')) {
            return true;
        }

        return false;
    }

    private static function commandExists($command)
    {
        $result = shell_exec('which ' . escapeshellarg($command));

        return !empty($result);
    }
}