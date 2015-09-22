<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter\LogImporter;

use Piwik\Container\StaticContainer;
use Piwik\SettingsPiwik;
use Psr\Log\LoggerInterface;

class Import
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function import($logFile)
    {
        $options = StaticContainer::get('AutoLogImporter.logImportOptions');
        $options[] = '--url=' . escapeshellarg(SettingsPiwik::getPiwikUrl());
        $options[] = '--replay-tracking';
        $options[] = escapeshellarg($logFile);

        if (preg_match('/_idsite_(\d+)/', $logFile, $matches)) {
            $options[] = '--idsite=' . (int) $matches[1];
        }

        $command = PIWIK_INCLUDE_PATH . '/misc/log-analytics/import_logs.py';
        foreach ($options as $option) {
            $command .= ' ' . $option;
        }

        $command .= ' 2>&1';

        $this->logger->debug("Executing command '$command' to import '$logFile'");

        try {
            exec($command, $output, $exitCode);
            $output = implode("\n", $output);
        } catch (\Exception $e) {
            $output   = $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            $exitCode = 255;
        }

        $this->logger->debug("Finished command with exit code '$exitCode' and output '$output'");

        return new Result($command, $output, $exitCode);
    }
}
