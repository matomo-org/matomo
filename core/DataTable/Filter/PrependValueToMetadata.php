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
 * Executes a callback for each row of a {@link DataTable} and prepends the given value to each metadata entry
 * but only if the given metadata entry exists.
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('PrependValueToMetadata', array('segment', 'segmentName==segmentValue'));
 *
 * @api
 */
class PrependValueToMetadata extends BaseFilter
{
    private $metadataColumn;
    private $valueToPrepend;

    /**
     * @param DataTable $table
     * @param string $metadataName    The name of the metadata that should be prepended
     * @param string $valueToPrepend  The value to prepend if the metadata entry exists
     */
    public function __construct($table, $metadataName, $valueToPrepend)
    {
        parent::__construct($table);

        $this->metadataColumn = $metadataName;
        $this->valueToPrepend = $valueToPrepend;
    }

    /**
     * See {@link PrependValueToMetadata}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if (empty($this->metadataColumn) || empty($this->valueToPrepend)) {
            return;
        }

        $metadataColumn = $this->metadataColumn;
        $valueToPrepend = $this->valueToPrepend;

        $table->filter(function (DataTable $dataTable) use ($metadataColumn, $valueToPrepend) {
            foreach ($dataTable->getRows() as $row) {
                $filter = $row->getMetadata($metadataColumn);
                if ($filter !== false) {
                    $row->setMetadata($metadataColumn, $valueToPrepend . $filter);
                }
            }
        });
    }
}
