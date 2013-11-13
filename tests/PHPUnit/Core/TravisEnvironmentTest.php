<?php

use Piwik\Translate;

/**
 * Class TravisEnvironmentTest
 *
 * @group Core
 */
class TravisEnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testUsageOfCorrectMysqlAdapter()
    {
        $mysqlAdapter = getenv('MYSQL_ADAPTER');

        if (!empty($mysqlAdapter)) {
            $this->assertTrue(in_array($mysqlAdapter, array('PDO_MYSQL', 'MYSQLI')));
        }
    }
}