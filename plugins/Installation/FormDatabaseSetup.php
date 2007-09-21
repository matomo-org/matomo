<?php
require_once "modules/Form.php";
class Piwik_Installation_FormDatabaseSetup extends Piwik_Form
{
	function init()
	{
		$urlToGoAfter = Piwik_Url::getCurrentUrl();			
		
		$formElements = array(
			array('text', 'host', 'mysql server', 'value='.'localhost'),
			array('text', 'username', 'login'),
			array('password', 'password', 'password'),
			array('text', 'dbname', 'database name'),
			array('text', 'tables_prefix', 'table prefix', 'value='.'piwik_'),
		);
		$this->addElements( $formElements );
		
		$formRules = array();
		foreach($formElements as $row)
		{
			if($row[1] != 'password' && $row[1] != 'tables_prefix')
			{
				$formRules[] = array($row[1], sprintf('%s required', $row[2]), 'required');
			}
		}
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', 'Go!');
	}	
}
