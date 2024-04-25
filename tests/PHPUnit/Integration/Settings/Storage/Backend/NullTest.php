<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Settings\Storage\Backend\NullBackend;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class NullTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Null
     */
    private $backend;

    public function setUp(): void
    {
        parent::setUp();

        $this->backend = new NullBackend('storageId1FooBar');
    }

    public function testGetStorageIdShouldReturnStorageId()
    {
        $this->assertSame('storageId1FooBar', $this->backend->getStorageId());
    }

    public function testSaveLoadShouldNotSaveAnything()
    {
        $this->assertSame(array(), $this->backend->load());
        $this->backend->save(array('foo' => 'bar'));
        $this->assertSame(array(), $this->backend->load());
    }
}
