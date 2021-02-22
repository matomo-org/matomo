<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Feedback\tests\Integration;

use Piwik\Date;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Plugins\Feedback\Controller;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ControllerTest extends IntegrationTestCase
{
    /** @var Controller */
    private $controller;

    /** @var Model */
    private $userModel;

    private $now;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new Controller();

        $this->userModel = new Model();
        $this->userModel->addUser(
            'user1',
            'a98732d98732',
            'user1@example.com',
            '2019-03-03'
        );
        FakeAccess::$identity = 'user1';
        FakeAccess::$superUser = false;

        $this->now = Date::$now;
        Date::$now = Date::factory('2019-05-31')->getTimestamp();
    }

    public function tearDown(): void
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
        $_POST['nextReminder'] = '90';
        $this->controller->updateFeedbackReminderDate();

        $option = Option::get('Feedback.nextFeedbackReminder.user1');
        $this->assertEquals($option, '2019-08-29');
    }

    public function test_updateFeedbackReminder_neverAgain()
    {
        $_POST['nextReminder'] = '-1';
        $this->controller->updateFeedbackReminderDate();

        $option = Option::get('Feedback.nextFeedbackReminder.user1');
        $this->assertEquals($option, '-1');
    }

    public function test_updateFeedbackReminder_notLoggedIn()
    {
        $this->expectException(NoAccessException::class);
        FakeAccess::$identity = null;
        FakeAccess::$superUser = false;
        $this->controller->updateFeedbackReminderDate();
    }

    public function test_updateReferReminder_add180Days()
    {
        $_POST['nextReminder'] = '180';
        $this->controller->updateReferReminderDate();

        $option = Option::get('Feedback.nextReferReminder.user1');
        $this->assertEquals($option, '2019-11-27');
    }

    public function test_updateReferReminder_neverAgain()
    {
        $_POST['nextReminder'] = '-1';
        $this->controller->updateReferReminderDate();

        $option = Option::get('Feedback.nextReferReminder.user1');
        $this->assertEquals($option, '-1');
    }

    public function test_updateReferReminder_notLoggedIn()
    {
        $this->expectException(NoAccessException::class);
        FakeAccess::$identity = null;
        FakeAccess::$superUser = false;
        $this->controller->updateReferReminderDate();
    }
}
