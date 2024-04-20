<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Fixtures;

use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;

/**
 * Simple fixture that creates a user with pending invitation
 */
class PendingUsers extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $users = [];


    public $pendingUser = [
      'login' => '000pendingUser',
      'email' => 'pendinguser2light@example.com'
    ];

    public $token = "13cb9dcef6cc70b02a640cee30dc8ce9";

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->setUpUser();
        $this->setUpTermsAndPrivacy();
    }

    public function tearDown(): void
    {
        // empty
    }

    protected function setUpUser()
    {
        $model = new Model();
        $model->addUser($this->pendingUser['login'], '', $this->pendingUser['email'], $this->dateTime, 1);
        $model->attachInviteToken($this->pendingUser['login'], $this->token, 7);
    }


    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime, $ecommerce = 1);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function setUpTermsAndPrivacy()
    {
        $settings = new SystemSettings();
        $settings->termsAndConditionUrl->setValue('matomo.org');
        $settings->privacyPolicyUrl->setValue('matomo.org');
        $settings->save();
    }
}
