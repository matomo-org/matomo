<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Cache;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Cache
 */
class CacheTest extends IntegrationTestCase
{
    public function test_getEagerCache_shouldPersistOnceEventWasTriggered()
    {
        $storageId = 'eagercache-test-ui';
        $cache = Cache::getEagerCache();
        $cache->save('test', 'mycontent'); // make sure something was changed, otherwise it won't save anything

        /** @var Cache\Backend $backend */
        $backend = StaticContainer::get('Matomo\Cache\Backend');
        $this->assertFalse($backend->doContains($storageId));

        $result = '';
        $module = 'CoreHome';
        $action = 'index';
        $params = array();
        Piwik::postEvent('Request.dispatch.end', array(&$result, $module, $action, $params)); // should trigger save

        $this->assertTrue($backend->doContains($storageId));
    }
}
