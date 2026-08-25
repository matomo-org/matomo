<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Everything the commands need to talk about one run: its profile, its queue and its spool.
 *
 * The profile is rebuilt from the JSON stored on the run row rather than from the command line,
 * so a worker joining a run hours later - possibly on another machine, possibly after the
 * defaults in the code have moved on - generates exactly what the plan phase assumed.
 */
class RunContext
{
    private int $idRun;
    private Profile $profile;
    private ChunkQueue $queue;
    private string $spoolDir;
    private array $row;

    private function __construct(int $idRun, Profile $profile, string $spoolDir, array $row)
    {
        $this->idRun = $idRun;
        $this->profile = $profile;
        $this->spoolDir = $spoolDir;
        $this->row = $row;
        $this->queue = new ChunkQueue($idRun);
    }

    /**
     * @param int|null $idRun null means "the most recent run"
     */
    public static function load(?int $idRun): self
    {
        if (null === $idRun) {
            $idRun = ChunkQueue::getLatestRunId();

            if (null === $idRun) {
                throw new \RuntimeException(
                    'No perfcorpus run found. Start one with perfcorpus:generate.'
                );
            }
        }

        $row = ChunkQueue::getRun($idRun);

        if (null === $row) {
            throw new \RuntimeException(sprintf('perfcorpus run %d does not exist.', $idRun));
        }

        $config = json_decode($row['config'], true);

        if (!is_array($config)) {
            throw new \RuntimeException(sprintf('perfcorpus run %d has an unreadable config.', $idRun));
        }

        return new self($idRun, Profile::fromArray($config), $row['spool_dir'], $row);
    }

    public static function fromParts(int $idRun, Profile $profile, string $spoolDir, array $row): self
    {
        return new self($idRun, $profile, $spoolDir, $row);
    }

    public function getRunId(): int
    {
        return $this->idRun;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function getQueue(): ChunkQueue
    {
        return $this->queue;
    }

    public function getSpoolDir(): string
    {
        return $this->spoolDir;
    }

    public function getStatus(): int
    {
        return (int) $this->row['status'];
    }

    public function getCreatedAt(): string
    {
        return $this->row['ts_created'];
    }

    public function getGitCommit(): ?string
    {
        return $this->row['git_commit'] ?: null;
    }

    /**
     * The first idaction the corpus owns. Stored on the run so every worker rebuilds exactly the
     * same dictionary layout, whenever it joins and wherever it runs.
     */
    public function getIdActionBase(): ?int
    {
        if (!isset($this->row['idaction_base']) || null === $this->row['idaction_base']) {
            // The row was read when this process started; another process - or an earlier phase
            // of this one - may have written the base since.
            $this->refresh();
        }

        return isset($this->row['idaction_base']) && null !== $this->row['idaction_base']
            ? (int) $this->row['idaction_base']
            : null;
    }

    /**
     * Re-reads the run row. Anything cached from it can go stale while other processes work on
     * the same run.
     */
    public function refresh(): void
    {
        $row = ChunkQueue::getRun($this->idRun);

        if (null !== $row) {
            $this->row = $row;
        }
    }

    public function buildDictionary(): ActionDictionary
    {
        $base = $this->getIdActionBase();

        if (null === $base) {
            throw new \RuntimeException(
                'This run has no action dictionary yet. Run the plan phase first.'
            );
        }

        return new ActionDictionary($this->profile, $base);
    }

    /**
     * Directory holding one day's plan files: <spool>/plan/YYYY-MM-DD/
     */
    public function getPlanDayDir(string $date): string
    {
        return $this->spoolDir . '/plan/' . $date;
    }

    /**
     * One shard's plan file for one day. Written as .part and renamed once the whole shard is
     * finished, so a half-written shard is never mistaken for a complete one.
     */
    public function getPlanFile(string $date, int $shard): string
    {
        return sprintf('%s/shard-%04d.bin', $this->getPlanDayDir($date), $shard);
    }
}
