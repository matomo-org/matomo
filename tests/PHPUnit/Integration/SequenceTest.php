<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Sequence;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Sequence
 */
class SequenceTest extends IntegrationTestCase
{
    public function testCreateShouldAddNewSequenceWithInitialId1()
    {
        $sequence = $this->getEmptySequence();

        $id = $sequence->create();
        $this->assertSame(0, $id);

        // verify
        $id = $sequence->getCurrentId();
        $this->assertSame(0, $id);
    }

    public function testCreateWithCustomInitialValue()
    {
        $sequence = $this->getEmptySequence();

        $id = $sequence->create(11);
        $this->assertSame(11, $id);

        // verify
        $id = $sequence->getCurrentId();
        $this->assertSame(11, $id);
    }

    public function testCreateShouldFailIfSequenceAlreadyExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Duplicate entry');

        $sequence = $this->getExistingSequence();

        $sequence->create();
    }

    public function testGetNextIdShouldGenerateNextId()
    {
        $sequence = $this->getExistingSequence();

        $this->assertNextIdGenerated($sequence, 1);
        $this->assertNextIdGenerated($sequence, 2);
        $this->assertNextIdGenerated($sequence, 3);
    }

    public function testGetNextIdShouldFailIfThereIsNoSequenceHavingThisName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sequence \'notCreatedSequence\' not found');

        $sequence = $this->getEmptySequence();
        $sequence->getNextId();
    }

    public function testGetCurrentIdShouldReturnTheCurrentIdAsInt()
    {
        $sequence = $this->getExistingSequence();

        $id = $sequence->getCurrentId();
        $this->assertSame(0, $id);
    }

    public function testGetCurrentIdShouldReturnNullIfSequenceDoesNotExist()
    {
        $sequence = $this->getEmptySequence();
        $id = $sequence->getCurrentId();
        $this->assertNull($id);
    }

    public function testExistsShouldReturnTrueIfSequenceExist()
    {
        $sequence = $this->getExistingSequence();
        $this->assertTrue($sequence->exists());
    }

    public function testExistsShouldReturnFalseIfSequenceExist()
    {
        $sequence = $this->getEmptySequence();
        $this->assertFalse($sequence->exists());
    }

    private function assertNextIdGenerated(Sequence $sequence, $expectedId)
    {
        $id = $sequence->getNextId();
        $this->assertSame($expectedId, $id);

        // verify
        $id = $sequence->getCurrentId();
        $this->assertSame($expectedId, $id);
    }

    private function getEmptySequence()
    {
        return new Sequence('notCreatedSequence');
    }

    private function getExistingSequence()
    {
        $sequence = new Sequence('mySequence0815');
        $sequence->create();

        return $sequence;
    }
}
