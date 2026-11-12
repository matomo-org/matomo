<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\DataTable;

/**
 * The stored records of a pre-processed page for a request, summed over its periods. See {@link Records}.
 */
final class StoredRecords
{
    /**
     * @param array<string, int> $numerics by part, see {@link Records::NUMERICS}
     * @param array<string, DataTable> $tables by part, the lists and the referrers that were read
     * @param array<string, string> $tsArchivedByPeriod by period range string, the UTC time (`YYYY-MM-DD HH:MM:SS`)
     *                                                  the oldest archive of the period's records was built
     */
    public function __construct(
        private readonly array $numerics,
        private readonly array $tables,
        private readonly array $tsArchivedByPeriod
    ) {
    }

    public function getNumeric(string $part): int
    {
        return $this->numerics[$part] ?? 0;
    }

    public function getTable(string $part): DataTable
    {
        return $this->tables[$part] ?? new DataTable();
    }

    /**
     * The UTC time the oldest of the archives was built, `YYYY-MM-DD HH:MM:SS`.
     */
    public function getTsArchived(): string
    {
        return empty($this->tsArchivedByPeriod) ? '' : min($this->tsArchivedByPeriod);
    }

    /**
     * @return array<string, string>
     */
    public function getTsArchivedByPeriod(): array
    {
        return $this->tsArchivedByPeriod;
    }
}
