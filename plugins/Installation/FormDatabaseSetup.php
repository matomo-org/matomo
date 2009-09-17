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
class Piwik_Installation_FormDatabaseSetup extends Piwik_Form
{
	function init()
	{		
		$infos = Piwik_Installation_Controller::getSystemInformation();
		$adapters = array();
		foreach($infos['adapters'] as $adapter => $port)
		{
			$adapters[$adapter] = $adapter;
		}

		$formElements = array(
			array('text', 'host', 'mysql server', 'value='.'localhost'),
			array('text', 'username', 'login'),
			array('password', 'password', 'password'),
			array('text', 'dbname', 'database name'),
			array('text', 'tables_prefix', 'table prefix', 'value='.'piwik_'),
			array('select', 'adapter', 'adapter', $adapters),
		);
		$this->addElements( $formElements );
		
		$formRules = array();
		foreach($formElements as $row)
		{
			if($row[1] != 'password' && $row[1] != 'tables_prefix')
			{
				$formRules[] = array($row[1], Piwik_Translate('General_Required', $row[2]), 'required');
			}
		}
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', Piwik_Translate('Installation_SubmitGo'));
	}	
}
