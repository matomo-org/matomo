<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Db;
use Piwik\Sequence;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Core_SequenceTest
 *
 * @group Core
 * @group Sequence
 */
class Core_SequenceTest extends IntegrationTestCase
{
    /**
     * @var Sequence
     */
    private $sequence;

    public function setUp()
    {
        parent::setUp();

        $this->sequence = new Sequence('mySequence0815');
        $this->sequence->create();
    }

    public function test_create_shouldAddNewSequenceWithInitalId1()
    {
        $sequence = $this->getEmptySequence();

        $id = $sequence->create();
        $this->assertSame(0, $id);

        // verify
        $id = $sequence->getCurrentId();
        $this->assertSame(0, $id);
    }

    public function test_create_WithCustomInitialValue()
    {
        $sequence = $this->getEmptySequence();

        $id = $sequence->create(11);
        $this->assertSame(11, $id);

        // verify
        $id = $sequence->getCurrentId();
        $this->assertSame(11, $id);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Duplicate entry
     */
    public function test_create_shouldFailIfSequenceAlreadyExists()
    {
        $this->sequence->create();
    }

    public function test_getNextId_shouldGenerateNextId()
    {
        $this->assertNextIdGenerated(1);
        $this->assertNextIdGenerated(2);
        $this->assertNextIdGenerated(3);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Sequence 'notCreatedSequence' not found
     */
    public function test_getNextId_shouldFailIfThereIsNoSequenceHavingThisName()
    {
        $sequence = $this->getEmptySequence();
        $sequence->getNextId();
    }

    private function assertNextIdGenerated($expectedId)
    {
        $id = $this->sequence->getNextId();
        $this->assertSame($expectedId, $id);

        // verify
        $id = $this->sequence->getCurrentId();
        $this->assertSame($expectedId, $id);
    }

    public function test_getCurrentId_shouldReturnTheCurrentIdAsInt()
    {
        $id = $this->sequence->getCurrentId();
        $this->assertSame(0, $id);
    }

    public function test_getCurrentId_shouldReturnNullIfSequenceDoesNotExist()
    {
        $sequence = $this->getEmptySequence();
        $id = $sequence->getCurrentId();
        $this->assertNull($id);
    }

    private function getEmptySequence()
    {
        return new Sequence('notCreatedSequence');
    }


}