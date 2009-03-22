<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Timer.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_Helper
 */

/**
 * 
 * @package Piwik_Helper
 */
class Piwik_Timer
{
	private $timerStart;
	private $memoryStart;

	public function __construct()
	{
		$this->init();
	}

	public function init()
	{
		$this->timerStart = $this->getMicrotime();
		$this->memoryStart = $this->getMemoryUsage();
	}

	public function getTime($decimals = 3)
	{
		return number_format($this->getMicrotime() - $this->timerStart, $decimals, '.', '');
	}
	
	public function getTimeMs($decimals = 3)
	{
		return number_format(1000*($this->getMicrotime() - $this->timerStart), $decimals, '.', '');
	}

	public function getMemoryLeak()
	{
		return "Memory delta: ".Piwik::getPrettySizeFromBytes($this->getMemoryUsage() - $this->memoryStart);
	}
	
	public function __toString()
	{
		return "Time elapsed: ". $this->getTime() ."s";
	}
	
	private function getMicrotime()
	{
		list($micro_seconds, $seconds) = explode(" ", microtime());
		return ((float)$micro_seconds + (float)$seconds);
	}

	private function getMemoryUsage()
	{
		if(function_exists('memory_get_usage'))
		{
			return memory_get_usage();
		}
		return 0;
	}
}
