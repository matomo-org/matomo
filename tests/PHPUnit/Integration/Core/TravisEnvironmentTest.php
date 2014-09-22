<?php

use Piwik\Translate;

/**
 * Class TravisEnvironmentTest
 *
 * @group Core
 */
class Core_TravisEnvironmentTest extends DatabaseTestCase
{
    public function testUsageOfCorrectMysqlAdapter()
    {
        $mysqlAdapter = getenv('MYSQL_ADAPTER');

        if (empty($mysqlAdapter)) {
            return;
        }

        $this->assertTrue(in_array($mysqlAdapter, array('PDO_MYSQL', 'PDO\MYSQL', 'MYSQLI')));

        $db = Piwik\Db::get();

        switch ($mysqlAdapter) {
            case 'PDO_MYSQL':
            case 'PDO\MYSQL':
                $this->assertInstanceOf('\Piwik\Db\Adapter\Pdo\Mysql', $db);
                break;
            case 'MYSQLI':
                $this->assertInstanceOf('\Piwik\Db\Adapter\Mysqli', $db);
                break;
        }

    }
}