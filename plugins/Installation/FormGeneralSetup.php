<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Installation
 */

/**
 * 
 * @package Piwik_Installation
 */
class Piwik_Installation_FormGeneralSetup extends Piwik_Form
{
	function init()
	{
		$urlToGoAfter = 'index.php' . Piwik_Url::getCurrentQueryString();

		$formElements = array(
			array('text', 'login', Piwik_Translate('Installation_SuperUserLogin')),
			array('password', 'password', Piwik_Translate('Installation_Password')),
			array('password', 'password_bis', Piwik_Translate('Installation_PasswordRepeat')),
			array('text', 'email', Piwik_Translate('Installation_Email')),
			array('checkbox', 'subscribe_newsletter_security', '', '&nbsp;&nbsp;' . Piwik_Translate('Installation_SecurityNewsletter')),
			array('checkbox', 'subscribe_newsletter_community', '', '&nbsp;&nbsp;'. Piwik_Translate('Installation_CommunityNewsletter')),
		);
		$this->addElements( $formElements );
		
		if(!$this->isSubmitted()
			|| $this->getSubmitValue('subscribe_newsletter_community') == '1')
		{
			$this->setChecked('subscribe_newsletter_community');
		}
		if(!$this->isSubmitted()
			|| $this->getSubmitValue('subscribe_newsletter_security') == '1')
		{
			$this->setChecked('subscribe_newsletter_security');
		}
		
		$formRules = array();
		foreach($formElements as $row)
		{
			// checkboxes are not required (form should validate when unchecked)
			if(in_array($row[1],array('subscribe_newsletter_security','subscribe_newsletter_community')))
			{
				continue;
			}
			$formRules[] = array($row[1], Piwik_Translate('General_Required', $row[2]), 'required');
		}
		
		$formRules[] = array( 	'email', 
								Piwik_Translate( 'UsersManager_ExceptionInvalidEmail'), 
								'checkEmail'
		);
		$formRules[] = array( 	'password',
								Piwik_Translate( 'Installation_PasswordDoNotMatch'),
								'fieldHaveSameValue',
								'password_bis'
		);
		
		$this->addRules( $formRules );	
		$this->addElement('submit', 'submit', Piwik_Translate('Installation_SubmitGo'));
	}	
}
