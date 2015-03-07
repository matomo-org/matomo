<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Test\Integration;

use Piwik\Plugins\UserCountry\Commands\AttributeHistoricalDataWithLocations;
use Piwik\Tests\Fixtures\ManyVisitsWithGeoIP;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AttributeHistoricalDataWithLocationsTest
 * @package Piwik\Plugins\UserCountry\Test\Integration
 *
 * @group UserCountry
 */
class AttributeHistoricalDataWithLocationsTest extends IntegrationTestCase
{
    /**
     * @var ManyVisitsWithGeoIP
     */
    public static $fixture = null;

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Not enough arguments
     */
    public function testExecute_ShouldThrowException_IfArgumentIsMissing()
    {
        $this->executeCommand(null);
    }

    public function testExecute_ShouldReturnMessage_IfDatesAreInvalid()
    {
        $this->assertEquals(
            'Invalid from [1970-01-01] or to [].' . "\n", $this->executeCommand('test')
        );
    }

    public function testExecute_ShouldReturnEmptyWorkingProcessLogs_IfThereIsNoData()
    {
        $this->assertRegExp(
            '/Re-attribution for date range: 2014-06-01 to 2014-06-06. 0 visits to process with provider "geoip_php"./',
            $this->executeCommand('2014-06-01,2014-06-06')
        );
    }

    public function testExecute_ShouldReturnLogAfterWorkingWithSomeData_IfThereAreData()
    {
        $result = $this->executeCommand('2010-01-03,2010-06-03');

        $this->assertContains(
            'Re-attribution for date range: 2010-01-03 to 2010-06-03. 35 visits to process with provider "geoip_php".',
            $result
        );

        $this->assertRegExp('/100% processed. \[in [(0-9)+] seconds\]/', $result);
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
}

AttributeHistoricalDataWithLocationsTest::$fixture = new ManyVisitsWithGeoIP();