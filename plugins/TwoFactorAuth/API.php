<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordVerifier;

class API extends \Piwik\Plugin\API
{
    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    /**
     * @var PasswordVerifier
     */
    private $passwordVerifier;

    public function __construct(TwoFactorAuthentication $twoFa, PasswordVerifier $passwordVerifier)
    {
        $this->twoFa = $twoFa;
        $this->passwordVerifier = $passwordVerifier;
    }

    public function resetTwoFactorAuth($userLogin, $passwordConfirmation)
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!$this->passwordVerifier->isPasswordCorrect(Piwik::getCurrentUserLogin(), $passwordConfirmation)) {
            throw new \Exception(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'));
        }

        $this->twoFa->disable2FAforUser($userLogin);
    }
}
