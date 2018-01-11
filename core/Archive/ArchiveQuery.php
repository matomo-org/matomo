<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Archive;


use Piwik\DataTable;

interface ArchiveQuery
{
    /**
     * @param string|string[] $names
     * @return false|number|array
     */
    public function getNumeric($names);

    /**
     * @param string|string[] $names
     * @return DataTable|DataTable\Map
     */
    public function getDataTableFromNumeric($names);

    /**
     * @param $names
     * @return mixed
     */
    public function getDataTableFromNumericAndMergeChildren($names);

    /**
     * @param string $name
     * @param int|string|null $idSubtable
     * @return DataTable|DataTable\Map
     */
    public function getDataTable($name, $idSubtable = null);

    /**
     * @param string $name
     * @param int|string|null $idSubtable
     * @param int|null $depth
     * @param bool $addMetadataSubtableId
     * @return DataTable|DataTable\Map
     */
    public function getDataTableExpanded($name, $idSubtable = null, $depth = null, $addMetadataSubtableId = true);
}