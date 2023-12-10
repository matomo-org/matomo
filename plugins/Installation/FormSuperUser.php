<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Installation;

use HTML_QuickForm2_DataSource_Array;
use HTML_QuickForm2_Factory;
use HTML_QuickForm2_Rule;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\QuickForm2;
use Piwik\Url;

/**
 * phpcs:ignoreFile PSR1.Classes.ClassDeclaration.MultipleClasses
 */
class FormSuperUser extends QuickForm2
{
    function __construct($id = 'generalsetupform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes = array('autocomplete' => 'off'), $trackSubmit);
    }

    function init()
    {
        HTML_QuickForm2_Factory::registerRule('checkLogin', 'Piwik\Plugins\Installation\RuleIsValidLoginString');
        HTML_QuickForm2_Factory::registerRule('checkEmail', 'Piwik\Plugins\Installation\RuleIsValidEmailString');

        $login = $this->addElement('text', 'login')
            ->setLabel(Piwik::translate('Installation_SuperUserLogin'));
        $login->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_SuperUserLogin')));
        $login->addRule('checkLogin');

        $password = $this->addElement('password', 'password')
            ->setLabel(Piwik::translate('Installation_Password'));
        $password->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Password')));
        $pwMinLen = UsersManager::PASSWORD_MIN_LENGTH;
        $pwLenInvalidMessage = Piwik::translate('UsersManager_ExceptionInvalidPassword', array($pwMinLen));
        $password->addRule('length', $pwLenInvalidMessage, array('min' => $pwMinLen));

        $passwordBis = $this->addElement('password', 'password_bis')
            ->setLabel(Piwik::translate('Installation_PasswordRepeat'));
        $passwordBis->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_PasswordRepeat')));
        $passwordBis->addRule('eq', Piwik::translate('Installation_PasswordDoNotMatch'), ['operand' => $password]);

        $email = $this->addElement('text', 'email')
            ->setLabel(Piwik::translate('Installation_Email'));
        $email->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Email')));
        $email->addRule('checkEmail', Piwik::translate('UsersManager_ExceptionInvalidEmail'));

        $this->addElement('checkbox', 'subscribe_newsletter_piwikorg', null,
            array(
                'content' => '&nbsp;&nbsp;' . Piwik::translate('Installation_PiwikOrgNewsletter'),
            ));

        $professionalServicesNewsletter = Piwik::translate('Installation_ProfessionalServicesNewsletter',
            ["<a href='" . Url::addCampaignParametersToMatomoLink('https://matomo.org/support/') . "' style='color:#444;' rel='noreferrer noopener' target='_blank'>", "</a>"]

        );

        $privacyNoticeLink = '<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/privacy-policy/') . '" target="_blank" rel="noreferrer noopener">';
        $privacyNotice = '<div class="form-help email-privacy-notice">' . Piwik::translate('Installation_EmailPrivacyNotice', [$privacyNoticeLink, '</a>'])
            . '</div>';

        $this->addElement('checkbox', 'subscribe_newsletter_professionalservices', null,
            array(
                'content' => $privacyNotice . '&nbsp;&nbsp;' . $professionalServicesNewsletter
            ));

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_Next') . ' »', 'class' => 'btn'));

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'subscribe_newsletter_piwikorg' => 0,
            'subscribe_newsletter_professionalservices' => 0,
        )));
    }
}

/**
 * Login id validation rule
 *
 */
class RuleIsValidLoginString extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        try {
            $login = $this->owner->getValue();
            if (!empty($login)) {
                Piwik::checkValidLoginString($login);
            }
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
        }
        return true;
    }
}

/**
 * Email address validation rule
 *
 */
class RuleIsValidEmailString extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        return Piwik::isValidEmailString($this->owner->getValue());
    }
}
