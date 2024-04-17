<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\GeoIp2\tests\System;

use Piwik\Option;
use Piwik\Plugins\GeoIp2\Commands\ConvertRegionCodesToIso;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group UserCountry
 * @group ConvertRegionCodesToIso
 */
class ConvertRegionCodesToIsoTest extends IntegrationTestCase
{
    /**
     * @var Fixture
     */
    public static $fixture = null;

    protected static $idSite;

    public function setUp(): void
    {
        parent::setUp();

        self::$idSite = Fixture::createWebsite('2016-01-01');
        Fixture::createSuperUser(true);

        LocationProvider::$providers = null;
        LocationProvider::setCurrentProvider('geoip2php');
    }

    protected static function trackVisit($country, $region)
    {
        $t = Fixture::getTracker(self::$idSite, '2017-05-05 12:36:00', $defaultInit = true);
        $t->setForceNewVisit();
        $t->setVisitorId('fed33392d3a48ab2');
        $t->setForceVisitDateTime('2017-05-10 12:36:00');
        $t->setTokenAuth(Fixture::getTokenAuth());
        $t->setIp(rand(1, 256) . '.' . rand(1, 256) . '.' . rand(1, 256) . '.' . rand(1, 256));
        $t->setUserId('userid.email@example.org');
        $t->setCountry($country);
        $t->setRegion($region);
        $t->setCity('any city');
        $t->setLatitude(1);
        $t->setLongitude(2);
        $t->setUrl("http://piwik.net/grue/lair");
        $t->setUrlReferrer('http://google.com/?q=Wikileaks FTW');
        $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3 (.NET CLR 3.5.30729)");
        Fixture::checkResponse($t->doTrackPageView('It\'s pitch black...'));
    }

    protected static function trackVisitAfterSwitch($country, $region)
    {
        $t = Fixture::getTracker(self::$idSite, '2017-05-15 12:36:00', $defaultInit = true);
        $t->setForceNewVisit();
        $t->setVisitorId('fed33392d3a48ab2');
        $t->setForceVisitDateTime('2017-05-15 12:36:00');
        $t->setTokenAuth(Fixture::getTokenAuth());
        $t->setIp(rand(1, 256) . '.' . rand(1, 256) . '.' . rand(1, 256) . '.' . rand(1, 256));
        $t->setUserId('userid.email@example.org');
        $t->setCountry($country);
        $t->setRegion($region);
        $t->setCity('any city');
        $t->setLatitude(1);
        $t->setLongitude(2);
        $t->setUrl("http://piwik.net/grue/lair");
        $t->setUrlReferrer('http://google.com/?q=Wikileaks FTW');
        $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3 (.NET CLR 3.5.30729)");
        Fixture::checkResponse($t->doTrackPageView('It\'s pitch black...'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Option::delete(ConvertRegionCodesToIso::OPTION_NAME);
    }

    public function testExecute_AlreadyConverted()
    {
        Option::set(ConvertRegionCodesToIso::OPTION_NAME, true);

        $result = $this->executeCommand();

        $this->assertRegExp('/Converting region codes already done/', $result);
    }

    public function testExecute_ShouldConvertRegionCodes()
    {
        Option::set(GeoIp2::SWITCH_TO_ISO_REGIONS_OPTION_NAME, mktime(0, 0, 0, 5, 12, 2017));

        self::trackVisit('gr', '14'); // should become A
        self::trackVisit('ir', '03'); // should become 08
        self::trackVisit('ir', '15'); // should become 10
        self::trackVisit('ir', '10'); // should become 05
        self::trackVisit('ad', '05'); // should not change
        self::trackVisit('bm', '04'); // should become empty, as not mappable
        self::trackVisit('gb', 'C5'); // should become empty, as not mappable
        self::trackVisit('jm', '10'); // should become 14
        self::trackVisit('ti', '1'); // should become cn / xz
        self::trackVisit('eu', ''); // should become `unknown` as country code is invalid

        self::trackVisitAfterSwitch('jm', '10');

        $result = $this->executeCommand();

        self::assertStringContainsString('All region codes converted', $result);

        $queryParams = array(
            'idSite'  => self::$idSite,
            'date'    => '2017-05-05',
            'period'  => 'month',
            'hideColumns' => 'sum_visit_length' // for unknown reasons this field is different in MySQLI only for this system test
        );

        // we need to manually reload the translations since they get reset for some reason in IntegrationTestCase::tearDown();
        Fixture::loadAllTranslations();

        $this->assertApiResponseEqualsExpected("UserCountry.getRegion", $queryParams);
        $this->assertApiResponseEqualsExpected("UserCountry.getCountry", $queryParams);
    }

    /**
     * @return string
     */
    private function executeCommand()
    {
        $command = new ConvertRegionCodesToIso();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $params = array();

        $params['command'] = $command->getName();
        $commandTester->execute($params);
        $result = $commandTester->getDisplay();

        return $result;
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

ConvertRegionCodesToIsoTest::$fixture = new Fixture();
