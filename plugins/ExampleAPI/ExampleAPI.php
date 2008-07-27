<?php
class Piwik_ExampleAPI extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			// name must be the className prefix!
			'name' => 'ExampleAPI',
			'description' => 'Example Plugin: How to create an API for your plugin, to export your data in multiple formats without any special coding? Visit the <a href="index.php?module=CoreAdminHome&action=showInContext&moduleToLoad=API&actionToLoad=listAllAPI&module=CoreAdminHome&action=showInContext#ExampleAPI">ExampleAPI example methods</a>.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}