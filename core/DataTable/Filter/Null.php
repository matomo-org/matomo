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
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Filter;

/**
 * Filter template.
 * You can use it if you want to create a new filter.
 *
 * @package Piwik
 * @subpackage DataTable
 */
class Null extends Filter
{
    /**
     * @param DataTable $table
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $key => $row) {
        }
    }
}
