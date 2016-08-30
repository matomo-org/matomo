<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\Integration;

use Piwik\Plugins\CustomPiwikJs\File;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomPiwikJs
 * @group FileTest
 * @group File
 * @group Plugins
 */
class FileTest extends IntegrationTestCase
{
    const NOT_EXISTING_FILE = 'notExisTinGFile.js';

    /**
     * @var string
     */
    private $dir = '';

    public function setUp()
    {
        parent::setUp();
        $this->dir = PIWIK_DOCUMENT_ROOT . '/plugins/CustomPiwikJs/tests/resources/';
    }

    public function tearDown()
    {
        if (file_exists($this->dir . self::NOT_EXISTING_FILE)) {
            unlink($this->dir . self::NOT_EXISTING_FILE);
        }

        parent::tearDown();
    }

    private function makeFile($fileName = 'test.js')
    {
        return new File($this->dir . $fileName);
    }

    private function makeNotReadableFile()
    {
        return $this->makeFile(self::NOT_EXISTING_FILE);
    }

    public function test_getName()
    {
        $this->assertSame('test.js', $this->makeFile()->getName());
        $this->assertSame('notExisTinGFile.js', $this->makeNotReadableFile()->getName());
    }

    public function test_hasReadAccess()
    {
        $this->assertTrue($this->makeFile()->hasReadAccess());
        $this->assertFalse($this->makeNotReadableFile()->hasReadAccess());
    }

    public function test_hasWriteAccess()
    {
        $this->assertTrue($this->makeFile()->hasWriteAccess());
        $this->assertFalse($this->makeNotReadableFile()->hasWriteAccess());
    }

    public function test_checkReadable_shouldNotThrowException_IfIsReadable()
    {
        $this->makeFile()->checkReadable();
        $this->assertTrue(true);
    }

    public function test_checkWritable_shouldNotThrowException_IfIsWritable()
    {
        $this->makeFile()->checkWritable();
        $this->assertTrue(true);
    }

    /**
     * @expectedException \Piwik\Plugins\CustomPiwikJs\Exception\AccessDeniedException
     * @expectedExceptionMessage not readable
     */
    public function test_checkReadable_shouldThrowException_IfNotIsReadable()
    {
        $this->makeNotReadableFile()->checkReadable();
    }

    /**
     * @expectedException \Piwik\Plugins\CustomPiwikJs\Exception\AccessDeniedException
     * @expectedExceptionMessage not writable
     */
    public function test_checkWritable_shouldThrowException_IfNotIsWritable()
    {
        $this->makeNotReadableFile()->checkWritable();
    }

    public function test_getContent()
    {
        $this->assertSame("// Hello world\nvar fooBar = 'test';", $this->makeFile()->getContent());
    }

    public function test_getContent_returnsNull_IfFileIsNotReadableOrNotExists()
    {
        $this->assertNull($this->makeNotReadableFile()->getContent());
    }

    public function test_save()
    {
        $notExistingFile = $this->makeNotReadableFile();
        $this->assertFalse($notExistingFile->hasReadAccess());
        $this->assertFalse($notExistingFile->hasWriteAccess());

        $notExistingFile->save('myTestContent');

        $this->assertEquals('myTestContent', $notExistingFile->getContent());
        $this->assertTrue($notExistingFile->hasReadAccess());
        $this->assertTrue($notExistingFile->hasWriteAccess());
    }

}
