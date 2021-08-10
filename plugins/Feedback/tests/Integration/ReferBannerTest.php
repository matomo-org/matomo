<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Feedback\tests\Integration;

use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\Feedback\Feedback;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ReferBannerTest extends IntegrationTestCase
{
    /** @var Feedback */
    private $feedback;

    /** @var Model */
    private $userModel;

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
            '2019-03-03',
            'super'
        );
        FakeAccess::$identity = 'user1';
        FakeAccess::$superUser = false;
    }

    public function tearDown(): void
    {
        Option::deleteLike('Feedback.nextReferReminder.%');
        try {
            $this->userModel->deleteUserOnly('user1');
        } catch (\Exception $e) {
            // ignore possible errors triggered when the delete user event is posted
        }

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }


    public function test_shouldNotShowReferBannerTo_AnonymousUser()
    {
        FakeAccess::$identity = '';

        $this->assertFalse($this->feedback->showReferBanner());
    }

    public function test_shouldNotShowReferBannerTo_NotSuperUser()
    {
        FakeAccess::$identity = 'user1';

        $this->assertFalse($this->feedback->showReferBanner());
    }

    public function test_shouldNotShowReferBannerTo_SuperUser_First()
    {
        FakeAccess::$identity = 'super';
        FakeAccess::$superUser = true;

        $this->assertFalse($this->feedback->showReferBanner());
    }

    public function test_shouldNotShowReferBanner_ifNeverRemindOn()
    {
        FakeAccess::$identity = 'super';
        FakeAccess::$superUser = true;
        Option::set('Feedback.nextReferReminder.super', '-1');

        $this->assertFalse($this->feedback->showReferBanner());
    }

    public function test_shouldNotShowReferBanner_ifNextReminderDateInTheFuture()
    {
        FakeAccess::$identity = 'super';
        FakeAccess::$superUser = true;

        Date::$now = strtotime('2021-01-01');
        $futureDate = Date::factory('2021-02-01')->toString('Y-m-d');
        Option::set('Feedback.nextReferReminder.super', $futureDate);

        $this->assertFalse($this->feedback->showReferBanner());
    }

    public function test_shouldShowReferBanner_ifNextReminderDateInThePast()
    {
        FakeAccess::$identity = 'super';
        FakeAccess::$superUser = true;

        Date::$now = strtotime('2021-01-01');
        $pastDate = Date::factory('2020-01-01')->toString('Y-m-d');
        Option::set('Feedback.nextReferReminder.super', $pastDate);

        $this->assertTrue($this->feedback->showReferBanner());
    }
}
