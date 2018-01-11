<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addIndex()
 * @ignore
 */
class AddIndex extends Sql
{
    protected $indexType = 'INDEX';
    protected $indexNamePrefix = 'index';

    /**
     * AddIndex constructor.
     * @param string $table
     * @param array $columnNames
     * @param string $indexName
     */
    public function __construct($table, $columnNames, $indexName)
    {
        $columns = array();
        $columnNamesOnly = array();
        foreach ($columnNames as $columnName) {
            $columnName = str_replace(' ', '', $columnName); // eg "column_name (10)" => "column_name(10)"
            preg_match('/^([\w]+)(\(?\d*\)?)$/', $columnName, $matches); // match "column_name" and "column_name(10)"

            $nameOnly = $matches[1]; // eg "column_name"
            $columnNamesOnly[] = $nameOnly;
            $column = "`$nameOnly`";
            if (!empty($matches[2])) {
                $column .= ' ' . $matches[2]; // eg "(10)"
            }

            $columns[] = $column;
        }

        if (empty($indexName)) {
            $indexName = $this->indexNamePrefix . '_' . implode('_', $columnNamesOnly);
        }

        $sql = sprintf("ALTER TABLE `%s` ADD %s %s (%s)", $table, $this->indexType, $indexName, implode(', ', $columns));

        parent::__construct($sql, array(static::ERROR_CODE_DUPLICATE_KEY, static::ERROR_CODE_KEY_COLUMN_NOT_EXISTS));
    }

}
