<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Db\Schema;
use Piwik\Db\Schema\Mariadb;
use Piwik\Db\Schema\Mysql;

/**
 * Runs code with the configured MySQL or MariaDB schema reporting other query capabilities, so the
 * window-function and emulated-rollup paths can be compared with the native paths on these engines.
 */
final class ForcedQueryCapabilities
{
    /**
     * @return mixed The callback's return value.
     */
    public static function run(bool $windowFunctions, bool $groupByWithRollup, callable $callback)
    {
        $property = new \ReflectionProperty(Schema::class, 'schema');
        $facade = Schema::getInstance();
        $facade->supportsSortingInSubquery(); // loads the configured schema
        $previous = $property->getValue($facade);

        $schema = $previous instanceof Mariadb ? new class extends Mariadb {
            use ForcedQueryCapabilitiesTrait;
        } : new class extends Mysql {
            use ForcedQueryCapabilitiesTrait;
        };
        $schema->forcedWindowFunctions = $windowFunctions;
        $schema->forcedGroupByWithRollup = $groupByWithRollup;

        $property->setValue($facade, $schema);

        try {
            return $callback();
        } finally {
            $property->setValue($facade, $previous);
        }
    }
}
