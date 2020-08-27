<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;
use Piwik\Db;

/**
 * @see Factory::boundSql()
 * @ignore
 */
class BoundSql extends Sql
{
    /**
     * @var array
     */
    private $bind;

    /**
     * BoundSql constructor.
     * @param string $sql
     * @param array $bind
     * @param int|int[] $errorCodesToIgnore
     */
    public function __construct($sql, $bind, $errorCodesToIgnore)
    {
        parent::__construct($sql, $errorCodesToIgnore);
        $this->bind = (array) $bind;
    }

    public function __toString()
    {
        $sql = parent::__toString();

        foreach ($this->bind as $value) {
            if (!is_int($value) && !is_float($value)) {
                $value = "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
            }
            $sql = substr_replace($sql, $value, $pos = strpos($sql, '?'), $len = 1);
        }

        return $sql;
    }

    public function exec()
    {
        Db::query($this->sql, $this->bind);
    }
}
