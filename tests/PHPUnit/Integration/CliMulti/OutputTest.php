<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CliMulti;

use Piwik\CliMulti\Output;
use Piwik\Tests\Framework\Mock\File;
use Piwik\Url;

/**
 * @group CliMulti
 */
class OutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Output
     */
    private $output;

    public function setUp()
    {
        parent::setUp();

        File::reset();
        Url::setHost(false);
        $this->output = new Output('myid');
    }

    public function tearDown()
    {
        if(is_object($this->output)){
            $this->output->destroy();
        }
        
        File::reset();

        parent::tearDown();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The given output id has an invalid format
     */
    public function test_construct_shouldFail_IfInvalidOutputIdGiven()
    {
        new Output('../../');
    }

    public function test_getOutputId()
    {
        $this->assertSame('myid', $this->output->getOutputId());
    }

    public function test_exists_ShouldReturnsFalse_IfNothingWrittenYet()
    {
        $this->assertFalse($this->output->exists());
    }

    public function test_getPathToFile_shouldReturnFullPath()
    {
        $expectedEnd = '/climulti/myid.output';

        $this->assertStringEndsWith($expectedEnd, $this->output->getPathToFile());
        $this->assertGreaterThan(strlen($expectedEnd), strlen($this->output->getPathToFile()));
    }

    public function test_isAbormal_ShouldReturnFalse_IfFileDoesNotExist()
    {
        $this->assertFalse($this->output->isAbnormal());
    }

    public function test_isAbormal_ShouldReturnTrue_IfFilesizeIsNotTooBig()
    {
        File::setFileSize(1024 * 1024 * 99);
        File::setFileExists(true);

        $this->assertFalse($this->output->isAbnormal());
    }

    public function test_isAbormal_ShouldReturnTrue_IfFilesizeIsTooBig()
    {
        File::setFileSize(1024 * 1024 * 101);
        File::setFileExists(true);

        $this->assertTrue($this->output->isAbnormal());
    }

    public function test_exists_ShouldReturnTrue_IfSomethingIsWritten()
    {
        $this->output->write('test');

        $this->assertTrue($this->output->exists());

        $this->output->destroy();

        $this->assertFalse($this->output->exists());
    }

    public function test_get_shouldReturnNull_IfNothingWritten()
    {
        $this->assertFalse($this->output->get());
    }

    public function test_get_write_shouldReturnTheActualOutput_IfExists()
    {
        $anyContent = 'My Actual Content';
        $this->output->write($anyContent);

        $this->assertEquals($anyContent, $this->output->get());
    }

    public function test_write_shouldNotAppend_IfWriteIsCalledTwice()
    {
        $anyContent = 'My Actual Content';
        $this->output->write($anyContent);
        $this->output->write($anyContent);

        $this->assertEquals($anyContent, $this->output->get());
    }

    public function test_write_shouldSaveAnEmptyString_IfContentIsNull()
    {
        $this->output->write(null);

        $this->assertTrue($this->output->exists());
        $this->assertEquals('', $this->output->get());
    }

    public function test_destroy_ShouldRemove_IfAnyOutputIsWritten()
    {
        $this->output->write('test');

        $this->assertTrue($this->output->exists());

        $this->output->destroy();

        $this->assertFalse($this->output->exists());
        $this->assertFalse($this->output->get());
    }

    public function test_destroy_ShouldNotFail_IfNothingIsWritten()
    {
        $this->output->destroy();

        $this->assertFalse($this->output->exists());
        $this->assertFalse($this->output->get());
    }

    public function test_twoDifferentOutputHandles_ShouldWriteInDifferentFiles()
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