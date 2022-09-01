<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\SettingsServer;

/**
 * There are three different states
 * - PID file exists with empty content: Process is created but not started
 * - PID file exists with the actual process PID as content: Process is running
 * - PID file does not exist: Process is marked as finished
 *
 * Class Process
 */
class Process
{
    const PS_COMMAND = 'ps x';
    const AWK_COMMAND = 'awk \'! /defunct/ {print $1}\'';

    private $finished = null;
    private $pidFile = '';
    private $timeCreation = null;
    private static $isSupported = null;
    private $pid = null;
    private $started = null;

    public function __construct($pid)
    {
        if (!Filesystem::isValidFilename($pid)) {
            throw new \Exception('The given pid has an invalid format');
        }

        $pidDir = CliMulti::getTmpPath();
        Filesystem::mkdir($pidDir);

        $this->pidFile      = $pidDir . '/' . $pid . '.pid';
        $this->timeCreation = time();
        $this->pid = $pid;

        $this->markAsNotStarted();
    }

    private static function isForcingAsyncProcessMode()
    {
        try {
            return (bool) StaticContainer::get('test.vars.forceCliMultiViaCurl');
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getPid()
    {
        return $this->pid;
    }

    private function markAsNotStarted()
    {
        $content = $this->getPidFileContent();

        if ($this->doesPidFileExist($content)) {
            return;
        }

        $this->writePidFileContent('');
    }

    public function hasStarted($content = null)
    {
        if (!$this->started) {
            $this->started = $this->checkPidIfHasStarted($content);
        }
        // PID will be deleted when process has finished so we want to remember this process started at some point. Otherwise we might return false here once the process finished.
        // therefore we want to "cache" a successful start
        return $this->started;
    }

    private function checkPidIfHasStarted($content = null)
    {
        if (is_null($content)) {
            $content = $this->getPidFileContent();
        }

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

    public function hasFinished()
    {
        if ($this->finished) {
            return true;
        }

        $content = $this->getPidFileContent();

        return !$this->doesPidFileExist($content);
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

        if (!$this->pidFileSizeIsNormal($content)) {
            $this->finishProcess();
            return false;
        }

        if ($this->isProcessStillRunning($content)) {
            return true;
        }

        if ($this->hasStarted($content)) {
            $this->finishProcess();
        }

        return false;
    }

    private function pidFileSizeIsNormal($content)
    {
        $size = Common::mb_strlen($content);

        return $size < 500;
    }

    public function finishProcess()
    {
        $this->finished = true;
        Filesystem::deleteFileIfExists($this->pidFile);
    }

    private function doesPidFileExist($content)
    {
        return false !== $content;
    }

    private function isProcessStillRunning($content)
    {
        if (!self::isSupported()) {
            return true;
        }

        $lockedPID   = trim($content);
        $runningPIDs = self::getRunningProcesses();

        return !empty($lockedPID) && in_array($lockedPID, $runningPIDs);
    }

    private function getPidFileContent()
    {
        return @file_get_contents($this->pidFile);
    }

    /**
     * Tests only
     * @internal
     * @param $content
     */
    public function writePidFileContent($content)
    {
        file_put_contents($this->pidFile, $content);
    }

    public static function isSupported()
    {
        if (!isset(self::$isSupported)) {
            $reasons = self::isSupportedWithReason();
            self::$isSupported = empty($reasons);
        }
        return self::$isSupported;
    }

    public static function isSupportedWithReason()
    {
        $reasons = [];

        if (defined('PIWIK_TEST_MODE')
            && self::isForcingAsyncProcessMode()
        ) {
            $reasons[] = 'forcing multicurl use for tests';
        }

        if (SettingsServer::isWindows()) {
            $reasons[] = 'not supported on windows';
            return $reasons;
        }

        if (self::isMethodDisabled('shell_exec')) {
            $reasons[] = 'shell_exec is disabled';
            return $reasons; // shell_exec is used for almost every other check
        }

        $getMyPidDisabled = self::isMethodDisabled('getmypid');
        if ($getMyPidDisabled) {
            $reasons[] = 'getmypid is disabled';
        }

        if (self::isSystemNotSupported()) {
            $reasons[] = 'system returned by `uname -a` is not supported';
        }

        if (!self::psExistsAndRunsCorrectly()) {
            $reasons[] = 'shell_exec(' . self::PS_COMMAND . '" 2> /dev/null") did not return a success code';
        } else if (!$getMyPidDisabled) {
            $pid = @getmypid();
            if (empty($pid) || !in_array($pid, self::getRunningProcesses())) {
                $reasons[] = 'could not find our pid (from getmypid()) in the output of `' . self::PS_COMMAND . '`';
            }
        }

        if (!self::awkExistsAndRunsCorrectly()) {
            $reasons[] = 'awk is not available or did not run as we would expect it to';
        }

        return $reasons;
    }

    private static function psExistsAndRunsCorrectly()
    {
        return self::returnsSuccessCode(self::PS_COMMAND . ' 2>/dev/null');
    }

    private static function awkExistsAndRunsCorrectly()
    {
        $testResult = @shell_exec('echo " 537 s000 Ss 0:00.05 login -pfl theuser /bin/bash -c exec -la bash /bin/bash" | ' . self::AWK_COMMAND . ' 2>/dev/null');
        return trim($testResult) == '537';
    }

    private static function isSystemNotSupported()
    {
        $uname = @shell_exec('uname -a 2> /dev/null');

        if (empty($uname)) {
            $uname = php_uname();
        }

        if (strpos($uname, 'synology') !== false) {
            return true;
        }
        return false;
    }

    public static function isMethodDisabled($command)
    {
        if (!function_exists($command)) {
            return true;
        }

        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        return in_array($command, $disabled)  || !function_exists($command);
    }

    private static function returnsSuccessCode($command)
    {
        $exec = $command . ' > /dev/null 2>&1; echo $?';
        $returnCode = @shell_exec($exec);

        if (false === $returnCode || null === $returnCode) {
            return false;
        }

        $returnCode = trim($returnCode);
        return 0 == (int) $returnCode;
    }

    public static function getListOfRunningProcesses()
    {
        $processes = @shell_exec(self::PS_COMMAND . ' 2>/dev/null');
        if (empty($processes)) {
            return array();
        }
        return explode("\n", $processes);
    }

    /**
     * @return int[] The ids of the currently running processes
     */
     public static function getRunningProcesses()
     {
         $ids = explode("\n", trim(shell_exec(self::PS_COMMAND . ' 2>/dev/null | ' . self::AWK_COMMAND . ' 2>/dev/null')));

         $ids = array_map('intval', $ids);
         $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });

         return $ids;
     }
}
