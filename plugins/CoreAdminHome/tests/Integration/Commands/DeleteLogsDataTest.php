<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group Core
 */
class DeleteLogsDataTest extends ConsoleCommandTestCase
{
    /**
     * @var ManySitesImportedLogs
     */
    public static $fixture;

    /**
     * @dataProvider getTestDataForInvalidDateRangeTest
     */
    public function test_Command_Fails_WhenInvalidDateRangeSupplied($dateRange)
    {
        $this->applicationTester->setInputs(["N\n"]);
        $result = $this->applicationTester->run(array(
            'command' => 'core:delete-logs-data',
            '--dates' => $dateRange,
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString('Invalid date range supplied', $this->applicationTester->getDisplay());
    }

    public function getTestDataForInvalidDateRangeTest()
    {
        return array(
            array('completegarbage'),
            array('2012-01-01,garbage'),
            array('garbage,2012-01-01'),
            array('2012-02-01,2012-01-01'), // first date is older than the last date
            array(',')
        );
    }

    public function test_Command_Fails_WhenInvalidSiteIdSupplied()
    {
        $this->applicationTester->setInputs(["N\n"]);
        $result = $this->applicationTester->run(array(
            'command' => 'core:delete-logs-data',
            '--dates' => '2012-01-01,2012-01-02',
            '--idsite' => 43,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString('Invalid site ID', $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getTestDataForInvalidIterationStepTest
     */
    public function test_Command_Fails_WhenInvalidIterationStepSupplied($limit)
    {
        $this->applicationTester->setInputs(["N\n"]);
        $result = $this->applicationTester->run(array(
            'command' => 'core:delete-logs-data',
            '--dates' => '2012-01-01,2012-01-02',
            '--idsite' => self::$fixture->idSite,
            '--limit' => $limit,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString('Invalid row limit supplied', $this->applicationTester->getDisplay());
    }

    public function getTestDataForInvalidIterationStepTest()
    {
        return array(
            array(0),
            array(-45)
        );
    }

    public function test_Command_SkipsLogDeletionIfUserDoesNotConfirm()
    {
        $this->applicationTester->setInputs(["N\n"]);
        $dateRange = '2012-08-09,2012-08-11';
        $this->assertVisitsFoundInLogs($dateRange);

        $result = $this->applicationTester->run(array(
            'command' => 'core:delete-logs-data',
            '--dates' => $dateRange,
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ));

        $this->assertEquals(1, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertNotRegExp("/Successfully deleted [0-9]+ rows from all log tables/", $this->applicationTester->getDisplay());
    }

    public function test_Command_CorrectlyDeletesRequestedLogFiles()
    {
        $this->applicationTester->setInputs(["Y\n"]);
        $dateRange = '2012-08-09,2012-08-11';
        $this->assertVisitsFoundInLogs($dateRange);

        $options = array('interactive' => true);
        $result = $this->applicationTester->run(array(
            'command' => 'core:delete-logs-data',
            '--dates' => $dateRange,
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ), $options);

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Successfully deleted 19 visits", $this->applicationTester->getDisplay());
    }

    protected function assertVisitsFoundInLogs($dateRange)
    {
        list($from, $to) = explode(",", $dateRange);

        /** @var RawLogDao $dao */
        $dao = StaticContainer::get('Piwik\DataAccess\RawLogDao');
        $this->assertNotEmpty($dao->countVisitsWithDatesLimit($from, $to));
    }
}

DeleteLogsDataTest::$fixture = new ManySitesImportedLogs();
