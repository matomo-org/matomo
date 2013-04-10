<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Generates a tag cloud from a given data array.
 * The generated tag cloud can be in PHP format, or in HTML.
 *
 * Inspired from Derek Harvey (www.derekharvey.co.uk)
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Piwik_Visualization_Cloud implements Piwik_View_Interface
{
    /** Used by integration tests to make sure output is consistent. */
    public static $debugDisableShuffle = false;

    protected $wordsArray = array();
    public $truncatingLimit = 50;

    /**
     * Assign word to array
     * @param string $word
     * @param int $value
     * @return string
     */
    function addWord($word, $value = 1)
    {
        if (isset($this->wordsArray[$word])) {
            $this->wordsArray[$word] += $value;
        } else {
            $this->wordsArray[$word] = $value;
        }
    }

    public function render()
    {
        $this->shuffleCloud();
        $return = array();
        if (empty($this->wordsArray)) {
            return array();
        }
        $maxValue = max($this->wordsArray);
        foreach ($this->wordsArray as $word => $popularity) {
            $wordTruncated = $word;
            if (Piwik_Common::mb_strlen($word) > $this->truncatingLimit) {
                $wordTruncated = Piwik_Common::mb_substr($word, 0, $this->truncatingLimit - 3) . '...';
            }

            // case hideFutureHoursWhenToday=1 shows hours with no visits
            if ($maxValue == 0) {
                $percent = 0;
            } else {
                $percent = ($popularity / $maxValue) * 100;
            }
            // CSS style value
            $sizeRange = $this->getClassFromPercent($percent);

            $return[$word] = array(
                'word'          => $word,
                'wordTruncated' => $wordTruncated,
                'value'         => $popularity,
                'size'          => $sizeRange,
                'percent'       => $percent,
            );
        }
        return $return;
    }

    /**
     * Shuffle associated names in array
     */
    protected function shuffleCloud()
    {
        if (self::$debugDisableShuffle) {
            return;
        }

        $keys = array_keys($this->wordsArray);

        shuffle($keys);

        if (count($keys) && is_array($keys)) {
            $tmpArray = $this->wordsArray;
            $this->wordsArray = array();
            foreach ($keys as $key => $value)
                $this->wordsArray[$value] = $tmpArray[$value];
        }
    }

    /**
     * Get the class range using a percentage
     *
     * @param $percent
     *
     * @return int class
     */
    protected function getClassFromPercent($percent)
    {
        $mapping = array(95, 70, 50, 30, 15, 5, 0);
        foreach ($mapping as $key => $value) {
            if ($percent >= $value) {
                return $key;
            }
        }
    }
}
