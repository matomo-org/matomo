<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth\tests\Fixtures;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\UsersManager\API as UsersAPI;

class TwoFactorFixture extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $idSite2 = 2;

    private $userWith2Fa = 'with2FA';
    private $userWith2FaDisable = 'with2FADisable'; // we use this user to disable two factor
    private $userWithout2Fa = 'without2FA';
    private $userNo2Fa = 'no2FA';
    private $userPassword = '123abcDk3_l3';
    private $superUserWith2Fa = 'superWith2FA';

    const USER_2FA_SECRET = '1111111111111111';


    /**
     * @var RecoveryCodeDao
     */
    private $dao;

    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    public function setUp(): void
    {
        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
        $this->twoFa = StaticContainer::get(TwoFactorAuthentication::class);

        $this->setUpWebsite();
        $this->setUpUsers();
        $this->trackFirstVisit();
    }

    public function tearDown(): void
    {
        // empty
    }

    public function setUpWebsite()
    {
        for ($i = 1; $i <= 2; $i++) {
            if (!self::siteCreated($i)) {
                $idSite = self::createWebsite($this->dateTime);
                // we set type "mobileapp" to avoid the creation of a default container
                $this->assertSame($i, $idSite);
            }
        }
    }

    public function setUpUsers()
    {
        \Piwik\Plugins\UsersManager\API::getInstance()->addUser($this->superUserWith2Fa, $this->userPassword,
            $this->superUserWith2Fa . '@matomo.org');
        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword($this->superUserWith2Fa, true);

        foreach ([$this->userWith2Fa, $this->userWithout2Fa, $this->userWith2FaDisable, $this->userNo2Fa] as $user) {
            \Piwik\Plugins\UsersManager\API::getInstance()->addUser($user, $this->userPassword, $user . '@matomo.org');
            // we cannot set superuser as logme won't work for super user
            UsersAPI::getInstance()->setUserAccess($user, 'view', [$this->idSite, $this->idSite2]);

            if ($this->userWith2Fa === $user) {
                $userModel = new Model();
                $userModel->addTokenAuth($user, 'a4ca4238a0b923820dcc509a6f75849b', 'twofa test', Date::now()->getDatetime());
            }
        }

        foreach ([$this->userWith2Fa, $this->userWith2FaDisable, $this->superUserWith2Fa] as $user) {
            $this->dao->insertRecoveryCode($user, '123456');
            $this->dao->insertRecoveryCode($user, '234567');
            $this->dao->insertRecoveryCode($user, '345678');
            $this->dao->insertRecoveryCode($user, '456789');
            $this->dao->insertRecoveryCode($user, '567890');
            $this->dao->insertRecoveryCode($user, '678901');
            $this->twoFa->saveSecret($user, self::USER_2FA_SECRET);
        }
    }

    protected function trackFirstVisit()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));
    }
}
