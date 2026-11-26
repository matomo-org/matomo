<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * Query features that decide how Matomo builds ranking and rollup queries.
 *
 * A schema implementing {@link SchemaInterface} may also implement this interface. For schemas that
 * don't, {@link Schema} reports the behaviour Matomo always had: no window functions and native
 * `GROUP BY ... WITH ROLLUP`.
 */
interface QueryCapabilitiesInterface
{
    /**
     * Returns whether {@link \Piwik\RankingQuery} must count rows with window functions instead
     * of user variables.
     */
    public function supportsWindowFunctions(): bool;

    /**
     * Returns whether `GROUP BY ... WITH ROLLUP` is available. If not, rollup queries are emulated
     * with one grouping query per level, see {@link RollupQuery}.
     */
    public function supportsGroupByWithRollup(): bool;
}
