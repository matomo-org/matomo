<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CliMulti;

use Piwik\CliMulti\Output;
use Piwik\CliMulti\OutputInterface;
use Piwik\CliMulti\StaticOutput;
use Piwik\Tests\Framework\Mock\File;
use Piwik\Url;

/**
 * @group CliMulti
 * @group OutputTest
 * @group StaticOutputTest
 */
class OutputTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Output
     */
    private $output;

    public function setUp(): void
    {
        parent::setUp();

        File::reset();
        Url::setHost(false);
    }

    public function tearDown(): void
    {
        if (is_object($this->output)) {
            $this->output->destroy();
        }

        File::reset();

        parent::tearDown();
    }

    public function outputProvider()
    {
        $this->output = new Output('myid');
        return [
            [$this->output],
            [new StaticOutput('myid')],
        ];
    }

    public function testConstructShouldFailIfInvalidOutputIdGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The given output id has an invalid format');

        new Output('../../');
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testGetOutputId($output)
    {
        $this->assertSame('myid', $output->getOutputId());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testExistsShouldReturnsFalseIfNothingWrittenYet(OutputInterface $output)
    {
        $this->assertFalse($output->exists());
    }

    public function testGetPathToFileShouldReturnFullPath()
    {
        $output = new Output('myid');
        $expectedEnd = '/climulti/myid.output';

        $this->assertStringEndsWith($expectedEnd, $output->getPathToFile());
        $this->assertGreaterThan(strlen($expectedEnd), strlen($output->getPathToFile()));
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testIsAbormalShouldReturnFalseIfFileDoesNotExist(OutputInterface $output)
    {
        $this->assertFalse($output->isAbnormal());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testIsAbormalShouldReturnTrueIfFilesizeIsNotTooBig(OutputInterface $output)
    {
        File::setFileSize(1024 * 1024 * 99);
        File::setFileExists(true);

        $this->assertFalse($output->isAbnormal());
    }

    public function testIsAbormalShouldReturnTrueIfFilesizeIsTooBig()
    {
        File::setFileSize(1024 * 1024 * 101);
        File::setFileExists(true);

        $output = new Output('myid');

        $this->assertTrue($output->isAbnormal());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testExistsShouldReturnTrueIfSomethingIsWritten(OutputInterface $output)
    {
        $output->write('test');

        $this->assertTrue($output->exists());

        $output->destroy();

        $this->assertFalse($output->exists());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testGetShouldReturnNullIfNothingWritten(OutputInterface $output)
    {
        $this->assertFalse($output->get());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testGetWriteShouldReturnTheActualOutputIfExists(OutputInterface $output)
    {
        $anyContent = 'My Actual Content';
        $output->write($anyContent);

        $this->assertEquals($anyContent, $output->get());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testWriteShouldNotAppendIfWriteIsCalledTwice(OutputInterface $output)
    {
        $anyContent = 'My Actual Content';
        $output->write($anyContent);
        $output->write($anyContent);

        $this->assertEquals($anyContent, $output->get());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testWriteShouldSaveAnEmptyStringIfContentIsNull(OutputInterface $output)
    {
        $output->write(null);

        $this->assertTrue($output->exists());
        $this->assertEquals('', $output->get());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testDestroyShouldRemoveIfAnyOutputIsWritten(OutputInterface $output)
    {
        $output->write('test');

        $this->assertTrue($output->exists());

        $output->destroy();

        $this->assertFalse($output->exists());
        $this->assertFalse($output->get());
    }

    /**
     * @dataProvider outputProvider
     * @param OutputInterface $output
     */
    public function testDestroyShouldNotFailIfNothingIsWritten(OutputInterface $output)
    {
        $output->destroy();

        $this->assertFalse($output->exists());
        $this->assertFalse($output->get());
    }

    public function testTwoDifferentOutputHandlesShouldWriteInDifferentFiles()
    {
        $output1 = new Output('id1');
        $output2 = new Output('id2');

        // cleanup possible earlier failed test runs
        $output1->destroy();
        $output2->destroy();

        $output1->write('test 1');
        $this->assertTrue($output1->exists());
        $this->assertFalse($output2->exists());
        $output2->write('test 2');

        $this->assertEquals('test 1', $output1->get());
        $this->assertEquals('test 2', $output2->get());

        $output1->destroy();
        $this->assertFalse($output1->exists());
        $this->assertTrue($output2->exists());
        $output2->destroy();

        $this->assertFalse($output2->exists());
    }
}
