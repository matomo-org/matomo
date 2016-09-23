<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Config;
use Piwik\Db;
use Piwik\Settings\Storage\Backend\NullBackend;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class NullTest extends IntegrationTestCase
{

    /**
     * @var Null
     */
    private $backend;

    public function setUp()
    {
        parent::setUp();

        $this->backend = new NullBackend('storageId1FooBar');
    }

    public function test_getStorageId_shouldReturnStorageId()
    {
        $this->assertSame('storageId1FooBar', $this->backend->getStorageId());
    }

    public function test_save_load_shouldNotSaveAnything()
    {
        $this->assertSame(array(), $this->backend->load());
        $this->backend->save(array('foo' => 'bar'));
        $this->assertSame(array(), $this->backend->load());
    }

}
