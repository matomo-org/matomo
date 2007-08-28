<?php
class Piwik_Timer
{
    private $m_Start;

    public function __construct()
    {
        $this->m_Start = 0.0;
        $this->init();
    }

    private function getMicrotime()
    {
        list($micro_seconds, $seconds) = explode(" ", microtime());
        return ((float)$micro_seconds + (float)$seconds);
    }

    public function init()
    {
        $this->m_Start = $this->getMicrotime();
    }

    public function getTime($decimals = 2)
    {
        return number_format($this->getMicrotime() - $this->m_Start, $decimals, '.', '');
    }
    public function getTimeMs($decimals = 2)
    {
        return number_format(1000*($this->getMicrotime() - $this->m_Start), $decimals, '.', '');
    }
    
    public function __toString()
    {
    	return "Time elapsed: ". $this->getTime() ."s";
    }
}
