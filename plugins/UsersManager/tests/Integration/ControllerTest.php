<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\UsersManager\Controller;
use Piwik\Nonce;
use Piwik\Auth\PasswordStrength;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Translation\Loader\DevelopmentLoader;
use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

/**
 * @group UsersManager
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;
    private $post;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(
            $translator = new Translator(new DevelopmentLoader(new JsonFileLoader())),
            $passwordVerify = new PasswordVerifier(),
            $userModel = new Model(),
            $passwordStrength = new PasswordStrength(true)
        );
        $this->post = $_POST;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_POST = $this->post;
    }

    public function testRecordPasswordChangePasswordStrengthCheck()
    {
        $_POST['nonce'] = Nonce::getNonce('changePasswordNonce');
        $_POST['password'] = 'password1';
        $_POST['passwordBis'] = 'password1';
        // original password (irrelevant for test)
        $_POST['passwordConfirmation'] = '';
        
        try {
            $this->controller->recordPasswordChange();
        } catch (\Exception $e) {
            $this->assertStringContainsString('General_PasswordStrengthValidationFailed', $e->getMessage());
        }
    }
}
