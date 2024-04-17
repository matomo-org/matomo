<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db;

use Piwik\Common;
use Piwik\Db;
use Piwik\Db\BatchInsert;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class BatchInsertTest extends IntegrationTestCase
{
    public function test_tableInsertBatchSql()
    {
        $access = Common::prefixTable('access');
        $fields = array('login', 'idsite', 'access', 'idaccess');

        $insertValues = array(
            array('foo', '1', 'view', 1),
            array('foo', '1', 'view', 2), // duplicate
            array('foo', '2', 'view', 3),
            array('bar', '1', 'write', 4),
            array('foo', '2', 'admin', 5),
            array('baz', '1', 'view', 6),
        );
        BatchInsert::tableInsertBatchSql($access, $fields, $insertValues);

        $all = Db::fetchAll('SELECT * FROM ' . $access);
        $this->assertEquals(array(
            array(
                'idaccess' => '1',
                'login' => 'foo',
                'idsite' => '1',
                'access' => 'view',
            ),
            array(
                'idaccess' => '2',
                'login' => 'foo',
                'idsite' => '1',
                'access' => 'view',
            ),
            array(
                'idaccess' => '3',
                'login' => 'foo',
                'idsite' => '2',
                'access' => 'view',
            ),
            array(
                'idaccess' => '4',
                'login' => 'bar',
                'idsite' => '1',
                'access' => 'write',
            ),
            array(
                'idaccess' => '5',
                'login' => 'foo',
                'idsite' => '2',
                'access' => 'admin',
            ),
            5 =>
                array(
                    'idaccess' => '6',
                    'login' => 'baz',
                    'idsite' => '1',
                    'access' => 'view',
                ),
        ), $all);
    }
}
