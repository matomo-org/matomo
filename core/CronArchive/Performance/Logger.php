<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive\Performance;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config;
use Piwik\Timer;
use Piwik\Url;
use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var int
     */
    private $isEnabled;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $archivingRunId;

    public function __construct(Config $config, LoggerInterface $logger = null)
    {
        $this->isEnabled = $config->Debug['archiving_profile'] == 1;
        $this->logger = $logger;

        $this->archivingRunId = $this->getArchivingRunId();
        if (empty($this->archivingRunId)) {
            $this->isEnabled = false;
        }
    }

    public function logMeasurement($category, $name, ArchiveProcessor\Parameters $activeArchivingParams, Timer $timer)
    {
        if (!$this->isEnabled || !$this->logger) {
            return;
        }

        $measurement = new Measurement($category, $name, $activeArchivingParams->getSite()->getId(),
            $activeArchivingParams->getPeriod()->getRangeString(), $activeArchivingParams->getPeriod()->getLabel(),
            $activeArchivingParams->getSegment()->getString(), $timer->getTime(), $timer->getMemoryLeakValue(),
            $timer->getPeakMemoryValue());

        $params = array_merge($_GET);
        unset($params['pid']);
        unset($params['runid']);

        $this->logger->info("[runid={runid},pid={pid}] {request}: {measurement}", [
            'pid' => Common::getRequestVar('pid', false),
            'runid' => $this->getArchivingRunId(),
            'request' => Url::getQueryStringFromParameters($params),
            'measurement' => $measurement,
        ]);
    }

    public static function getMeasurementsFor($runId, $childPid)
    {
        $profilingLogFile = preg_replace('/[\'"]/', '', Config::getInstance()->Debug['archive_profiling_log']);
        if (!is_readable($profilingLogFile)) {
            return [];
        }

        $runId = self::cleanId($runId);
        $childPid = self::cleanId($childPid);

        $lineIdentifier = "[runid=$runId,pid=$childPid]";
        $lines = `grep "$childPid" "$profilingLogFile"`;
        $lines = explode("\n", $lines);
        $lines = array_map(function ($line) use ($lineIdentifier) {
            $index = strpos($line, $lineIdentifier);
            if ($index === false) {
                return null;
            }
            $line = substr($line, $index + strlen($lineIdentifier));
            return trim($line);
        }, $lines);
        $lines = array_filter($lines);
        $lines = array_map(function ($line) {
            $parts = explode(":", $line, 2);
            $parts = array_map('trim', $parts);
            return $parts;
        }, $lines);

        $data = [];
        foreach ($lines as $line) {
            if (count($line) != 2) {
                continue;
            }

            list($request, $measurement) = $line;
            $data[$request][] = $measurement;
        }
        return $data;
    }

    private function getArchivingRunId()
    {
        return Common::getRequestVar('runid', false);
    }

    private static function cleanId($id)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
    }
}
