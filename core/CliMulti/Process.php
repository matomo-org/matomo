<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\CliMulti;
use Piwik\Filesystem;
use Piwik\SettingsServer;

/**
 * There are three different states
 * - PID file exists with empty content: Process is created but not started
 * - PID file exists with the actual process PID as content: Process is runnning
 * - PID file does not exist: Process is marked as finished
 *
 * Class Process
 */
class Process
{
    private $pidFile = '';
    private $timeCreation = null;
    private $isSupported = null;

    public function __construct($pid)
    {
        if (!Filesystem::isValidFilename($pid)) {
            throw new \Exception('The given pid has an invalid format');
        }

        $pidDir = CliMulti::getTmpPath();
        Filesystem::mkdir($pidDir);

        $this->isSupported  = self::isSupported();
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

    public function hasStarted($content = null)
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

        if ($this->isProcessStillRunning($content)) {
            return true;
        }

        if ($this->hasStarted($content)) {
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
        if (!$this->isSupported) {
            return true;
        }

        $lockedPID   = trim($content);
        $runningPIDs = explode("\n", trim( `ps ex | awk '{print $1}'` ));

        return !empty($lockedPID) && in_array($lockedPID, $runningPIDs);
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

        if (self::shellExecFunctionIsDisabled()) {
            return false;
        }

        if (self::isSystemNotSupported()) {
            return false;
        }

        if(!self::isProcFSMounted()) {
            return false;
        }

        if (static::commandExists('ps') && self::returnsSuccessCode('ps') && self::commandExists('awk')) {
            return true;
        }

        return false;
    }

    private static function isSystemNotSupported()
    {
        $uname = @shell_exec('uname -a');

        if(empty($uname)) {
            $uname = php_uname();
        }

        if(strpos($uname, 'synology') !== false) {
            return true;
        }
        return false;
    }

    private static function shellExecFunctionIsDisabled()
    {
        $command = 'shell_exec';
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        return in_array($command, $disabled);
    }

    private static function returnsSuccessCode($command)
    {
        $exec = $command . ' > /dev/null 2>&1; echo $?';
        $returnCode = shell_exec($exec);
        $returnCode = trim($returnCode);
        return 0 == (int) $returnCode;
    }

    private static function commandExists($command)
    {
        $result = shell_exec('which ' . escapeshellarg($command) . ' 2> /dev/null');

        return !empty($result);
    }

    /**
     * ps -e requires /proc
     * @return bool
     */
    private static function isProcFSMounted()
    {
        if(is_resource(@fopen('/proc', 'r'))) {
            return true;
        }
        // Testing if /proc is a resource with @fopen fails on systems with open_basedir set.
        // by using stat we not only test the existance of /proc but also confirm it's a 'proc' filesystem
        $type = shell_exec('stat -f -c "%T" /proc 2>/dev/null');
        return strpos($type, 'proc') === 0;
    }

}
