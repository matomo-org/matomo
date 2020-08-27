<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addUniqueKey()
 * @ignore
 */
class AddUniqueKey extends AddIndex
{
    protected $indexType = 'UNIQUE KEY';
    protected $indexNamePrefix = 'unique';

    /**
     * AddUniqueKey constructor.
     * @param string $table
     * @param array $columnNames
     * @param string $indexName
     */
    public function __construct($table, $columnNames, $indexName)
    {
        parent::__construct($table, $columnNames, $indexName);
    }

}
