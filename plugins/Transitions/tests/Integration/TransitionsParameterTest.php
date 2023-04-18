<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\Transitions\API;

/**
 * Tests the transitions plugin parameters for various methods
 *
 * @group TransitionsParameterTest
 * @group Transitions
 * @group Plugins
 */
class TransitionsParameterTest extends IntegrationTestCase
{

    public $api;

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2010-02-03 00:00:00');
        $this->api = API::getInstance();

        $t = Fixture::getTracker(1, '2012-08-09 01:02:03', $defaultInit = true, $useLocalTracker = false);

        $t->setUrl('http://example.org/page/one.html');
        $t->doTrackPageView('incredible title');
    }

    public function test_ShouldPass_IfActionNameUrlIsString()
    {
        $r = $this->api->getTransitionsForAction('http://example.org/page/one.html', 'url', 1, 'day', '2012-08-09');
        self::assertEquals(1, $r['pageMetrics']['pageviews']);
    }

    public function test_ShouldPass_IfActionNameUrlIsArraySingleElement()
    {
        $r = $this->api->getTransitionsForAction(['http://example.org/page/one.html'], 'url', 1, 'day', '2012-08-09');
        self::assertEquals(1, $r['pageMetrics']['pageviews']);
    }

    public function test_ShouldThrowException_IfActionNameUrlIsArrayEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('NoDataForAction');
        $this->api->getTransitionsForAction([], 'url', 1,'day', '2012-08-09');
    }

    public function test_ShouldThrowException_IfActionNameUrlIsArrayMultipleElements()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('NoDataForAction');
        $this->api->getTransitionsForAction(['http://example.org/page/one.html', 'http://example.org/page/two.html'],
            'url', 1, 'day', '2012-08-09');
    }

    public function test_ShouldPass_IfActionNameTitleIsString()
    {
        $r = $this->api->getTransitionsForAction('incredible title', 'title', 1, 'day', '2012-08-09');
        self::assertEquals(1, $r['pageMetrics']['pageviews']);
    }

    public function test_ShouldPass_IfActionNameTitleIsArraySingleElement()
    {
        $r = $this->api->getTransitionsForAction(['incredible title'], 'title', 1, 'day', '2012-08-09');
        self::assertEquals(1, $r['pageMetrics']['pageviews']);
    }

    public function test_ShouldThrowException_IfActionNameTitleIsArrayEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('NoDataForAction');
        $this->api->getTransitionsForAction([], 'title', 1,'day', '2012-08-09');
    }

    public function test_ShouldThrowException_IfActionNameTitleIsArrayMultipleElements()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('NoDataForAction');
        $this->api->getTransitionsForAction(['incredible title', 'another incredible title'],
            'title', 1, 'day', '2012-08-09');
    }


}
