<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\EntityDuplicator;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\EntityDuplicator\DuplicateRequest;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group EntityDuplicator
 */
class DuplicateRequestTest extends TestCase
{
    public const TEST_ID_SITE = 1;
    public const TEST_ENTITY_TYPE = 'heatmap';
    public const TEST_ID_DESTINATION_SITES = [1, 2, 3];
    public const TEST_REQUEST_DATA = ['key' => 'value'];

    /**
     * @var DuplicateRequest
     */
    private $duplicateRequest;

    protected function setUp(): void
    {
        $this->duplicateRequest = new DuplicateRequest(
            self::TEST_ID_SITE,
            self::TEST_ENTITY_TYPE,
            self::TEST_ID_DESTINATION_SITES,
            self::TEST_REQUEST_DATA
        );
    }

    public function testConstructor()
    {
        $this->assertSame(self::TEST_ID_SITE, $this->duplicateRequest->getIdSite());
        $this->assertSame(self::TEST_ENTITY_TYPE, $this->duplicateRequest->getEntityTypeName());
        $this->assertSame(self::TEST_ID_DESTINATION_SITES, $this->duplicateRequest->getIdDestinationSites());
        $this->assertSame(self::TEST_REQUEST_DATA, $this->duplicateRequest->getRequestData());
    }

    public function testConstructorWithDefaults()
    {
        // Instantiate with only the required fields
        $this->duplicateRequest = new DuplicateRequest(
            self::TEST_ID_SITE,
            self::TEST_ENTITY_TYPE
        );

        $this->assertSame(self::TEST_ID_SITE, $this->duplicateRequest->getIdSite());
        $this->assertSame(self::TEST_ENTITY_TYPE, $this->duplicateRequest->getEntityTypeName());
        $this->assertSame([self::TEST_ID_SITE], $this->duplicateRequest->getIdDestinationSites(), 'Should default to an array container only idSite.');
        $this->assertSame([], $this->duplicateRequest->getRequestData(), 'Should default to an empty array.');
    }
}
