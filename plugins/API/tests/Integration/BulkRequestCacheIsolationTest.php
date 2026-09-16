<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\API\Request as ApiRequest;
use Piwik\Cache;
use Piwik\DI;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The sub-requests of a bulk request are logically separate requests: they must not share transient
 * (per-request) cache writes with one another, but they must still see cache entries the outer
 * request had already populated before the batch.
 *
 * @group API
 * @group Plugins
 */
class BulkRequestCacheIsolationTest extends IntegrationTestCase
{
    /**
     * What the second sub-request found under a key the first sub-request wrote.
     *
     * @var mixed
     */
    public $probeSeenBySecondSubRequest = 'unset';

    /**
     * What a sub-request found under a key the outer request wrote before the batch.
     *
     * @var mixed
     */
    public $probeSeenFromBaseline = 'unset';

    public function testBulkSubRequestsDoNotShareTransientCacheWrites()
    {
        ApiRequest::processRequest('API.getBulkRequest', [
            'urls' => [
                'method=API.getMatomoVersion',
                'method=API.getPhpVersion',
            ],
        ]);

        // false is the transient cache's "missing entry" return value.
        $this->assertFalse(
            $this->probeSeenBySecondSubRequest,
            'a later bulk sub-request saw a transient-cache entry written by an earlier one'
        );
    }

    public function testBulkSubRequestsStillSeeCacheEntriesFromBeforeTheBatch()
    {
        Cache::getTransientCache()->save('bulk.baseline.probe', 'written-before-batch');

        ApiRequest::processRequest('API.getBulkRequest', [
            'urls' => [
                'method=API.getMatomoVersion',
            ],
        ]);

        $this->assertSame(
            'written-before-batch',
            $this->probeSeenFromBaseline,
            'a bulk sub-request could not see a transient-cache entry populated before the batch'
        );
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => DI::add([
                ['API.API.getMatomoVersion.end', DI::value(function () {
                    $this->probeSeenFromBaseline = Cache::getTransientCache()->fetch('bulk.baseline.probe');
                    Cache::getTransientCache()->save('bulk.isolation.probe', 'written-by-first-subrequest');
                })],
                ['API.API.getPhpVersion.end', DI::value(function () {
                    $this->probeSeenBySecondSubRequest = Cache::getTransientCache()->fetch('bulk.isolation.probe');
                })],
            ]),
        ];
    }
}
