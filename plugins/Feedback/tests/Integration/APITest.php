<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Feedback\tests\Unit;

use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\Feedback\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class APITest extends IntegrationTestCase
{
    /** @var API */
    private $api;

    /** @var Model */
    private $userModel;

    private $now;

    public function setUp()
    {
        parent::setUp();
        $this->api = new API();

        $this->userModel = new Model();
        $this->userModel->addUser(
            'user1',
            'a98732d98732',
            'user1@example.com',
            'user1',
            'ab9879dc23876f19',
            '2019-03-03'
        );
        FakeAccess::$identity = 'user1';
        FakeAccess::$superUser = false;

        $this->now = Date::$now;
        Date::$now = Date::factory('2019-05-31')->getTimestamp();
    }

    public function tearDown()
    {
        Option::deleteLike('Feedback.nextFeedbackReminder.%');
        $this->userModel->deleteUserOnly('user1');
        Date::$now = $this->now;

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }


    public function test_updateFeedbackReminder_addNinetyDays()
    {
        $this->api->updateFeedbackReminderDate('90');

        $option = Option::get('Feedback.nextFeedbackReminder.user1');
        $this->assertEquals($option, '2019-08-29');
    }

    public function test_updateFeedbackReminder_neverAgain()
    {
        $this->api->updateFeedbackReminderDate('-1');

        $option = Option::get('Feedback.nextFeedbackReminder.user1');
        $this->assertEquals($option, '-1');
    }
}