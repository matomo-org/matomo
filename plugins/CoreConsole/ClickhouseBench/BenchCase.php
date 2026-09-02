<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use InvalidArgumentException;

/**
 * One thing to measure, on one site, over one period, with one segment.
 *
 * A case is passed to the worker process as JSON rather than as command line options: the
 * segments carry ';', '=@' and '!@', and every layer between here and the worker would need
 * its own quoting rule for them.
 */
final class BenchCase
{
    public const GROUP_API = 'api';
    public const GROUP_ARCHIVE = 'archive';

    private string $id;
    private string $group;
    private string $title;
    private int $idSite;
    private string $period;
    private string $date;
    private string $segment;
    private string $segmentLabel;

    /** API group only. */
    private string $apiMethod;

    /** @var array<string, mixed> API group only, merged over the site/period/date/segment params. */
    private array $apiParams;

    /** Archive group only. Empty means "every plugin", which is what the real archiver runs. */
    private string $plugin;

    /**
     * @param array<string, mixed> $apiParams
     */
    private function __construct(
        string $id,
        string $group,
        string $title,
        int $idSite,
        string $period,
        string $date,
        string $segment,
        string $segmentLabel,
        string $apiMethod = '',
        array $apiParams = [],
        string $plugin = ''
    ) {
        $this->id = $id;
        $this->group = $group;
        $this->title = $title;
        $this->idSite = $idSite;
        $this->period = $period;
        $this->date = $date;
        $this->segment = $segment;
        $this->segmentLabel = $segmentLabel;
        $this->apiMethod = $apiMethod;
        $this->apiParams = $apiParams;
        $this->plugin = $plugin;
    }

    /**
     * @param array<string, mixed> $apiParams
     */
    public static function api(
        string $id,
        string $title,
        int $idSite,
        string $period,
        string $date,
        string $segment,
        string $segmentLabel,
        string $apiMethod,
        array $apiParams = []
    ): self {
        return new self(
            $id,
            self::GROUP_API,
            $title,
            $idSite,
            $period,
            $date,
            $segment,
            $segmentLabel,
            $apiMethod,
            $apiParams
        );
    }

    public static function archive(
        string $id,
        string $title,
        int $idSite,
        string $period,
        string $date,
        string $segment,
        string $segmentLabel,
        string $plugin = ''
    ): self {
        return new self(
            $id,
            self::GROUP_ARCHIVE,
            $title,
            $idSite,
            $period,
            $date,
            $segment,
            $segmentLabel,
            '',
            [],
            $plugin
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIdSite(): int
    {
        return $this->idSite;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getSegment(): string
    {
        return $this->segment;
    }

    public function getSegmentLabel(): string
    {
        return $this->segmentLabel;
    }

    public function getApiMethod(): string
    {
        return $this->apiMethod;
    }

    /**
     * @return array<string, mixed>
     */
    public function getApiParams(): array
    {
        return $this->apiParams;
    }

    public function getPlugin(): string
    {
        return $this->plugin;
    }

    public function isArchive(): bool
    {
        return $this->group === self::GROUP_ARCHIVE;
    }

    /**
     * What the case actually exercises, for the run log. The segment is deliberately shown in
     * full: a timing without the segment it was measured under is not a comparable number.
     */
    public function describe(): string
    {
        $what = $this->isArchive()
            ? 'archive ' . ($this->plugin === '' ? 'all plugins' : $this->plugin)
            : $this->apiMethod;

        return sprintf(
            '%s, idSite=%d, %s=%s, segment=%s',
            $what,
            $this->idSite,
            $this->period,
            $this->date,
            $this->segment === '' ? '(none)' : $this->segment
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group' => $this->group,
            'title' => $this->title,
            'idSite' => $this->idSite,
            'period' => $this->period,
            'date' => $this->date,
            'segment' => $this->segment,
            'segmentLabel' => $this->segmentLabel,
            'apiMethod' => $this->apiMethod,
            'apiParams' => $this->apiParams,
            'plugin' => $this->plugin,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['id', 'group', 'idSite', 'period', 'date'] as $required) {
            if (!isset($data[$required])) {
                throw new InvalidArgumentException('Case data is missing "' . $required . '".');
            }
        }

        if (!in_array($data['group'], [self::GROUP_API, self::GROUP_ARCHIVE], true)) {
            throw new InvalidArgumentException('Case data has an unknown group "' . $data['group'] . '".');
        }

        return new self(
            (string) $data['id'],
            (string) $data['group'],
            (string) ($data['title'] ?? $data['id']),
            (int) $data['idSite'],
            (string) $data['period'],
            (string) $data['date'],
            (string) ($data['segment'] ?? ''),
            (string) ($data['segmentLabel'] ?? 'none'),
            (string) ($data['apiMethod'] ?? ''),
            (array) ($data['apiParams'] ?? []),
            (string) ($data['plugin'] ?? '')
        );
    }
}
