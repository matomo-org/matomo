<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration\Dao;

use Piwik\Container\StaticContainer;
use Piwik\DbHelper;
use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group RecoveryCodeDaoTest
 * @group Plugins
 */
class RecoveryCodeDaoTest extends IntegrationTestCase
{
    /**
     * @var RecoveryCodeDao
     */
    private $dao;

    public function setUp(): void
    {
        parent::setUp();

        $this->dao = StaticContainer::get(RecoveryCodeDao::class);
    }

    public function testShouldInstallTable()
    {
        $columns = DbHelper::getTableColumns($this->dao->getPrefixedTableName());
        $columns = array_keys($columns);

        $this->assertEquals(['idrecoverycode', 'login', 'recovery_code'], $columns);
    }

    public function testGetAllRecoveryCodesForLoginEmptyByDefault()
    {
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('login1'));
    }

    public function testInsertRecoveryCodeGetAllRecoveryCodesForLogin()
    {
        $this->dao->insertRecoveryCode('login1', '123456');
        $this->dao->insertRecoveryCode('login1', '654321');
        $this->dao->insertRecoveryCode('login2', '333111');
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['333111'], $this->dao->getAllRecoveryCodesForLogin('login2'));
    }

    public function testDeleteRecoveryCode()
    {
        $this->insertManyCodesDifferentLogins();
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->assertEquals(1, $this->dao->deleteRecoveryCode('login2', '654321')); // this one should be deleted
        $this->assertEquals(0, $this->dao->deleteRecoveryCode('login2', 'xya123')); // cannot be found
        $this->assertEquals(0, $this->dao->deleteRecoveryCode('login999', '123456')); // cannot be found

        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->dao->deleteRecoveryCode('login2', '123456'); // delete last code for this login
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->assertEquals(0, $this->dao->deleteRecoveryCode('login2', '654321')); // cannot be deleted again
    }

    public function testDeleteAllRecoveryCodesForLogin()
    {
        $this->insertManyCodesDifferentLogins();
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->dao->deleteAllRecoveryCodesForLogin('login2'); // this one should be deleted
        $this->dao->deleteAllRecoveryCodesForLogin('login999'); // login cannot be found

        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('login2'));
    }

    public function testUseRecoveryCode()
    {
        $this->insertManyCodesDifferentLogins();
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->assertTrue($this->dao->useRecoveryCode('login2', '654321')); // this one should be used and deleted

        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->assertFalse($this->dao->useRecoveryCode('login2', '654321')); // cannot be used again
        $this->assertFalse($this->dao->useRecoveryCode('login2', 'xya123')); // cannot be found
        $this->assertFalse($this->dao->useRecoveryCode('login999', '123456')); // cannot be found

        $this->assertEquals(['123456', '654321'], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->assertEquals(['123456'], $this->dao->getAllRecoveryCodesForLogin('login2'));

        $this->assertTrue($this->dao->useRecoveryCode('login2', '123456')); // cannot be used again
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('login2'));
    }

    public function testCreateRecoveryCodesForLogin()
    {
        $this->assertEquals([], $this->dao->getAllRecoveryCodesForLogin('login1'));
        $this->dao->createRecoveryCodesForLogin('login1');

        $codes1 = $this->dao->getAllRecoveryCodesForLogin('login1');
        $this->assertCount(10, $codes1);

        // generating new codes will remove the old codes
        $this->dao->createRecoveryCodesForLogin('login1');

        $codes2 = $this->dao->getAllRecoveryCodesForLogin('login1');
        $this->assertCount(10, $codes2);

        // not the same
        $this->assertCount(10, array_diff($codes1, $codes2));
        foreach ($codes1 as $code) {
            // none of the old codes can be used
            $this->assertFalse($this->dao->useRecoveryCode('login1', $code));
        }
        foreach ($codes2 as $code) {
            // all new codes can be used
            $this->assertTrue($this->dao->useRecoveryCode('login1', $code));
        }
    }

    public function testCreateRecoveryCodesForLoginDifferentPerLogin()
    {
        $this->dao->createRecoveryCodesForLogin('login1');
        $this->dao->createRecoveryCodesForLogin('login2');

        $codes1 = $this->dao->getAllRecoveryCodesForLogin('login1');
        $codes2 = $this->dao->getAllRecoveryCodesForLogin('login2');

        // not the same
        $this->assertCount(10, array_diff($codes1, $codes2));

        foreach ($codes1 as $code) {
            // all new codes can be used
            $this->assertTrue($this->dao->useRecoveryCode('login1', $code));
        }
        foreach ($codes2 as $code) {
            // all new codes can be used
            $this->assertTrue($this->dao->useRecoveryCode('login2', $code));
        }
    }

    private function insertManyCodesDifferentLogins()
    {
        $this->dao->insertRecoveryCode('login1', '123456');
        $this->dao->insertRecoveryCode('login1', '654321');
        $this->dao->insertRecoveryCode('login2', '123456');
        $this->dao->insertRecoveryCode('login2', '654321');
    }
}
