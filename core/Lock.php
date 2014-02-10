<?php
namespace Piwik;

class Lock
{
    private $locksDir = '';
    private $name;

    public function __construct($name)
    {
        $this->locksDir   = PIWIK_INCLUDE_PATH . '/tmp/';
        $this->name       = $name;
    }

    public function lock()
    {
        $lockFile = $this->getLockFile();

        file_put_contents($lockFile, getmypid());
    }

    public function isLocked()
    {
        Filesystem::createWritableFolderIfNeeded($this->locksDir);

        if (!$this->doesLockFileExist()) {
            return false;
        }

        if ($this->isProcessStillRunning()) {
            return true;
        }

        $this->removeLock();

        return false;
    }

    public function removeLock()
    {
        $lockFile = $this->getLockFile();

        Filesystem::deleteIfExists($lockFile);
    }

    private function getLockFile()
    {
        return $this->locksDir . '/' . $this->name . '.lock';
    }

    private function doesLockFileExist()
    {
        $lockFile = $this->getLockFile();

        return file_exists($lockFile);
    }

    private function isProcessStillRunning()
    {
        $lockFile    = $this->getLockFile();
        $lockedPID   = file_get_contents($lockFile);
        $runningPIDs = explode("\n", trim( `ps -e | awk '{print $1}'` ));

        return in_array($lockedPID, $runningPIDs);
    }
}