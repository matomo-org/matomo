<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Mail;
use Piwik\Plugins\UsersManager\UserEmailChanger;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\Model;

class UserEmailChangerTest extends IntegrationTestCase
{
    const NEW_EMAIL = 'newemail@newemail.com';

    /**
     * @var UserEmailChanger
     */
    private $userEmailChanger;

    /**
     * @var Model
     */
    private $userModel;

    /**
     * @var string
     */
    private $capturedToken;

    public function setUp()
    {
        parent::setUp();
        $this->userEmailChanger = new UserEmailChanger();
        $this->userModel = new Model();
        $this->capturedToken = null;

        Manager::getInstance()->loadPluginTranslations();
    }

    public function test_userEmailChange_processWorksAsExpected()
    {
        $user = $this->userModel->getUser('superUserLogin');
        $originalEmail = $user['email'];

        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEmpty($this->capturedToken);

        $user = $this->userModel->getUser('superUserLogin');
        $this->assertEquals($originalEmail, $user['email']);

        $this->userEmailChanger->confirmEmailChange($user, $this->capturedToken);

        $user = $this->userModel->getUser('superUserLogin');
        $this->assertEquals(self::NEW_EMAIL, $user['email']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Token is invalid or has expired
     */
    public function test_passwordReset_shouldNotAllowTokenToBeUsedMoreThanOnce()
    {
        $user = $this->userModel->getUser('superUserLogin');

        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEmpty($this->capturedToken);

        $this->userEmailChanger->confirmEmailChange($user, $this->capturedToken);

        $user = $this->userModel->getUser('superUserLogin');
        $this->assertEquals(self::NEW_EMAIL, $user['email']);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->userEmailChanger->startEmailChange($user, 'anotherpassword');
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->userEmailChanger->confirmEmailChange($user, $oldCapturedToken);
    }

    public function test_passwordReset_shouldNeverGenerateTheSameToken()
    {
        $user = $this->userModel->getUser('superUserLogin');

        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Token is invalid or has expired
     */
    public function test_passwordReset_shouldNotAllowOldTokenToBeUsedAfterAnotherResetRequest()
    {
        $user = $this->userModel->getUser('superUserLogin');

        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEmpty($this->capturedToken);

        sleep(1);

        $oldCapturedToken = $this->capturedToken;
        $this->userEmailChanger->startEmailChange($user, self::NEW_EMAIL);
        $this->assertNotEquals($oldCapturedToken, $this->capturedToken);

        $this->userEmailChanger->confirmEmailChange($user, $oldCapturedToken);
    }

    /**
     * @param Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
        $fixture->extraTestEnvVars['loadRealTranslations'] = true;
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => [
                ['Mail.send', function (Mail $mail) {
                    $body = $mail->getBodyHtml(true);
                    $body = str_replace("\n", "", $body);
                    preg_match('/toke=?n=3D([a-zA-Z0-9=\s]+?)<\/p>/', $body, $matches);
                    $capturedToken = $matches[1];
                    $capturedToken = preg_replace('/=\s*/', '', $capturedToken);
                    $this->capturedToken = $capturedToken;
                }],
            ],
        ];
    }
}