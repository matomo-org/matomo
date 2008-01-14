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

require_once "modules/Form.php";
class Piwik_Installation_FormGeneralSetup extends Piwik_Form
{
	function init()
	{
		$urlToGoAfter = Piwik_Url::getCurrentUrl();			
		
		$formElements = array(
			array('text', 'login', 'super user login'),
			array('password', 'password', 'password'),
			array('password', 'password_bis', 'password (repeat)'),
			array('text', 'email', 'email'),
		);
		$this->addElements( $formElements );
		
		$formRules = array();
		foreach($formElements as $row)
		{
			$formRules[] = array($row[1], sprintf('%s required', $row[2]), 'required');
		}
		
		$formRules[] = array( 	'email', 
								'email adress must have a valid format', 
								'checkEmail'
		);
		$formRules[] = array( 	'password',
								'password do not match',
								'fieldHaveSameValue',
								'password_bis'
		);
		
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', 'Go!');
	}	
}
