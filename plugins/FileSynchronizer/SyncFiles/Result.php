<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer\SyncFiles;

use Piwik\Db;

class Result
{
    private $command;
    private $output;
    private $exitCode;

    public function __construct($command, $output, $exitCode)
    {
        $this->command = $command;
        $this->output = $output;
        $this->exitCode = $exitCode;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
