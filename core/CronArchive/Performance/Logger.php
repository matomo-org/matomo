<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CronArchive\Performance;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config;
use Piwik\Option;
use Piwik\Timer;
use Piwik\Url;
use Psr\Log\LoggerInterface;

// TODO: need scheduled task to clean up old ones? maybe cronarchive should delete them
class Logger
{
    /**
     * @var int
     */
    private $isEnabled;

    /**
     * @var array[]
     */
    private $measurements = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $archivingRunId;

    public function __construct(Config $config, LoggerInterface $logger)
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
        if (!$this->isEnabled) {
            return;
        }

        $measurement = new Measurement($category, $name, $activeArchivingParams->getSite()->getId(),
            $activeArchivingParams->getPeriod()->getRangeString(), $activeArchivingParams->getPeriod()->getLabel(),
            $activeArchivingParams->getSegment()->getString(), $timer->getTime(), $timer->getMemoryLeakValue());

        $this->measurements[] = $measurement;

        $this->logger->info($measurement);
    }

    public function flush()
    {
        if (!$this->isEnabled) {
            return;
        }

        $optionName = $this->getOptionName();

        $existing = Option::get($optionName);
        $originalMeasurements = isset($existing['measurements']) ? $existing['measurements'] : [];

        $serialized = json_encode([
            'request' => Url::getCurrentQueryString(),
            'measurements' => array_merge($originalMeasurements, $this->measurements),
        ]);

        Option::set($optionName, $serialized);
    }

    public static function getMeasurementsFor($archivingRunId, $pid)
    {
        $data = Option::get($archivingRunId . '_' . $pid);
        if (empty($data)) {
            return null;
        }

        $data = json_decode($data, $isAssoc = true);
        $data['measurements'] = array_map(function ($m) { return Measurement::fromArray($m); }, $data['measurements']);
        return $data;
    }

    private function getOptionName()
    {
        return $this->archivingRunId . '_' . Common::getRequestVar('pid', false);
    }

    private function getArchivingRunId()
    {
        return Common::getRequestVar('runid', false);
    }
}
