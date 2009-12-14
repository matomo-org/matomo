<?php
/**
 * @package Updates
 */
class Piwik_Updates_0_5_3 implements Piwik_iUpdate
{
	static function update()
	{
		$config = Zend_Registry::get('config');
		try {
			if(is_writable( Piwik_Config::getDefaultUserConfigPath() )) {
				$plugins = $config->Plugins->toArray();
				$plugins[] = 'MultiSites';
				$config->Plugins = $plugins;
				$config->__destruct();
				Piwik::createConfigObject();
				return;
			}
		} catch(Exception $e) { }
		throw new Piwik_Updater_UpdateErrorException("You can enable the new MultiSites plugin in the Plugins screen in the Piwik admin!");
	}
}