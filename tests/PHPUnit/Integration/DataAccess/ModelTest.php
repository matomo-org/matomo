<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\DataAccess\Model;

/**
 * @group Core
 * @group DataAccess
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;
    private $tableName = 'archive_numeric_test';

    public function setUp()
    {
        parent::setUp();

        $this->model = new Model();
        $this->model->createArchiveTable($this->tableName, 'archive_numeric');
    }

    public function test_insertNewArchiveId()
    {
        $this->assertAllocatedArchiveId(1);
        $this->assertAllocatedArchiveId(2);
        $this->assertAllocatedArchiveId(3);
        $this->assertAllocatedArchiveId(4);
        $this->assertAllocatedArchiveId(5);
        $this->assertAllocatedArchiveId(6);
    }

    private function assertAllocatedArchiveId($expectedId)
    {
        $id = $this->model->allocateNewArchiveId($this->tableName);

        $this->assertEquals($expectedId, $id);
    }
}
