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
		
		echo $this->css();
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
	
	function css()
	{
		return "<style>
		.rss ul {
			list-style-image:none;
			list-style-position:outside;
			list-style-type:none;
			padding:0pt;
		}
		.rss li {
			line-height:140%;
			margin-bottom:6px;
			margin:0.5em 0pt 1em;
		}			
		.rss-title, .rss-date {	
			float:left;
			font-size:14px;
			line-height:140%;
		}
		.rss-title{
			color:#2583AD;
			margin:0pt 0.5em 0.2em 0pt;
			font-weight:bold;
		}	
		.rss-date {
			color:#999999;
			margin:0pt;
		}
		.rss-description {
			clear:both;
			line-height:1.5em;
			font-size:11px;
			color:#333333;
		}
		</style>";
	}
}