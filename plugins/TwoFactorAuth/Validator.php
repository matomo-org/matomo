<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Common;
use Piwik\Exception\NotYetInstalledException;
use Piwik\Piwik;
use Piwik\Session\SessionFingerprint;
use Exception;
use Piwik\SettingsPiwik;

class Validator
{
    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    public function __construct(TwoFactorAuthentication $twoFactorAuthentication)
    {
        $this->twoFa = $twoFactorAuthentication;
    }

    public function canUseTwoFa()
    {
        if (Common::isPhpCliMode() && (!defined('PIWIK_TEST_MODE') || !PIWIK_TEST_MODE)) {
            // eg when archiving or executing other commands
            return false;
        }

        if (!SettingsPiwik::isMatomoInstalled()) {
            return false;
        }

        return !Piwik::isUserIsAnonymous();
    }

    public function checkCanUseTwoFa()
    {
        Piwik::checkUserIsNotAnonymous();

        if (!SettingsPiwik::isMatomoInstalled()) {
            throw new NotYetInstalledException('Matomo is not set up yet');
        }
    }

    public function check2FaIsRequired()
    {
        if (!$this->twoFa->isUserRequiredToHaveTwoFactorEnabled()) {
            throw new Exception('not available');
        }
    }

    public function check2FaEnabled()
    {
        if (!TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin())) {
            throw new Exception('not available');
        }
    }

    public function check2FaNotEnabled()
    {
        if (TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin())) {
            throw new Exception('not available');
        }
    }

    public function checkVerified2FA()
    {
        $sessionFingerprint = $this->getSessionFingerPrint();
        if (!$sessionFingerprint->hasVerifiedTwoFactor()) {
            throw new Exception('not available');
        }
    }

    public function checkNotVerified2FAYet()
    {
        $sessionFingerprint = $this->getSessionFingerPrint();
        if ($sessionFingerprint->hasVerifiedTwoFactor()) {
            throw new Exception('not available');
        }
    }

    private function getSessionFingerPrint()
    {
        return new SessionFingerprint();
    }

}
