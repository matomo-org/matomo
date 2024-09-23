<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Framework\Assert;

class ProcessedMetricAssert
{
    public function assertProcessedMetricFormulasAreValid(string $pluginName): void
    {
        // TODO
        /*
        - check plugin is loaded
        - get list of processed metrics for the plugin (check that the list is not empty)
        - for each:
        -   * check that the formula parses
        -   * create a fake datatable row with random values for columns
        -   * check that the compute function matches the result of evaluating the formula
        */
    }
}
