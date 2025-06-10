<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\MatomoCopyModal;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\MatomoCopyModal\CopyRequest;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group MatomoCopyModal
 */
class CopyRequestTest extends TestCase
{
    public const TEST_ID_SITE = 1;
    public const TEST_ENTITY_TYPE = 'heatmap';
    public const TEST_ID_DESTINATION_SITES = [1, 2, 3];
    public const TEST_REQUEST_DATA = ['key' => 'value'];

    /**
     * @var CopyRequest
     */
    private $copyRequest;

    protected function setUp(): void
    {
        $this->copyRequest = new CopyRequest(
            self::TEST_ID_SITE,
            self::TEST_ENTITY_TYPE,
            self::TEST_ID_DESTINATION_SITES,
            self::TEST_REQUEST_DATA
        );
    }

    public function testConstructor()
    {
        $this->assertSame(self::TEST_ID_SITE, $this->copyRequest->getIdSite());
        $this->assertSame(self::TEST_ENTITY_TYPE, $this->copyRequest->getEntityTypeName());
        $this->assertSame(self::TEST_ID_DESTINATION_SITES, $this->copyRequest->getIdDestinationSites());
        $this->assertSame(self::TEST_REQUEST_DATA, $this->copyRequest->getRequestData());
    }

    public function testConstructorWithDefaults()
    {
        // Instantiate with only the required fields
        $this->copyRequest = new CopyRequest(
            self::TEST_ID_SITE,
            self::TEST_ENTITY_TYPE
        );

        $this->assertSame(self::TEST_ID_SITE, $this->copyRequest->getIdSite());
        $this->assertSame(self::TEST_ENTITY_TYPE, $this->copyRequest->getEntityTypeName());
        $this->assertSame([self::TEST_ID_SITE], $this->copyRequest->getIdDestinationSites(), 'Should default to an array container only idSite.');
        $this->assertSame([], $this->copyRequest->getRequestData(), 'Should default to an empty array.');
    }
}
