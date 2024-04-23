<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db\Adapter;

use Piwik\Db\Adapter\Mysqli;
use Exception;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class MysqliTest extends IntegrationTestCase
{
    public function testIsMysqliErrorNumberWhenNoConnectionIsSet()
    {
        $e = new Exception('Error query: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry');
        $connection = null;
        $this->assertTrue(Mysqli::isMysqliErrorNumber($e, $connection, 1062));
        $this->assertTrue(Mysqli::isMysqliErrorNumber($e, $connection, '1062'));

        $this->assertFalse(Mysqli::isMysqliErrorNumber($e, $connection, '2300'));
        $this->assertFalse(Mysqli::isMysqliErrorNumber($e, $connection, '23000'));
    }
}
