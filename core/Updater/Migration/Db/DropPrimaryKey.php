<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;


/**
 * @see Factory::dropPrimaryKey()
 * @ignore
 */
class DropPrimaryKey extends Sql
{
    /**
     * @param string $table Prefixed table name
     */
    public function __construct($table)
    {
        $sql = sprintf('ALTER TABLE `%s` DROP PRIMARY KEY', $table);

        parent::__construct($sql, array(static::ERROR_CODE_COLUMN_NOT_EXISTS));
    }

}
