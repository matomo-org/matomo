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
use Piwik\Plugins\Feedback\Feedback;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class FeedbackTest extends IntegrationTestCase
{
    /** @var Feedback */
    private $feedback;

    /** @var Model */
    private $userModel;

    private $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->feedback = $this->createPartialMock(Feedback::class, ['isDisabledInTestMode']);
        $this->feedback->method('isDisabledInTestMode')->willReturn(false);

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
    }

    public function tearDown(): void
    {
        FakeAccess::$identity = 'user1';
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


    public function test_shouldPromptForFeedback_AnonymousUser()
    {
        FakeAccess::$identity = '';

        $this->assertFalse($this->feedback->getShouldPromptForFeedback());
    }

    public function test_shouldPromptForFeedback_noFeedbackReminderOptionForUser()
    {
        Date::$now = Date::factory('2019-05-31')->getTimestamp();   // 89 days

        $this->assertFalse($this->feedback->getShouldPromptForFeedback());
    }

    public function test_shouldPromptForFeedback_dontRemindUserAgain()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '-1');

        $this->assertFalse($this->feedback->getShouldPromptForFeedback());
    }

    public function test_shouldPromptForFeedback_nextReminderDateInPast()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '2019-05-31');
        Date::$now = Date::factory('2019-06-01')->getTimestamp();

        $this->assertTrue($this->feedback->getShouldPromptForFeedback());
    }

    public function test_shouldPromptForFeedack_nextReminderDateToday()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '2019-05-31');
        Date::$now = Date::factory('2019-05-31')->getTimestamp();

        $this->assertTrue($this->feedback->getShouldPromptForFeedback());
    }

    public function test_shouldPromptForFeedack_nextReminderDateInFuture()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '2019-05-31');
        Date::$now = Date::factory('2019-05-30')->getTimestamp();

        $this->assertFalse($this->feedback->getShouldPromptForFeedback());
    }
}
