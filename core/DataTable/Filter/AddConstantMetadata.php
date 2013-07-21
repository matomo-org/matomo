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
 * Add a new metadata column to the table.
 *
 * This is used to add a column containing the logo width and height of the countries flag icons.
 * This value is fixed for all icons so we simply add the same value for all rows.
 *
 * @package Piwik
 * @subpackage DataTable
 */
class AddConstantMetadata extends Filter
{
    /**
     * Creates a new filter and sets all required parameters
     *
     * @param DataTable $table
     * @param string $metadataName
     * @param mixed $metadataValue
     */
    public function __construct($table, $metadataName, $metadataValue)
    {
        parent::__construct($table);
        $this->name = $metadataName;
        $this->value = $metadataValue;
    }

    /**
     * Filters the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
            $row->addMetadata($this->name, $this->value);
        }
    }
}
