<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Marketplace
 * @group MarketplaceTest
 * @group Plugins
 */
class MarketplaceTest extends IntegrationTestCase
{
    private $cacheKey = 'Marketplace_ExpiredPlugins';

    public function testCheckForUpdatesClearsTheInvalidLicensesCache(): void
    {
        Cache::getEagerCache()->save($this->cacheKey, ['exceeded' => ['FooPlugin']]);

        (new Marketplace())->checkForUpdates();

        self::assertFalse(Cache::getEagerCache()->contains($this->cacheKey));
    }
}
