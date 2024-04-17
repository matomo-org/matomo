<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Common;
use Piwik\Date;
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
            '--last-n' => 2,
            '--idsite' => self::$fixture->idSite,
            '-vvv' => true
        ]);

        // Check command completed ok
        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        // Check conversions have been calculated
        $this->checkPageviewsBeforeValid('2009-01-06 07:54:00');
    }

    /**
     * Set all pageviews before value to null
     *
     * @throws \Exception
     * @return void
     */
    private function unsetPageviewsBefore(): void
    {
        Db::query(
            'UPDATE ' . Common::prefixTable('log_conversion') . ' SET pageviews_before = NULL WHERE idsite = ?',
            [self::$fixture->idSite]
        );
    }

    /**
     * Check that the log_conversion.pageviews_before column was correctly calculated
     *
     * @param string|null $onlyToDate
     *
     * @return void
     * @throws \Exception
     */
    public function checkPageviewsBeforeValid(?string $onlyToDate = null): void
    {
        $expectedValues = TrackGoalsPagesTest::getConversionPagesBeforeExpected();

        foreach ($expectedValues as $expected) {
            $values = Db::get()->fetchAssoc('SELECT server_time, pageviews_before FROM ' . Common::prefixTable('log_conversion') .
                                      ' WHERE idlink_va = ?', [$expected['id']]);
            $row = reset($values);

            // If the 'only to date' parameter is passed then expect only conversions up to that date to be have been
            // processed
            if ($onlyToDate === null || Date::factory($row['server_time'])->getTimestamp() >= Date::factory($onlyToDate)->getTimestamp()) {
                $this->assertEquals($expected['expected'], $row['pageviews_before']);
            } else {
                $this->assertEquals(null, $row['pageviews_before']);
            }
        }
    }
}

CalculateConversionPagesCommandTest::$fixture = new SomePageGoalVisitsWithConversions();
