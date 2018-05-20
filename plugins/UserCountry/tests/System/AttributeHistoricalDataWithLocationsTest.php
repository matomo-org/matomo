<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin;
use Piwik\Plugins\UserCountry\Commands\AttributeHistoricalDataWithLocations;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AttributeHistoricalDataWithLocationsTest
 * @package Piwik\Plugins\UserCountry\tests\Integration
 *
 * @group UserCountry
 * @group AttributeHistoricalDataWithLocations
 */
class AttributeHistoricalDataWithLocationsTest extends IntegrationTestCase
{
    /**
     * @var ManyVisitsWithGeoIP
     */
    public static $fixture = null;

    public function setUp()
    {
        parent::setUp();

        $tablesToUpdate = array('log_visit', 'log_conversion');
        $columnsToUpdate = array(
            'location_country' => '"xx"',
            'location_region' => 'NULL',
            'location_city' => 'NULL',
            'location_latitude' => 'NULL',
            'location_longitude' => 'NULL'
        );

        foreach ($tablesToUpdate as $table) {
            $sql = "UPDATE `" . Common::prefixTable($table) . "` SET ";

            $sets = array();
            foreach ($columnsToUpdate as $column => $defaultValue) {
                $sets[] = $column . ' = ' . $defaultValue;
            }

            $sql .= implode(', ', $sets);

            Db::query($sql);
        }

        self::$fixture->setLocationProvider('GeoIP2-City.mmdb');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Not enough arguments
     */
    public function testExecute_ShouldThrowException_IfArgumentIsMissing()
    {
        $this->executeCommand(null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionInvalidDateFormat
     */
    public function testExecute_ShouldReturnMessage_IfDatesAreInvalid()
    {
        $this->executeCommand('test');
    }

    public function testExecute_ShouldReturnEmptyWorkingProcessLogs_IfThereIsNoData()
    {
        $this->assertRegExp(
            '/Re-attribution for date range: 2014-06-01 to 2014-06-06. 0 visits to process with provider "geoip2php"./',
            $this->executeCommand('2014-06-01,2014-06-06')
        );
    }

    public function testExecute_ShouldReturnLogAfterWorkingWithSomeData()
    {
        $result = $this->executeCommand('2010-01-03,2010-06-03');

        $this->assertContains(
            'Re-attribution for date range: 2010-01-03 to 2010-06-03. 35 visits to process with provider "geoip2php".',
            $result
        );

        $this->assertRegExp('/100% processed. Time elapsed: [0-9.]+s/', $result);

        $queryParams = array(
            'idSite'  => self::$fixture->idSite,
            'date'    => self::$fixture->dateTime,
            'period'  => 'month',
            'hideColumns' => 'sum_visit_length' // for unknown reasons this field is different in MySQLI only for this system test
        );

        // we need to manually reload the translations since they get reset for some reason in IntegrationTestCase::tearDown();
        // if we do not load translations, a DataTable\Map containing multiple periods will contain only one DataTable having
        // the label `General_DateRangeFromTo` instead of many like `From 2010-01-04 to 2010-01-11`, ' `From 2010-01-11 to 2010-01-18`
        // As those data tables would all have the same prettyfied period label they would overwrite each other.
        Translate::loadAllTranslations();

        $this->assertApiResponseEqualsExpected("UserCountry.getCountry", $queryParams);
        $this->assertApiResponseEqualsExpected("UserCountry.getContinent", $queryParams);
        $this->assertApiResponseEqualsExpected("UserCountry.getRegion", $queryParams);
        $this->assertApiResponseEqualsExpected("UserCountry.getCity", $queryParams);
    }

    /**
     * @param string|null $dates
     *
     * @return string
     */
    private function executeCommand($dates)
    {
        $command = new AttributeHistoricalDataWithLocations();

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);

        if (is_null($dates)) {
            $params = array();
        } else {
            $params = array(AttributeHistoricalDataWithLocations::DATES_RANGE_ARGUMENT => $dates);
        }

        $params['command'] = $command->getName();
        $commandTester->execute($params);
        $result = $commandTester->getDisplay();

        return $result;
    }

    public static function configureFixture($fixture)
    {
        // empty (undo IntegrationTestCase configuring)
        $fixture->extraTestEnvVars['loadRealTranslations'] = false;
    }

    public static function getPathToTestDirectory()
    {
        return __DIR__;
    }
}

AttributeHistoricalDataWithLocationsTest::$fixture = new ManyVisitsWithGeoIP();