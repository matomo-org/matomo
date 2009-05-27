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
		echo '<link rel="stylesheet" type="text/css" href="plugins/ExampleRssWidget/templates/styles.css" />';
	}
}

Piwik_AddWidget('Example Widgets', 'Piwik.org Blog', 'ExampleRssWidget', 'rssPiwik');
Piwik_AddWidget('Example Widgets', 'Piwik Changelog', 'ExampleRssWidget', 'rssChangelog');

class Piwik_ExampleRssWidget_Controller extends Piwik_Controller
{
	function rssPiwik()
	{
		$rss = new Piwik_ExampleRssWidget_Rss('http://feeds.feedburner.com/Piwik');
		$rss->showDescription(true);
		echo $rss->get();
	}
	function rssChangelog()
	{
		$rss = new Piwik_ExampleRssWidget_Rss('http://feeds.feedburner.com/PiwikReleases');
		$rss->setCountPosts(1);
		$rss->showDescription(false);
		$rss->showContent(true);
		echo $rss->get();
	}
}

class Piwik_ExampleRssWidget_Rss
{
	protected $url = null;
	protected $count = 3;
	protected $showDescription = false;
	protected $showContent = false;
	function __construct($url) 
	{
		$this->url = $url;
	}
	function showDescription($bool) 
	{
		$this->showDescription = $bool;
	}
	function showContent($bool) 
	{
		$this->showContent = $bool;
	}
	function setCountPosts($count) 
	{
		$this->count = $count;
	}
	function get() 
	{
		require_once 'Zend/Feed.php';
		try {
		    $rss = Zend_Feed::import($this->url);
		} catch (Zend_Feed_Exception $e) {
			echo "Error while importing feed: {$e->getMessage()}\n";
			exit;
		}

		$output = '<div style="padding:10px 15px;"><ul class="rss">';
		$i = 0;
		foreach($rss as $post)
		{
			$title = $post->title();
			$date = strftime("%B %e, %Y", strtotime($post->pubDate()));
			$link = $post->link();
			
			$output .= '<li><a class="rss-title" title="" href="'.$link.'">'.$title.'</a>'.
						'<span class="rss-date">'.$date.'</span>';
			if($this->showDescription) 
			{
				$output .= '<div class="rss-description">'.$post->description().'</div>';
			}
			if($this->showContent) 
			{
				$output .= '<div class="rss-content">'.$post->content().'</div>';
			}
			$output .= '</li>';
			
			if(++$i == $this->count) break;
		}
		$output .= '</ul></div>';
		return $output;
	}
}
