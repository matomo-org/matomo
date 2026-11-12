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
     * @param string $tsArchived UTC time the oldest of the archives was built, `YYYY-MM-DD HH:MM:SS`
     */
    public function __construct(
        private readonly array $numerics,
        private readonly array $tables,
        private readonly string $tsArchived
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

    public function getTsArchived(): string
    {
        return $this->tsArchived;
    }
}
