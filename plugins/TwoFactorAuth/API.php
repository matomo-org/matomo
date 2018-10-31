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
     * @var Validate2FA
     */
    private $validate2FA;

    public function __construct(Validate2FA $validate2FA)
    {
        $this->validate2FA = $validate2FA;
    }

    public function resetTwoFactorAuth($userLogin)
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->validate2FA->disable2FAforUser($userLogin);
    }
}

