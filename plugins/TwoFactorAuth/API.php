<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Piwik;

class API extends \Piwik\Plugin\API
{
    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    public function __construct(TwoFactorAuthentication $twoFa)
    {
        $this->twoFa = $twoFa;
    }

    public function resetTwoFactorAuth($userLogin, $passwordConfirmation = '')
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->confirmCurrentUserPassword($passwordConfirmation);
        $this->twoFa->disable2FAforUser($userLogin);
    }
}
