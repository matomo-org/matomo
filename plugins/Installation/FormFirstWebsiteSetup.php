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
class Piwik_Installation_FormFirstWebsiteSetup extends Piwik_QuickForm
{
	function validate()
	{
		try {
    		$timezone = $this->getSubmitValue('timezone');
    		if(!empty($timezone))
    		{
    			Piwik_SitesManager_API::getInstance()->setDefaultTimezone($timezone);
    		}
		} catch(Exception $e) {
			$this->_errors['timezone'] = Piwik_Translate('General_NotValid', Piwik_Translate('Installation_Timezone'));
		}
		return parent::validate();
	}
	
	function init()
	{
		$urlToGoAfter = 'index.php' . Piwik_Url::getCurrentQueryString();

		$urlExample = 'http://example.org';
		$javascriptOnClickUrlExample = "\"javascript:if(this.value=='$urlExample'){this.value='http://';} this.style.color='black';\"";
		
		$timezones = Piwik_SitesManager_API::getInstance()->getTimezonesList();
		$timezones = array_merge(array('No timezone' => Piwik_Translate('SitesManager_SelectACity')), $timezones);
		
		$formElements = array(
			array('text', 'siteName', Piwik_Translate('Installation_SetupWebSiteName')),
			array('text', 'url', Piwik_Translate('Installation_SetupWebSiteURL'), "style='color:rgb(153, 153, 153);' value=$urlExample onfocus=".$javascriptOnClickUrlExample." onclick=".$javascriptOnClickUrlExample),
			array('select', 'timezone', Piwik_Translate('Installation_Timezone'), $timezones),
			
		);
		$this->addElements( $formElements );
		
		$formRules = array();
		foreach($formElements as $row)
		{
			$formRules[] = array($row[1], Piwik_Translate('General_Required', $row[2]), 'required');
		}
		
	
		$submitTimezone = $this->getSubmitValue('timezone');
		if(!$this->isSubmitted()
			|| !empty($submitTimezone))
		{
			$this->setSelected('timezone', $submitTimezone);
		}
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', Piwik_Translate('Installation_SubmitGo'));
	}	
}
