<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CliMulti;

use Piwik\SettingsPiwik;

class RequestParser
{
    private $supportsAsync;

    public function __construct($supportsAsync)
    {
        $this->supportsAsync = $supportsAsync;
    }

    public function getInProgressCommands()
    {
        $psOutput = $this->getPsOutput();

        $climultiRequestCommands = $this->getPsLinesWithCliMulti($psOutput);
        $climultiRequestCommands = $this->parseQueries($climultiRequestCommands);

        return $climultiRequestCommands;
    }

    public function getInProgressArchivingCommands()
    {
        $commands = $this->getInProgressCommands();
        $commands = $this->filterNonArchivingJobs($commands);
        return $commands;
    }

    private function getPsOutput() // protected for tests
    {
        if (!$this->supportsAsync) {
            // we cannot detect if web archive is still running
            return '';
        }

        return $this->invokePs();
    }

    private function filterNonArchivingJobs($commands)
    {
        $result = array_filter($commands, function ($command) {
            if (
                empty($command['trigger'])
                || $command['trigger'] != 'archivephp'
            ) {
                return false;
            }

            return true;
        });
        $result = array_values($result);
        return $result;
    }

    private function getPsLinesWithCliMulti(string $psOutput)
    {
        $instanceId = SettingsPiwik::getPiwikInstanceId();
        $lines = explode("\n", $psOutput);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, function ($line) use ($instanceId) {
            if (!empty($instanceId) && strpos($line, 'matomo-domain=' . $instanceId) === false) {
                return false;
            }
            return strpos($line, 'climulti:request') !== false
                && (
                    strpos($line, 'console') !== false || strpos($line, 'php') !== false
                );
        });
        return $lines;
    }

    private function parseQueries(array $climultiRequestCommands)
    {
        $commandName = 'climulti:request';

        $result = [];
        foreach ($climultiRequestCommands as $command) {
            $pos = strpos($command, $commandName);

            $commandParts = substr($command, $pos + strlen($commandName));
            $commandParts = explode(" ", $commandParts);
            $commandParts = array_filter($commandParts, function ($p) {
                return strlen($p) && substr($p, 0, 1) != '-';
            });

            $query = reset($commandParts);
            parse_str($query, $parsed);

            $result[] = $parsed;
        }
        return $result;
    }

    protected function invokePs()
    {
        if (defined('PIWIK_TEST_MODE')) {
            return ''; // skip check in tests as it might result in random failures
        }

        return `ps wwaux`;
    }
}
