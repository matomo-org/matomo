<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit;

use Piwik\Plugins\SitesManager\Controller;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Site;
use ReflectionClass;
use Piwik\Http;

/**
 * @group SitesManaager
 * @group APITest
 * @group Plugins
 */
class GuessSiteTypeAndGtmTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Controller
     */
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_guess_site_type()
    {

        // $mock = $this->getMockBuilder(Controller::class)
        //      ->disableOriginalConstructor()
        //      ->getMock();

        // $mockHttp = $this->getMockBuilder(Http::class)
        //     ->onlyMethods(['sendHttpRequest'])
        //     ->getMock();

        // $mockHttp->method('sendHttpRequest')->willReturn('abc');
        // $reflection = new ReflectionClass($mock);
        // $reflectionProperty = $reflection->getProperty('site');
        // $reflectionProperty->setAccessible(true);
        // $site = $this->getMockBuilder(Site::class)
        //     ->disableOriginalConstructor()
        //     ->onlyMethods(['getMainUrl'])
        //     ->getMock();
        // $site->method('getMainUrl')->willReturn('https://test.test');

        // $reflectionProperty->setValue($mock, $site);

        $c = new Controller;
        $reflection = new ReflectionClass($c);
        $reflectionProperty = $reflection->getProperty('site');
        $reflectionProperty->setAccessible(true);
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()->onlyMethods(['getMainUrl'])->getMock();
        $site->method('getMainUrl')->willReturn('http://test.test');

        $reflectionProperty->setValue($c, $site);
        $a = $c->guessSiteTypeAndGtm();

        $this->assertTrue(true);
    }
}
