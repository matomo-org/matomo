<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\SitesManager\API;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\Fixture;

/**
 * @group SitesManaager
 * @group APITest
 * @group Plugins
 */
class APITest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        if (!SettingsServer::isTimezoneSupportEnabled()) {
            $this->markTestSkipped('timezones needs to be supported');
        }

        Fixture::loadAllTranslations();

        $this->api = API::getInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Fixture::resetTranslations();
    }

    public function getTimezoneNameTestData()
    {
        return array(
            array('Europe/Rome', 'en', 'Italy'),
            array('Europe/Rome', 'it', 'Italia'),
            array('America/New_York', 'en', 'United States - New York'),
            array('America/New_York', 'ru', 'Соединенные Штаты - Нью-Йорк'),
            array('Asia/Foo_Bar', 'en', 'Foo Bar'),
            array('Etc/UTC', 'en', 'UTC'),
            array('UTC', 'en', 'UTC'),
            array('UTC+1', 'en', 'UTC+1'),
            array('UTC+1.5', 'en', 'UTC+1:30'),
            array('UTC-1.5', 'en', 'UTC-1:30'),
        );
    }

    /**
     * @dataProvider getTimezoneNameTestData
     */
    public function testGetTimezoneName($timezone, $language, $expected)
    {
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        $translator->setCurrentLanguage($language);

        $name = $this->api->getTimezoneName($timezone);
        $this->assertEquals($expected, $name);
    }

    public function testGetTimezonesList()
    {
        $timezones = $this->api->getTimezonesList();

        $this->assertArrayHasKey('Asia', $timezones);
        $this->assertArrayHasKey('North America', $timezones);
        $this->assertArrayHasKey('Central America', $timezones);
        $this->assertArrayHasKey('UTC', $timezones);

        $this->assertEquals('Japan', $timezones['Asia']['Asia/Tokyo']);
        $this->assertEquals('United States - New York', $timezones['North America']['America/New_York']);
        $this->assertEquals('Antarctica - Dumont d’Urville', $timezones['Antarctica']['Antarctica/DumontDUrville']);

        $this->assertArrayHasKey('UTC', $timezones['UTC']);
        $this->assertArrayHasKey('UTC+6', $timezones['UTC']);
        $this->assertArrayHasKey('UTC+13.75', $timezones['UTC']);
        $this->assertArrayHasKey('UTC-11.5', $timezones['UTC']);
    }
}
