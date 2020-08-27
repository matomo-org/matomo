<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::dropTable()
 * @ignore
 */
class DropTable extends Sql
{
    /**
     * @param string $table Prefixed table name
     */
    public function __construct($table)
    {
        $sql = sprintf('DROP TABLE IF EXISTS `%s`', $table);

        parent::__construct($sql, array(static::ERROR_CODE_TABLE_NOT_EXISTS, static::ERROR_CODE_UNKNOWN_TABLE));
    }

}
