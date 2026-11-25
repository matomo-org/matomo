<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model;

/**
 * Provides API methods for managing two-factor authentication.
 *
 * @method static \Piwik\Plugins\TwoFactorAuth\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private TwoFactorAuthentication $twoFa;

    public function __construct(TwoFactorAuthentication $twoFa)
    {
        $this->twoFa = $twoFa;
    }

    /**
     * Disables two-factor authentication for the specified user. Your own two-factor authentication can only be
     * turned off in your personal security settings.
     *
     * @param string $userLogin The login or email address of the user whose two-factor authentication should be reset.
     * @param string $passwordConfirmation The current superuser's password confirmation.
     * @return void
     */
    public function resetTwoFactorAuth(
        string $userLogin,
        #[\SensitiveParameter]
        string $passwordConfirmation = ''
    ) {
        Piwik::checkUserHasSuperUserAccess();

        // the database matches logins loosely, so work with the login as it is stored
        $model = new Model();
        $user = $model->getUser($userLogin) ?: $model->getUserByEmail($userLogin);
        $storedLogin = $user['login'] ?? $userLogin;

        // turning off your own 2FA has to go through the personal security settings, which ask for a 2FA code
        if ($storedLogin === Piwik::getCurrentUserLogin()) {
            throw new BadRequestException(Piwik::translate('TwoFactorAuth_ResetOwnTwoFactorAuthNotAllowed'));
        }

        $this->confirmCurrentUserPassword($passwordConfirmation);
        $this->twoFa->disable2FAforUser($storedLogin);
    }
}
