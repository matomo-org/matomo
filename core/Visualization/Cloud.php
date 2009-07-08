<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @package Piwik_Visualization
 */


/**
 * Generates a tag cloud from a given data array.
 * The generated tag cloud can be in PHP format, or in HTML. 
 *
 * Inspired from Derek Harvey (www.derekharvey.co.uk)
 * 
 * @package Piwik_Visualization
 */
class Piwik_Visualization_Cloud
{
	protected $wordsArray = array();
	public $truncatingLimit = 30;
	
	/*
	 * Assign word to array
	 * @param string $word
	 * @return string
	 */
	function addWord($word, $value = 1)
	{
		if (isset($this->wordsArray[$word]))
		{
			$this->wordsArray[$word] += $value;
		}
		else
		{
			$this->wordsArray[$word] = $value;
		}
	}

	public function render()
	{
		$this->shuffleCloud();
		$return = array();
		if(empty($this->wordsArray)) {
			return array();
		}
		$maxValue = max($this->wordsArray);
		foreach ($this->wordsArray as $word => $popularity)
		{
			$wordTruncated = $word;
			if(strlen($word) > $this->truncatingLimit)
			{
				$wordTruncated = substr($word, 0, $this->truncatingLimit - 3).'...';
			}
			$percent = ($popularity / $maxValue) * 100;
			// CSS style value
			$sizeRange = $this->getClassFromPercent($percent);

			$return[$word] = array(
				'word' => $word,
				'wordTruncated' => $wordTruncated,
				'value' => $popularity,
				'size' => $sizeRange,
				'percent' => $percent,
			);
		}
		return $return;
	}
	
	/*
	 * Shuffle associated names in array
	 */
	protected function shuffleCloud()
	{
		$keys = array_keys($this->wordsArray);
		 
		shuffle($keys);
		 
		if (count($keys) && is_array($keys))
		{
			$tmpArray = $this->wordsArray;
			$this->wordsArray = array();
			foreach ($keys as $key => $value)
			$this->wordsArray[$value] = $tmpArray[$value];
		}
	}
	 
	/*
	 * Get the class range using a percentage
	 *
	 * @returns int $class
	 */	 
	protected function getClassFromPercent($percent)
	{
		$mapping = array(95, 70, 50, 30, 15, 5, 0);
		foreach($mapping as $key => $value)
		{
			if($percent >= $value)
			{
				return $key;
			}
		}
	}
}
