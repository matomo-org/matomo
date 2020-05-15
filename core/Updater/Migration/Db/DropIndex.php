<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::dropIndex()
 * @ignore
 */
class DropIndex extends Sql
{
    /**
     * @param string $table Prefixed table name
     * @param string $indexName name of the index
     */
    public function __construct($table, $indexName)
    {
        $sql = sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName);

        parent::__construct($sql, array(static::ERROR_CODE_COLUMN_NOT_EXISTS));
    }

}
