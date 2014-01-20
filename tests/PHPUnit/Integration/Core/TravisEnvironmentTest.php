<?php

use Piwik\Translate;

/**
 * Class TravisEnvironmentTest
 *
 * @group Core
 */
class TravisEnvironmentTest extends DatabaseTestCase
{
    /**
     * @group Core
     */
    public function testUsageOfCorrectMysqlAdapter()
    {
        $mysqlAdapter = getenv('MYSQL_ADAPTER');

        if (empty($mysqlAdapter)) {
            return;
        }

        $this->assertTrue(in_array($mysqlAdapter, array('PDO_MYSQL', 'MYSQLI')));

        $db = Piwik\Db::get();

        switch ($mysqlAdapter) {
            case 'PDO_MYSQL':
                $this->assertInstanceOf('\Piwik\Db\Adapter\Pdo\Mysql', $db);
                break;
            case 'MYSQLI':
                $this->assertInstanceOf('\Piwik\Db\Adapter\Mysqli', $db);
                break;
        }

    }
}