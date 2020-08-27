<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db;

use Piwik\Db;
use Piwik\Db\TransactionLevel;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Funnels
 * @group TransactionLevelTest
 * @group TransactionLevel
 * @group Plugins
 */
class TransactionLevelTest extends IntegrationTestCase
{
	/**
	 * @var TransactionLevel
	 */
	private $level;

	/**
	 * @var \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db $db
	 */
	private $db;

	public function setUp(): void
	{
		parent::setUp();
		$this->db = Db::get();
		$this->level = new TransactionLevel($this->db);
	}

	public function test_canLikelySetTransactionLevel()
	{
		$this->assertTrue($this->level->canLikelySetTransactionLevel());
	}

	public function test_setUncommitted_restorePreviousStatus()
	{
		$value = $this->db->fetchOne('SELECT @@TX_ISOLATION');
		$this->assertSame('REPEATABLE-READ', $value);

		$this->level->setUncommitted();
		$value = $this->db->fetchOne('SELECT @@TX_ISOLATION');

		$this->assertSame('READ-UNCOMMITTED', $value);
		$this->level->restorePreviousStatus();

		$value = $this->db->fetchOne('SELECT @@TX_ISOLATION');
		$this->assertSame('REPEATABLE-READ', $value);
	}

}
