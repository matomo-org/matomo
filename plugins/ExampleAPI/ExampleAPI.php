<?php
class Piwik_ExampleAPI extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Example API',
			'description' => 'Example Plugin: How to create an API for your plugin, to export your data in multiple formats without any special coding? Visit the <a href="index.php?module=API&action=listAllAPI#ExampleAPI">ExampleAPI example methods</a>.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}
