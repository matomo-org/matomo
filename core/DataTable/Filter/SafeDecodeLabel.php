<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * Sanitizes DataTable labels as an extra precaution. Called internally by Piwik.
 *
 */
class SafeDecodeLabel extends BaseFilter
{
    const APPLIED_METADATA_NAME = 'SafeDecodeLabelApplied';

    private $columnToDecode;

    /**
     * @param DataTable $table
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
     * @return string
     */
    public static function decodeLabelSafe($value)
    {
        if (empty($value)) {
            return $value;
        }
        $raw = urldecode($value);
        $value = htmlspecialchars_decode($raw, ENT_QUOTES);

        // ENT_IGNORE so that if utf8 string has some errors, we simply discard invalid code unit sequences
        $style = ENT_QUOTES | ENT_IGNORE;

        // See changes in 5.4: http://nikic.github.com/2012/01/28/htmlspecialchars-improvements-in-PHP-5-4.html
        // Note: at some point we should change ENT_IGNORE to ENT_SUBSTITUTE
        $value = htmlspecialchars($value, $style, 'UTF-8');

        return $value;
    }

    /**
     * Decodes all columns of the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if ($table->getMetadata(self::APPLIED_METADATA_NAME)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            $value = $row->getColumn($this->columnToDecode);
            if ($value !== false) {
                $value = self::decodeLabelSafe($value);
                $row->setColumn($this->columnToDecode, $value);

                $this->filterSubTable($row);
            }
        }
    }
}
