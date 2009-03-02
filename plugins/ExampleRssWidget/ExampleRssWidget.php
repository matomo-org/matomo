<?php

class Piwik_ExampleRssWidget extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Example Rss Widget',
			'description' => 'Example Plugin: How to create a new widget that reads a RSS feed?',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
	
	public function getListHooksRegistered()
	{
		return array( 'template_css_import' => 'css');
	}

	function css()
	{
		echo '<link rel="stylesheet" type="text/css" href="plugins/ExampleRssWidget/styles.css" />';
	}
}

Piwik_AddWidget('ExampleRssWidget', 'rssPiwik', 'Piwik.org Blog');

class Piwik_ExampleRssWidget_Controller extends Piwik_Controller
{
	function rssPiwik()
	{
		require_once 'libs/Zend/Feed.php';
		try {
		    $rss = Zend_Feed::import('http://feeds.feedburner.com/Piwik');
		} catch (Zend_Feed_Exception $e) {
		    echo "Exception caught importing feed: {$e->getMessage()}\n";
		    exit;
		}
		
		echo '<div style="padding:10px 15px;"><ul class="rss">';
		
		$i = 0;
		foreach($rss as $post)
		{
			$title = $post->title();
			$date = strftime("%B %e, %Y", strtotime($post->pubDate()));
			$description = $post->description();
			$link = $post->link();
			
			echo '<li>
				<a class="rss-title" title="" href="'.$link.'">'.$title.'</a>
				<span class="rss-date">'.$date.'</span>
				<div class="rss-description">'.$description.'</div>
				</li>';
			
			if(++$i == 3) break;
		}
		echo '</ul></div>';
	}
}

