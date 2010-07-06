<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Installation
 */

/**
 * 
 * @package Piwik_Installation
 */
class Piwik_Installation_FormGeneralSetup extends Piwik_QuickForm2
{
	function __construct( $id = 'generalsetupform', $method = 'post', $attributes = null, $trackSubmit = false)
	{
		parent::__construct($id,  $method, $attributes = array('autocomplete' => 'off'), $trackSubmit);
	}

	function init()
	{
		HTML_QuickForm2_Factory::registerRule('checkLogin', 'Piwik_Installation_FormGeneralSetup_Rule_isValidLoginString');
		HTML_QuickForm2_Factory::registerRule('checkEmail', 'Piwik_Installation_FormGeneralSetup_Rule_isValidEmailString');

		$login = $this->addElement('text', 'login')
		              ->setLabel(Piwik_Translate('Installation_SuperUserLogin'));
		$login->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Installation_SuperUserLogin')));
		$login->addRule('checkLogin');

		$password = $this->addElement('password', 'password')
		                 ->setLabel(Piwik_Translate('Installation_Password'));
		$password->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Installation_Password')));

		$passwordBis = $this->addElement('password', 'password_bis')
		     ->setLabel(Piwik_Translate('Installation_PasswordRepeat'));
		$passwordBis->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Installation_PasswordRepeat')));
		$passwordBis->addRule('eq', Piwik_Translate( 'Installation_PasswordDoNotMatch'), $password);

		$email = $this->addElement('text', 'email')
		              ->setLabel(Piwik_Translate('Installation_Email'));
		$email->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Installation_Email')));
		$email->addRule('checkEmail', Piwik_Translate( 'UsersManager_ExceptionInvalidEmail'));

		$this->addElement('checkbox', 'subscribe_newsletter_security', null, array(
			'content' => '&nbsp;&nbsp;' . Piwik_Translate('Installation_SecurityNewsletter'),
		));

		$this->addElement('checkbox', 'subscribe_newsletter_community', null, array(
			'content' => '&nbsp;&nbsp;' . Piwik_Translate('Installation_CommunityNewsletter'),
		));

		$this->addElement('submit', 'submit', array('value' => Piwik_Translate('Installation_SubmitGo')));

		// default values
		$this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
			'subscribe_newsletter_community' => 1,
			'subscribe_newsletter_security' => 1,
		)));
	}
}

class Piwik_Installation_FormGeneralSetup_Rule_isValidLoginString extends HTML_QuickForm2_Rule
{
	function validateOwner()
	{
		try {
    		$login = $this->owner->getValue();
    		if(!empty($login))
    		{
    			Piwik::checkValidLoginString($login);
    		}
		} catch(Exception $e) {
			$this->setMessage($e->getMessage());
			return false;
		}
		return true;
	}
}

class Piwik_Installation_FormGeneralSetup_Rule_isValidEmailString extends HTML_QuickForm2_Rule
{
	function validateOwner()
	{
		return Piwik::isValidEmailString($this->owner->getValue());
	}
}
