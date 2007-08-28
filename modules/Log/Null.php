<?php


class Piwik_Log_Null extends Zend_Log
{
	public function __construct()
	{
	}
	
	public function log($message, $priority = Zend_Log::INFO )
	{
		parent::log($message, $priority);
	}
}


