<?php
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Registry');

class Piwik_Log extends Zend_Log
{
	function __construct()
	{
		parent::__construct();
		
		Zend_Loader::loadClass('Zend_Log_Writer_Stream');
		$writer 	= new Zend_Log_Writer_Stream('php://output');
		$formatter	= new Zend_Log_Formatter_Simple('%message% <br>' . PHP_EOL);
		$writer->setFormatter($formatter);
		$this->addWriter($writer);
		Zend_Registry::set('logger', $this);
	}
}

?>
