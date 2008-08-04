<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Cloud.php 444 2008-04-11 13:38:22Z johmathe $
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
	
	/**
	 * @param array array( word => 10, word2 => 50, word3 => 1)
	 */
	function __construct($words = false)
	{
		if ($words !== false && is_array($words))
		{
			foreach ($words as $word => $value)
			{
				$this->addWord($word, $value);
			}
		}
	}
	 
	/*
	 * Assign word to array
	 *
	 * @param string $word
	 * @return string
	 */
	function addWord($word, $value = 1)
	{
		//        $word = strtolower($word);
		if (isset($this->wordsArray[$word]))
		{
			$this->wordsArray[$word] += $value;
		}
		else
		{
			$this->wordsArray[$word] = $value;
		}
	}
	 
	/*
	 * Shuffle associated names in array
	 */
	function shuffleCloud()
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
	 * Calculate size of words array
	 */
	 
	function getCloudSize()
	{
		return array_sum($this->wordsArray);
	}
	 
	/*
	 * Get the class range using a percentage
	 *
	 * @returns int $class
	 */	 
	function getClassFromPercent($percent)
	{
		$mapping = array(
		95,
		70,
		50,
		30,
		15,
		5,
		0
		);
		foreach($mapping as $key => $value)
		{
			if($percent >= $value)
			{
				return $key;
			}
		}
	}
	 
	/*
	 * Create the HTML code for each word and apply font size.
	 *
	 * @returns string $spans
	 */
	 
	function render($returnType = "html")
	{
		$this->shuffleCloud();

		if($returnType == "html")
		{
			$return = '';
		}
		else
		{
			$return = array();
		}

		if (count($this->wordsArray) > 0)
		{
			$this->max = max($this->wordsArray);

			$return = ($returnType == "html" ? "" : ($returnType == "array" ? array() : ""));
			foreach ($this->wordsArray as $word => $popularity)
			{
				 
				// truncating the word
				$wordTruncated = $word;
				if(strlen($word) > $this->truncatingLimit)
				{
					$wordTruncated = substr($word, 0, $this->truncatingLimit - 3).'...';
				}
				 
				// computing the percentage
				$percent = ($popularity / $this->max) * 100;

				// and the CSS style value
				$sizeRange = $this->getClassFromPercent($percent);

				if ($returnType == "array")
				{
					$return[$word]['word'] = $word;
					$return[$word]['wordTruncated'] = $wordTruncated;
					$return[$word]['size'] = $sizeRange;
					$return[$word]['percent'] = $percent;
				}
				else if ($returnType == "html")
				{
					$return .= "\n<span title='".$word."' class='word size{$sizeRange}'> &nbsp; {$wordTruncated} &nbsp; </span>";
				}
				//            	print( $word ."=".$percent."<br>");
			}
		}
		return $return;
	}
}

