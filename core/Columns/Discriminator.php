<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Exception;

/**
 * @api
 * @since 3.1.0
 */
class Discriminator
{
    private $table;
    private $discriminatorColumn;
    private $discriminatorValue;

    /**
     * Join constructor.
     * @param string $table  unprefixed table name
     * @param null|string $discriminatorColumn
     * @param null|int $discriminatorValue should be only hard coded, safe values.
     * @throws Exception
     */
    public function __construct($table, $discriminatorColumn = null, $discriminatorValue = null)
    {
        if (empty($discriminatorColumn) || !isset($discriminatorValue)) {
            throw new Exception('Both discriminatorColumn and discriminatorValue need to be defined');
        }
        if (isset($discriminatorColumn) && !is_numeric($discriminatorValue)) {
            throw new Exception('$discriminatorValue needs to be null or numeric');
        }
        $this->table = $table;
        $this->discriminatorColumn = $discriminatorColumn;
        $this->discriminatorValue = $discriminatorValue;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->discriminatorColumn;
    }

    /**
     * @return int|null
     */
    public function getValue()
    {
        return $this->discriminatorValue;
    }
}
