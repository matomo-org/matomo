<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Fixtures\SomePageGoalVisitsWithConversions;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CalculateConversionPagesCommandTest
 * @group Goals
 * @group Plugins
 */
class CalculateConversionPagesCommandTest extends ConsoleCommandTestCase
{
    /**
     * @var SomePageGoalVisitsWithConversions
     */
    public static $fixture = null;

    public function test_CommandSuccessfullyCalculates_ForDateRange()
    {
       $this->unsetPageviewsBefore();

        $this->applicationTester->setInputs(["N\n"]);
        $result = $this->applicationTester->run([
            'command' => 'core:calculate-conversion-pages',
            '--dates' => '2009-01-05,2009-01-31',
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ]);

        // Check command completed ok
        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        // Check conversions have been calculated
        $this->checkPageviewsBeforeValid();
    }

    public function test_CommandSuccessfullyCalculates_ForLastN()
    {
        $this->unsetPageviewsBefore();

        $this->applicationTester->setInputs(["N\n"]);
        $result = $this->applicationTester->run([
            'command' => 'core:calculate-conversion-pages',
            '--last-n' => 10000,
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ]);

        // Check command completed ok
        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        // Check conversions have been calculated
        $this->checkPageviewsBeforeValid();
    }

    /**
     * Set all pageviews before value to null
     *
     * @throws \Exception
     * @return void
     */
    private function unsetPageviewsBefore(): void
    {
        Db::query('UPDATE ' . Common::prefixTable('log_conversion') . ' SET pageviews_before = NULL WHERE idsite = ?',
                  [self::$fixture->idSite]);
    }

    /**
     * Check that the log_conversion.pageviews_before column was correctly calculated
     *
     * @return void
     */
    public function checkPageviewsBeforeValid(): void
    {
        $expectedValues = TrackGoalsPagesTest::getConversionPagesBeforeExpected();

        foreach ($expectedValues as $expected) {
            $actualValue = Db::get()->fetchOne('SELECT pageviews_before FROM ' . Common::prefixTable('log_conversion') .
                                      ' WHERE idlink_va = ?', [$expected['id']]);

            $this->assertEquals($expected['expected'], $actualValue);
        }
    }

}

CalculateConversionPagesCommandTest::$fixture = new SomePageGoalVisitsWithConversions();
