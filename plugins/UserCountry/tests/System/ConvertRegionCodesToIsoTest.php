<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\tests\System;

use Piwik\Option;
use Piwik\Plugins\FormAnalytics\tests\Framework\Mock\Date;
use Piwik\Plugins\UserCountry\Commands\ConvertRegionCodesToIso;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AttributeHistoricalDataWithLocationsTest
 * @package Piwik\Plugins\UserCountry\Test\Integration
 *
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

    public function setUp()
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
        $t->setIp(rand(1, 256).'.'.rand(1, 256).'.'.rand(1, 256).'.'.rand(1, 256));
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
        $t->setIp(rand(1, 256).'.'.rand(1, 256).'.'.rand(1, 256).'.'.rand(1, 256));
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

    public function tearDown()
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
        Option::set(GeoIp2::SWITCH_TO_GEOIP2_OPTION_NAME, mktime(0,0,0,5,12,2017));

        self::trackVisit('gr', '14'); // should become A
        self::trackVisit('ir', '03'); // should become 08
        self::trackVisit('ir', '15'); // should become 10
        self::trackVisit('ir', '10'); // should become 05
        self::trackVisit('ad', '05'); // should not change
        self::trackVisit('bm', '04'); // should become empty, as not mappable
        self::trackVisit('gb', 'C5'); // should become empty, as not mappable
        self::trackVisit('jm', '10'); // should become 14

        self::trackVisitAfterSwitch('jm', '10');

        $result = $this->executeCommand();

        $this->assertContains('All region codes converted', $result);

        $queryParams = array(
            'idSite'  => self::$idSite,
            'date'    => '2017-05-05',
            'period'  => 'month',
            'hideColumns' => 'sum_visit_length' // for unknown reasons this field is different in MySQLI only for this system test
        );

        // we need to manually reload the translations since they get reset for some reason in IntegrationTestCase::tearDown();
        // if we do not load translations, a DataTable\Map containing multiple periods will contain only one DataTable having
        // the label `General_DateRangeFromTo` instead of many like `From 2010-01-04 to 2010-01-11`, ' `From 2010-01-11 to 2010-01-18`
        // As those data tables would all have the same prettyfied period label they would overwrite each other.
        Translate::loadAllTranslations();

        $this->assertApiResponseEqualsExpected("UserCountry.getRegion", $queryParams);
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