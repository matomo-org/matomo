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
 * Filter template.
 * You can use it if you want to create a new filter.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Null extends Piwik_DataTable_Filter
{
    /**
     * @param Piwik_DataTable $table
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
        }
    }
}
