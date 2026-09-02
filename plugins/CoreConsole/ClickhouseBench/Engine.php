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
use Piwik\Db;
use RuntimeException;

/**
 * One side of the A/B run.
 *
 * The two legs differ by exactly one environment variable. MATOMO_ANALYTICS_DB_DISABLED is
 * the only analytics override {@see Db::getAnalyticsDatabaseConfig()} honours outside test
 * mode, and it can only ever turn the analytics database OFF - so one deployed config serves
 * both legs, and neither leg is produced by hand-editing connection details between two timed
 * runs and hoping the edit was put back.
 */
final class Engine
{
    public const MYSQL = 'mysql';
    public const CLICKHOUSE = 'clickhouse';

    private const ALIASES = [
        'aurora' => self::MYSQL,
        'mariadb' => self::MYSQL,
        'ch' => self::CLICKHOUSE,
    ];

    private string $key;

    private function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function fromKey(string $key): self
    {
        $key = strtolower(trim($key));
        $key = self::ALIASES[$key] ?? $key;

        if (!in_array($key, [self::MYSQL, self::CLICKHOUSE], true)) {
            throw new InvalidArgumentException(
                'Unknown engine "' . $key . '". Expected one of: ' . self::MYSQL . ', ' . self::CLICKHOUSE . '.'
            );
        }

        return new self($key);
    }

    /**
     * @param string $list comma separated engine keys, empty for both
     * @return self[]
     */
    public static function fromList(string $list): array
    {
        $keys = array_filter(
            array_map('trim', explode(',', $list)),
            static fn(string $key): bool => $key !== ''
        );
        if (empty($keys)) {
            return self::all();
        }

        $engines = [];
        foreach ($keys as $key) {
            $engine = self::fromKey($key);
            $engines[$engine->getKey()] = $engine;
        }

        return array_values($engines);
    }

    /**
     * @return self[]
     */
    public static function all(): array
    {
        return [new self(self::MYSQL), new self(self::CLICKHOUSE)];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->key === self::CLICKHOUSE ? 'ClickHouse' : 'MySQL';
    }

    public function isClickhouse(): bool
    {
        return $this->key === self::CLICKHOUSE;
    }

    /**
     * The environment a worker process needs to run this leg. Both values are set explicitly:
     * an ambient MATOMO_ANALYTICS_DB_DISABLED exported into the shell must not decide which
     * leg a run labelled "clickhouse" actually measured.
     *
     * @return array<string, string>
     */
    public function getChildEnvironment(): array
    {
        return ['MATOMO_ANALYTICS_DB_DISABLED' => $this->isClickhouse() ? '0' : '1'];
    }

    /**
     * Fails when this process is not routing log queries where the label claims.
     *
     * Every number this harness prints is attributed to an engine by its label, so the label
     * has to be checked rather than trusted. A ClickHouse leg that quietly ran on MySQL would
     * publish as a ClickHouse result and there would be nothing in the output to show it.
     *
     * @throws RuntimeException
     */
    public function assertActive(): void
    {
        $routesToAnalytics = Db::hasAnalyticsConfigured();
        if ($routesToAnalytics === $this->isClickhouse()) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Asked to measure the %s leg, but this process routes log queries to %s.'
            . ' Db::hasAnalyticsConfigured() is %s. Check [database_analytics] enabled/host, and'
            . ' that MATOMO_ANALYTICS_DB_DISABLED is not exported in the calling shell.',
            $this->getLabel(),
            $routesToAnalytics ? 'the analytics database' : 'MySQL',
            $routesToAnalytics ? 'true' : 'false'
        ));
    }
}
