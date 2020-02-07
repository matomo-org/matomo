<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Tracker\LogTable;

class LogTableTemporary extends LogTable
{
    private $tableName;
    public function __construct($name)
    {
        $this->tableName = $name;
    }

    public function setName($name)
    {
        $this->tableName = $name;
    }

    public function getName()
    {
        return $this->tableName;
    }

    public function getIdColumn()
    {
        return 'idvist';
    }

    public function getColumnToJoinOnIdVisit()
    {
        return 'idvisit';
    }
    public function getPrimaryKey()
    {
        return array('idvisit');
    }
}