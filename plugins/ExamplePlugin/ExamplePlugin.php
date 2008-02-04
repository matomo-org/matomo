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
	
	function install()
	{
		Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " ADD `feedburnerName` VARCHAR( 100 ) NOT NULL ;");
	}
	function uninstall()
	{
		Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " DROP `feedburnerName`");
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
	
	function blogMatthieu()
	{
		echo '
		<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" type="application/x-shockwave-flash" width="400px" height="338px" id="InsertWidget_3fe0d93d-bae5-42f9-bec4-4235cb6285a8" align="middle">
			<param name="movie" value="http://widgetserver.com/syndication/flash/wrapper/InsertWidget.swf?appId=3fe0d93d-bae5-42f9-bec4-4235cb6285a8"/>
			<param name="quality" value="high" />
			<param name="wmode" value="transparent" />
			<param name="menu" value="false" />
		<embed src="http://widgetserver.com/syndication/flash/wrapper/InsertWidget.swf?appId=3fe0d93d-bae5-42f9-bec4-4235cb6285a8"  name="InsertWidget_3fe0d93d-bae5-42f9-bec4-4235cb6285a8"  width="400px" height="338px" quality="high" menu="false" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent" align="middle" /> </object>
		<img style="visibility:hidden;width:0px;height:0px;" border="0" width="0" height="0" src="http://runtime.widgetbox.com/syndication/track/3fe0d93d-bae5-42f9-bec4-4235cb6285a8.gif" />
		';
	}
	
	function feedburner()
	{
		$view = new Piwik_View('ExamplePlugin/feedburner.tpl');
		$feedburnerFeedName = Piwik_FetchOne('SELECT feedburnerName FROM '.Piwik::prefixTable('site').
								' WHERE idsite = ?', Piwik_Common::getRequestVar('idSite',1,'int') );
		if(empty($feedburnerFeedName))
		{
			$feedburnerFeedName = 'BlogGiik';
		}
		$view->feedburnerFeedName = $feedburnerFeedName;
		echo $view->render();
	}
	function saveFeedburnerName()
	{
		Piwik_Query('UPDATE '.Piwik::prefixTable('site').' SET feedburnerName = ?',
							Piwik_Common::getRequestVar('name','','string'));
	}
}

Piwik_AddWidget('ExamplePlugin', 'exampleWidget', 'Example widget');
Piwik_AddWidget('ExamplePlugin', 'feedburner', 'Feedburner statistics');
Piwik_AddWidget('ExamplePlugin', 'blogMatthieu', 'Blog matthieu RSS');