<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ExamplePlugin
 */

class Piwik_ExamplePlugin extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			// name must be the className prefix!
			'name' => 'ExamplePlugin',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => false,
		);
	}
}

class Piwik_ExamplePlugin_Controller extends Piwik_Controller
{	
	function index()
	{
		// invoke view
		// render view
		// do stuff...
	}
	function exampleWidget()
	{
		echo "Hello world! <br> You can output whatever you want in widgets, and put them on dashboard or everywhere on the web (in your blog, website, etc.).
		<br>Widgets can include graphs, tables, flash, text, images, etc.
		<br>It's very easy to create a new plugin and widgets in Piwik. Have a look at this example file (/plugins/ExamplePlugin/ExamplePlugin.php).
		<br><i>Happy coding!</i>";
	}
}

Piwik_AddWidget('ExamplePlugin', 'exampleWidget', 'Example widget');