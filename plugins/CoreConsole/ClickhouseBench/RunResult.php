<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

/**
 * One measured run: one case, one engine, one iteration.
 *
 * Two timings are kept rather than one. wallMs is the child process end to end, which is what
 * an operator waits for and includes bootstrap. archiveMs comes from ArchivingMetrics and is
 * the archive build alone. They answer different questions and the difference between them is
 * itself informative, so neither is derived from the other.
 */
final class RunResult
{
    private string $engineKey;
    private BenchCase $case;
    private int $iteration;
    private bool $isWarmup;
    private bool $ok;
    private string $error;
    private float $wallMs;
    private ?float $archiveMs;
    private ?float $archiveExclusiveMs;
    private int $archiveCount;
    private int $otherArchiveCount;

    /** @var array{strength: string, rows: ?int, digest: string, summary: string}|null */
    private ?array $fingerprint;

    /** @var string[] the console commands this run actually executed, in order */
    private array $commands;

    /**
     * @param array{strength: string, rows: ?int, digest: string, summary: string}|null $fingerprint
     * @param string[] $commands
     */
    public function __construct(
        string $engineKey,
        BenchCase $case,
        int $iteration,
        bool $isWarmup,
        bool $ok,
        float $wallMs,
        ?array $fingerprint = null,
        ?float $archiveMs = null,
        ?float $archiveExclusiveMs = null,
        int $archiveCount = 0,
        int $otherArchiveCount = 0,
        string $error = '',
        array $commands = []
    ) {
        $this->engineKey = $engineKey;
        $this->case = $case;
        $this->iteration = $iteration;
        $this->isWarmup = $isWarmup;
        $this->ok = $ok;
        $this->wallMs = $wallMs;
        $this->fingerprint = $fingerprint;
        $this->archiveMs = $archiveMs;
        $this->archiveExclusiveMs = $archiveExclusiveMs;
        $this->archiveCount = $archiveCount;
        $this->otherArchiveCount = $otherArchiveCount;
        $this->error = $error;
        $this->commands = $commands;
    }

    public function getEngineKey(): string
    {
        return $this->engineKey;
    }

    public function getCase(): BenchCase
    {
        return $this->case;
    }

    public function getCaseId(): string
    {
        return $this->case->getId();
    }

    public function getIteration(): int
    {
        return $this->iteration;
    }

    public function isWarmup(): bool
    {
        return $this->isWarmup;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getWallMs(): float
    {
        return $this->wallMs;
    }

    public function getArchiveMs(): ?float
    {
        return $this->archiveMs;
    }

    public function getArchiveExclusiveMs(): ?float
    {
        return $this->archiveExclusiveMs;
    }

    /**
     * The number to compare between engines: the archive build when ArchivingMetrics recorded
     * one, otherwise the wall clock. Which of the two it is, is reported alongside - a wall
     * clock and an archive time are not interchangeable and a table that silently mixes them
     * would understate ClickHouse or MySQL depending on which rows fell back.
     */
    public function getComparableMs(): float
    {
        return $this->archiveMs ?? $this->wallMs;
    }

    public function hasArchiveMetrics(): bool
    {
        return $this->archiveMs !== null;
    }

    public function getArchiveCount(): int
    {
        return $this->archiveCount;
    }

    public function getOtherArchiveCount(): int
    {
        return $this->otherArchiveCount;
    }

    /**
     * @return array{strength: string, rows: ?int, digest: string, summary: string}|null
     */
    public function getFingerprint(): ?array
    {
        return $this->fingerprint;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engine' => $this->engineKey,
            'case' => $this->case->getId(),
            'group' => $this->case->getGroup(),
            'segment' => $this->case->getSegment(),
            'segmentLabel' => $this->case->getSegmentLabel(),
            'iteration' => $this->iteration,
            'warmup' => $this->isWarmup,
            'ok' => $this->ok,
            'error' => $this->error,
            'wallMs' => round($this->wallMs, 1),
            'archiveMs' => $this->archiveMs === null ? null : round($this->archiveMs, 1),
            'archiveExclusiveMs' => $this->archiveExclusiveMs === null
                ? null
                : round($this->archiveExclusiveMs, 1),
            'archivesBuilt' => $this->archiveCount,
            'otherArchivesBuilt' => $this->otherArchiveCount,
            'fingerprint' => $this->fingerprint,
            'commands' => $this->commands,
        ];
    }
}
