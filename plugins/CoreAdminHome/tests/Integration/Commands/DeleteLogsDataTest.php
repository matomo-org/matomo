<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Common;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Helper\QuestionHelper;

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
        $this->setCommandInput('N');

        $result = $this->applicationTester->run(array(
            'command' => 'logs:delete',
            '--dates' => $dateRange,
            '--site' => self::$fixture->idSite,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('Invalid date range supplied', $this->applicationTester->getDisplay());
    }

    public function getTestDataForInvalidDateRangeTest()
    {
        return array(
            array('completegarbage'),
            array('2012-01-01,garbage'),
            array('garbage,2012-01-01'),
            array('2012-02-01,2012-01-01'),
            array(',')
        );
    }

    public function test_Command_Fails_WhenInvalidSiteIdSupplied()
    {
        $this->setCommandInput('N');

        $result = $this->applicationTester->run(array(
            'command' => 'logs:delete',
            '--dates' => '2012-01-01,2012-01-02',
            '--site' => 43,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('Invalid site ID', $this->applicationTester->getDisplay());
    }

    /**
     * @dataProvider getTestDataForInvalidIterationStepTest
     */
    public function test_Command_Fails_WhenInvalidIterationStepSupplied($limit)
    {
        $this->setCommandInput('N');

        $result = $this->applicationTester->run(array(
            'command' => 'logs:delete',
            '--dates' => '2012-01-01,2012-01-02',
            '--site' => self::$fixture->idSite,
            '--limit' => $limit,
            '-vvv' => true
        ));

        $this->assertNotEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains('Invalid row limit supplied', $this->applicationTester->getDisplay());
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
        $this->setCommandInput('N');

        $result = $this->applicationTester->run(array(
            'command' => 'logs:delete',
            '--dates' => '2012-08-09,2012-08-11',
            '--site' => self::$fixture->idSite,
            '-vvv' => true
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertNotRegExp("/Successfully deleted [0-9]+ rows from all log tables/", $this->applicationTester->getDisplay());
    }

    public function test_Command_CorrectlyDeletesRequestedLogFiles()
    {
        $this->setCommandInput('Y');

        $options = array('interactive' => true);
        $result = $this->applicationTester->run(array(
            'command' => 'logs:delete',
            '--dates' => '2012-08-09,2012-08-11',
            '--site' => self::$fixture->idSite,
            '-vvv' => true
        ), $options);

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
        $this->assertContains("Successfully deleted 15 visits", $this->applicationTester->getDisplay());
    }

    private function setCommandInput($value)
    {
        /** @var QuestionHelper $dialog */
        $dialog = $this->application->getHelperSet()->get('question');
        $dialog->setInputStream($this->getInputStream("$value\n"));
    }
}

DeleteLogsDataTest::$fixture = new ManySitesImportedLogs();