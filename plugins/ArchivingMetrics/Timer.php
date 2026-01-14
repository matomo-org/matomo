<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Plugins\ArchivingMetrics\Clock\Clock;
use Piwik\Plugins\ArchivingMetrics\Clock\ClockInterface;
use Piwik\Plugins\ArchivingMetrics\Writer\DbWriter;
use Piwik\Plugins\ArchivingMetrics\Writer\WriterInterface;

final class Timer
{
    /**
     * @var bool
     */
    private $isArchivePhpTriggered;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var array<string, array<string, mixed>>
     */
    private $runs = [];

    /**
     * @var ?Timer
     */
    private static $instance;

    public function __construct(bool $isArchivePhpTriggered, ClockInterface $clock, WriterInterface $writer)
    {
        $this->isArchivePhpTriggered = $isArchivePhpTriggered;
        $this->clock = $clock;
        $this->writer = $writer;
    }

    public static function getInstance(bool $isArchivePhpTriggered, ?ClockInterface $clock = null, ?WriterInterface $writer = null): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if ($clock === null) {
            $clock = new Clock();
        }

        if ($writer === null) {
            $writer = new DbWriter();
        }

        self::$instance = new self($isArchivePhpTriggered, $clock, $writer);
        return self::$instance;
    }

    /**
     * @internal For tests only.
     */
    public static function resetInstanceForTests(): void
    {
        self::$instance = null;
    }

    public function start(Context $context): void
    {
        if (false === $this->isApplicableForTiming($context)) {
            return;
        }

        $this->runs[$context->getKey()] = [
            'context' => $context,
            'timeStarted' => $this->clock->microtime(),
        ];
    }

    public function complete(Context $context, array $idArchives, bool $wasCached): void
    {
        if (false === $this->isApplicableForTiming($context)) {
            return;
        }

        if (true === $wasCached) {
            return;
        }

        if (empty($idArchives) || count($idArchives) !== 1) {
            return;
        }

        $idArchive = reset($idArchives);
        $key = $context->getKey();

        if (!isset($this->runs[$key]['timeStarted'])) {
            return;
        }

        $finishedAt = $this->clock->microtime();
        $totalTimeMs = ($finishedAt - $this->runs[$key]['timeStarted']);

        $this->runs[$key]['ts_started'] = date('Y-m-d H:i:s', (int) $this->runs[$key]['timeStarted']);
        $this->runs[$key]['totalTime'] = $totalTimeMs;
        $this->runs[$key]['timeFinished'] = $finishedAt;
        $this->runs[$key]['ts_finished'] = date('Y-m-d H:i:s', (int) $this->runs[$key]['timeFinished']);

        $exclusiveTimeMs = $this->calculateExclusiveTime($key);
        $this->runs[$key]['exclusiveTime'] = $exclusiveTimeMs;

        /** @var Context $storedContext */
        $storedContext = $this->runs[$key]['context'];
        $archiveName = Rules::getDoneStringFlagFor(
            [$storedContext->idSite],
            $storedContext->segment,
            $storedContext->period->getLabel(),
            $storedContext->plugin
        );
        $this->writer->write($storedContext, [
            'idarchive' => $idArchive,
            'archive_name' => $archiveName,
            'ts_started' => $this->runs[$key]['ts_started'],
            'ts_finished' => $this->runs[$key]['ts_finished'],
            'total_time' => (int) round($totalTimeMs * 1000),
            'total_time_exclusive' => (int) round($exclusiveTimeMs * 1000),
        ]);
    }

    private function isApplicableForTiming(Context $context): bool
    {
        if (false === $this->isArchivePhpTriggered) {
            return false;
        }

        $doneFlag = Rules::getDoneStringFlagFor(
            [$context->idSite],
            $context->segment,
            $context->period->getLabel(),
            $context->plugin
        );
        if (strpos($doneFlag, '.') !== false) {
            return false;
        }

        return true;
    }

    private function calculateExclusiveTime(string $currentKey): float
    {
        if (empty($this->runs[$currentKey])) {
            return 0.0;
        }

        $current = $this->runs[$currentKey];

        $totalTimeMs = $current['totalTime'];

        // If the key is last in the array then it's probably not a nested archive so this calculation doesn't matter
        if ($currentKey === array_key_last($this->runs)) {
            return $totalTimeMs;
        }

        $childTotalMs = 0.0;

        foreach ($this->runs as $otherKey => $run) {
            if ($otherKey === $currentKey) {
                continue;
            }
            if (empty($run['exclusiveTime'])) {
                continue;
            }

            $childFinished = $run['timeFinished'] ?? null;
            $childStarted = $run['timeStarted'] ?? null;
            if ($childFinished === null || $childStarted === null) {
                continue;
            }

            if ($childFinished <= $current['timeFinished'] && $childStarted >= $current['timeStarted']) {
                $childTotalMs += $run['exclusiveTime'];
            }
        }

        $exclusive = $totalTimeMs - $childTotalMs;
        if ($exclusive < 0) {
            return $totalTimeMs;
        }

        return $exclusive;
    }
}
