<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ImageGraph\tests\Integration;

use Exception;
use Piwik\API\Request;
use Piwik\Plugins\ImageGraph\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionProperty;

/**
 * @group ImageGraph
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
    }

    public function tearDown(): void
    {
        $this->setNestedApiInvocationCount(0);

        parent::tearDown();
    }

    public function testGetRefusesStreamingOutputInsideNestedApiRequest()
    {
        $this->setNestedApiInvocationCount(2);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A graph can only be sent to the browser by the top-level request.');

        API::getInstance()->get(1, 'day', 'today', 'VisitsSummary', 'get');
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    private function setNestedApiInvocationCount(int $count): void
    {
        $reflectionProperty = new ReflectionProperty(Request::class, 'nestedApiInvocationCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, $count);
    }
}
