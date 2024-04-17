<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Date;
use Piwik\Plugins\Login\Model;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();
        $this->testInstance = new Model();
    }

    public function test_getTotalLoginAttemptsInLastHourForLogin_returnsDistinctCountOfIpForTheRightLogin()
    {
        Date::$now = strtotime('2020-02-03 05:00:00');

        // brute_force_log
        $this->insertAttempt(['ip_address' => '10.20.30.40', 'attempted_at' => '2020-02-03 04:05:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.41', 'attempted_at' => '2020-02-03 04:25:00', 'login' => 'athirduser']);
        $this->insertAttempt(['ip_address' => '10.20.30.42', 'attempted_at' => '2020-02-03 02:36:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.43', 'attempted_at' => '2020-02-03 04:41:00', 'login' => 'anotheruser']);
        $this->insertAttempt(['ip_address' => '10.20.30.44', 'attempted_at' => '2020-02-03 04:09:00', 'login' => 'stillanotheruser']);
        $this->insertAttempt(['ip_address' => '10.20.30.45', 'attempted_at' => '2020-02-03 04:21:00', 'login' => 'theuser']);

        $count = $this->testInstance->getTotalLoginAttemptsInLastHourForLogin('theuser');
        $this->assertEquals(2, $count);
    }

    public function test_getTotalLoginAttemptsInlastHourForLogin_returnsZeroIfAllAttemptsAreBeforeLastHour()
    {
        Date::$now = strtotime('2020-02-03 05:00:00');

        // brute_force_log
        $this->insertAttempt(['ip_address' => '10.20.30.40', 'attempted_at' => '2020-02-03 02:05:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.41', 'attempted_at' => '2020-02-03 02:25:00', 'login' => 'athirduser']);
        $this->insertAttempt(['ip_address' => '10.20.30.42', 'attempted_at' => '2020-02-03 02:36:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.43', 'attempted_at' => '2020-02-03 02:41:00', 'login' => 'anotheruser']);
        $this->insertAttempt(['ip_address' => '10.20.30.44', 'attempted_at' => '2020-02-03 04:59:00', 'login' => 'stillanotheruser']);
        $this->insertAttempt(['ip_address' => '10.20.30.45', 'attempted_at' => '2020-02-03 02:21:00', 'login' => 'theuser']);

        $count = $this->testInstance->getTotalLoginAttemptsInLastHourForLogin('theuser');
        $this->assertEquals(0, $count);
    }

    public function test_hasNotifiedUserAboutSuspiciousLogins_returnsFalseIfJsonValueIsBroken()
    {
        $optionName = 'BruteForceDetection.suspiciousLoginCountNotified.theuser';
        Option::set($optionName, 'aslkdfjsadlkfj');

        $result = $this->testInstance->hasNotifiedUserAboutSuspiciousLogins('theuser');
        $this->assertFalse($result);
    }

    public function test_hasNotifiedUserAboutSuspiciousLogins_returnsFalseIfJsonValueIsNegative()
    {
        $optionName = 'BruteForceDetection.suspiciousLoginCountNotified.theuser';
        Option::set($optionName, '-5');

        $result = $this->testInstance->hasNotifiedUserAboutSuspiciousLogins('theuser');
        $this->assertFalse($result);
    }

    public function test_hasNotifiedUserAboutSuspiciousLogins_returnsFalseIfThereWasNoLastTimeSent()
    {
        $result = $this->testInstance->hasNotifiedUserAboutSuspiciousLogins('theuser');
        $this->assertFalse($result);
    }

    public function test_hasNotifiedUserAboutSuspiciousLogins_returnsFalseIfLastTimeSentIsBeforeTwoWeeks()
    {
        $optionName = 'BruteForceDetection.suspiciousLoginCountNotified.theuser';
        Option::set($optionName, \Piwik\Date::now()->subWeek(3)->getTimestamp());

        $result = $this->testInstance->hasNotifiedUserAboutSuspiciousLogins('theuser');
        $this->assertFalse($result);
    }

    public function test_hasNotifiedUserAboutSuspiciousLogins_returnsTrueIfLastTimeSentIsWithinTwoWeeks()
    {
        $optionName = 'BruteForceDetection.suspiciousLoginCountNotified.theuser';
        Option::set($optionName, \Piwik\Date::now()->subWeek(1)->getTimestamp());

        $result = $this->testInstance->hasNotifiedUserAboutSuspiciousLogins('theuser');
        $this->assertTrue($result);
    }

    public function test_getDistinctIpsAttemptingLoginsInLastHour_returnsCountOfDistinctIpsOfFailedLoginsForUserInLastHour()
    {
        Date::$now = strtotime('2020-02-03 05:00:00');

        // brute_force_log
        $this->insertAttempt(['ip_address' => '10.20.30.40', 'attempted_at' => '2020-02-03 04:05:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.41', 'attempted_at' => '2020-02-03 04:25:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.42', 'attempted_at' => '2020-02-03 02:36:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.43', 'attempted_at' => '2020-02-03 04:41:00', 'login' => 'anotheruser']);
        $this->insertAttempt(['ip_address' => '10.20.30.41', 'attempted_at' => '2020-02-03 04:09:00', 'login' => 'theuser']);
        $this->insertAttempt(['ip_address' => '10.20.30.45', 'attempted_at' => '2020-02-03 04:21:00', 'login' => 'theuser']);

        $count = $this->testInstance->getDistinctIpsAttemptingLoginsInLastHour('theuser');
        $this->assertEquals(3, $count);
    }

    public function test_markSuspiciousLoginsNotifiedEmailSent_setsTheOptionValueToNow()
    {
        Date::$now = strtotime('2020-02-03 05:00:00');

        $this->testInstance->markSuspiciousLoginsNotifiedEmailSent('theuser');

        $optionName = 'BruteForceDetection.suspiciousLoginCountNotified.theuser';
        $this->assertEquals('2020-02-03 05:00:00', Date::factory(Option::get($optionName))->getDatetime());
    }

    private function insertAttempt($params)
    {
        $sql = "INSERT INTO " . Common::prefixTable(BruteForceDetection::TABLE_NAME) . " (ip_address, attempted_at, login) VALUES (?, ?, ?)";
        Db::query($sql, [
            $params['ip_address'],
            $params['attempted_at'],
            $params['login'],
        ]);
    }
}
