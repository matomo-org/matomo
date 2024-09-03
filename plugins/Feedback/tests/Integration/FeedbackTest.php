<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Feedback\tests\Unit;

use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Feedback\API;
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

        $this->userModel->addUser(
            'user2',
            'a98732d98732',
            'user2@example.com',
            Date('Y-m-d')
        );
        FakeAccess::$identity = 'user1';
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesView = [1];
        $this->now = Date::$now;
    }

    public function tearDown(): void
    {
        FakeAccess::$identity = 'user1';
        Option::deleteLike('Feedback.nextFeedbackReminder.%');
        $this->userModel->deleteUserOnly('user1');

        FakeAccess::$identity = 'user2';
        Option::deleteLike('Feedback.nextFeedbackReminder.%');
        $this->userModel->deleteUserOnly('user2');

        Date::$now = $this->now;
        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return array(
          'Piwik\Access' => new FakeAccess()
        );
    }


    public function testShouldPromptForFeedbackAnonymousUser()
    {
        FakeAccess::$identity = '';

        $this->assertFalse($this->feedback->showQuestionBanner());
    }


    public function testShouldPromptForFeedbackDontRemindUserAgain()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '-1');

        $this->assertFalse($this->feedback->showQuestionBanner());
    }

    public function testShouldPromptForFeedbackNextReminderDateInPast()
    {
        FakeAccess::$identity = 'user1';
        Option::set('Feedback.nextFeedbackReminder.user1', '2019-05-31');
        $this->assertTrue($this->feedback->showQuestionBanner());
    }

    public function testShouldPromptForFeedackNextReminderDateToday()
    {
        Option::set('Feedback.nextFeedbackReminder.user1', '2018-10-31');
        $this->assertTrue($this->feedback->showQuestionBanner());
    }

    public function testShouldPromptForFeedbackUserOldThanHalfYear()
    {
        FakeAccess::$identity = 'user1';
        Option::deleteLike('Feedback.nextFeedbackReminder.user1');
        $this->assertFalse($this->feedback->showQuestionBanner());
    }

    public function testShouldNotPromptForFeedbackUserLessThanHalfYear()
    {
        FakeAccess::$identity = 'user2';
        $this->assertFalse($this->feedback->showQuestionBanner());
    }

    public function testShouldSendFeedbackForFeature()
    {
        $api = API::getInstance();

        //test failed without message
        $result = $api->sendFeedbackForFeature('test');
        $this->assertEquals(Piwik::translate("Feedback_FormNotEnoughFeedbackText"), $result);

        //test pass with like is string 0
        $result = $api->sendFeedbackForFeature('test', "0", null, "dislike this test");
        $this->assertEquals("success", $result);

        //test pass with like is a string 1
        $result = $api->sendFeedbackForFeature('test', "1", null, "like this test");
        $this->assertEquals("success", $result);

        //test pass with like is null
        $result = $api->sendFeedbackForFeature('test', null, null, "dislike this test");
        $this->assertEquals("success", $result);
    }
}
