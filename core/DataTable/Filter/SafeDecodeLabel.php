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
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_SafeDecodeLabel extends Piwik_DataTable_Filter
{
    private $columnToDecode;
    static private $outputHtml = true;

    /**
     * @param Piwik_DataTable $table
     */
    public function __construct($table)
    {
        parent::__construct($table);
        $this->columnToDecode = 'label';
    }

    /**
     * Decodes the given value
     *
     * @param string $value
     * @return mixed|string
     */
    static public function safeDecodeLabel($value)
    {
        if (empty($value)) {
            return $value;
        }
        $raw = urldecode($value);
        $value = htmlspecialchars_decode($raw, ENT_QUOTES);
        if (self::$outputHtml) {
            // Pre 5.3
            if (!defined('ENT_IGNORE')) {
                $style = ENT_QUOTES;
            } else {
                $style = ENT_QUOTES | ENT_IGNORE;
            }
            // See changes in 5.4: http://nikic.github.com/2012/01/28/htmlspecialchars-improvements-in-PHP-5-4.html
            // Note: at some point we should change ENT_IGNORE to ENT_SUBSTITUTE
            $value = htmlspecialchars($value, $style, 'UTF-8');
        }
        return $value;
    }

    /**
     * Decodes all columns of the given data table
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
            $value = $row->getColumn($this->columnToDecode);
            if ($value !== false) {
                $value = self::safeDecodeLabel($value);
                $row->setColumn($this->columnToDecode, $value);

                $this->filterSubTable($row);
            }
        }
    }

}
