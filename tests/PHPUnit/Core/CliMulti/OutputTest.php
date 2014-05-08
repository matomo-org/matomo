<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\CliMulti\Output;

/**
 * Class OutputTest
 * @group Core
 */
class OutputTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Output
     */
    private $output;

    public function setUp()
    {
        \Piwik\Url::setHost(false);
        $this->output = new Output('myid');
    }

    public function tearDown()
    {
        $this->output->destroy();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The given output id has an invalid format
     */
    public function test_construct_shouldFail_IfInvalidOutputIdGiven()
    {
        new Output('../../');
    }

    public function test_exists_ShouldReturnsFalse_IfNothingWrittenYet()
    {
        $this->assertFalse($this->output->exists());
    }

    public function test_getPathToFile_shouldReturnFullPath()
    {
        $expectedEnd = '/tmp/climulti/myid.output';

        $this->assertStringEndsWith($expectedEnd, $this->output->getPathToFile());
        $this->assertGreaterThan(strlen($expectedEnd), strlen($this->output->getPathToFile()));
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