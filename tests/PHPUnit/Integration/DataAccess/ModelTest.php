<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\DataAccess\Model;

/**
 * @group Core
 * @group DataAccess
 */
class Core_DataAccess_ModelTest extends IntegrationTestCase
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
        $this->assertCreatedArchiveId(1);
        $this->assertCreatedArchiveId(2);
        $this->assertCreatedArchiveId(3);
        $this->assertCreatedArchiveId(4);
        $this->assertCreatedArchiveId(5, 2);
        $this->assertCreatedArchiveId(6, 2);
    }

    private function assertCreatedArchiveId($expectedId, $siteId = 1)
    {
        $id = $this->model->insertNewArchiveId($this->tableName, $siteId, '2014-01-01 00:01:02');

        $this->assertEquals($expectedId, $id);
    }

}