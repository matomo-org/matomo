<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        $this->table = $table;
        $this->discriminatorColumn = $discriminatorColumn;
        $this->discriminatorValue = $discriminatorValue;

        if (!$this->isValid()) {
            // if adding another string value please post an event instead to get a list of allowed values
            throw new Exception('$discriminatorValue needs to be null or numeric');
        }
    }

    public function isValid()
    {
        return isset($this->discriminatorColumn) && is_numeric($this->discriminatorValue);
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
