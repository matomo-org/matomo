<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;

class ChallengeSetupTwoFa extends Challenge
{
    public function getName()
    {
        return Piwik::translate('Tour_SetupX', Piwik::translate('TwoFactorAuth_TwoFactorAuthentication'));
    }

    public function getDescription()
    {
        return Piwik::translate('TwoFactorAuth_TwoFactorAuthenticationIntro', array('', ''));
    }

    public function getId()
    {
        return 'setup_twofa';
    }

    public function isCompleted()
    {
        return TwoFactorAuthentication::isUserUsingTwoFactorAuthentication(Piwik::getCurrentUserLogin());
    }

    public function getUrl()
    {
        return 'https://matomo.org/faq/general/faq_27245';
    }


}