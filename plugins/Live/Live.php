<?php

/**
	TODO Live! Plugin
	====
	- api propre
	- html
	- jquery spy
	- make sure only one query is launched at once or what if requests takes more than 10s to succeed?
	- simple stats above in TEXT
	- Security review
	- blog post, push version
	
//TODO add api to get actions name/count/first/last/etc
 */

class Piwik_Live extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Live Visitors',
			'description' => 'Live Visitors!',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}

Piwik_AddWidget('Live!', 'Live Visitors!', 'Live', 'widget');