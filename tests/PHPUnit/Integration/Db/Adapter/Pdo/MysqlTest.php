<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db\Adapter\Pdo;

use Piwik\Db\Adapter\Pdo\Mysql;
use Exception;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class MysqlTest extends IntegrationTestCase
{
    public function test_isPdoErrorNumber()
    {
        $e = new Exception('Error query: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry');
        $this->assertTrue(Mysql::isPdoErrorNumber($e, 1062));
        $this->assertTrue(Mysql::isPdoErrorNumber($e, '1062'));

        $this->assertFalse(Mysql::isPdoErrorNumber($e, '2300'));
        $this->assertFalse(Mysql::isPdoErrorNumber($e, '23000'));
    }
}
