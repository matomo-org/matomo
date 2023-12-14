<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\tests\Integration;

use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomTestFile extends File {
}

/**
 * @group CustomJsTracker
 * @group FileTest
 * @group File
 * @group Plugins
 */
class FileTest extends IntegrationTestCase
{
    const NOT_EXISTING_FILE_IN_WRITABLE_DIRECTORY = 'notExisTinGFile.js';
    const NOT_EXISTING_FILE_IN_NON_WRITABLE_DIRECTORY = 'is-not-writable/notExisTinGFile.js';

    /**
     * @var string
     */
    private $dir = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = PIWIK_DOCUMENT_ROOT . '/plugins/CustomJsTracker/tests/resources/';

        // make directory not writable
        $nonWritableDir = dirname($this->dir . self::NOT_EXISTING_FILE_IN_NON_WRITABLE_DIRECTORY);
        @chmod($nonWritableDir, 0444);
        if(is_writable($nonWritableDir)) {
            throw new \Exception("The directory $nonWritableDir should have been made non writable by this test, but it didn't work");
        }
    }

    public function tearDown(): void
    {
        // restore permissions changed by makeNotWritableFile()
        chmod($this->dir, 0777);

        if (file_exists($this->dir . self::NOT_EXISTING_FILE_IN_WRITABLE_DIRECTORY)) {
            unlink($this->dir . self::NOT_EXISTING_FILE_IN_WRITABLE_DIRECTORY);
        }
        parent::tearDown();
    }

    private function makeFile($fileName = 'test.js')
    {
        return new File($this->dir . $fileName);
    }

    private function makeNotWritableFile()
    {
        $path = $this->dir . 'file-made-non-writable.js';
        if(file_exists($path)) {
            chmod($path, 0777);
        }
        $file = new File($path);
        $file->save('will be saved OK, and then we make it non writable.');

        if (!chmod($path, 0444)) {
            throw new \Exception("chmod on the file didn't work");
        }
        if (!chmod(dirname($path), 0755)) {
            throw new \Exception("chmod on the directory didn't work");
        }
        $this->assertTrue(is_writable(dirname($path)));
        $this->assertFalse(is_writable($path));
        $this->assertTrue(file_exists($path));
        return $file;
    }

    private function makeNotReadableFile()
    {
        return $this->makeNotReadableFile_inWritableDirectory();
    }

    private function makeNotReadableFile_inNonWritableDirectory()
    {
        return $this->makeFile(self::NOT_EXISTING_FILE_IN_NON_WRITABLE_DIRECTORY);
    }

    private function makeNotReadableFile_inWritableDirectory()
    {
        return $this->makeFile(self::NOT_EXISTING_FILE_IN_WRITABLE_DIRECTORY);
    }

    public function test_getName()
    {
        $this->assertSame('test.js', $this->makeFile()->getName());
        $this->assertSame('notExisTinGFile.js', $this->makeNotReadableFile()->getName());
    }

    public function test_setFile_createsNewObjectLeavesOldUnchanged()
    {
        $file = $this->makeFile();
        $file2 = $file->setFile('foo/bar.png');
        $this->assertSame('test.js', $file->getName());
        $this->assertSame('bar.png', $file2->getName());
    }

    public function test_setFile_returnsObjectOfSameType()
    {
        $file = new CustomTestFile('foo/baz.png');
        $file2 = $file->setFile('foo/bar.png');
        $this->assertTrue($file2 instanceof CustomTestFile);
    }

    public function test_getPath()
    {
        $this->assertSame($this->dir . 'notExisTinGFile.js', $this->makeNotReadableFile()->getPath());
    }

    public function test_hasReadAccess()
    {
        $this->assertTrue($this->makeFile()->hasReadAccess());
        $this->assertFalse($this->makeNotReadableFile()->hasReadAccess());
    }

    public function test_hasWriteAccess()
    {
        $this->assertTrue($this->makeFile()->hasWriteAccess());
        $this->assertTrue($this->makeNotReadableFile_inWritableDirectory()->hasWriteAccess());
        $this->assertFalse($this->makeNotReadableFile_inNonWritableDirectory()->hasWriteAccess());
    }

    public function test_hasWriteAccess_whenFileExistAndIsNotWritable()
    {
        $this->assertFalse($this->makeNotWritableFile()->hasWriteAccess());
    }

    public function test_checkReadable_shouldNotThrowException_IfIsReadable()
    {
        self::expectNotToPerformAssertions();

        $this->makeFile()->checkReadable();
    }

    public function test_checkWritable_shouldNotThrowException_IfIsWritable()
    {
        self::expectNotToPerformAssertions();

        $this->makeFile()->checkWritable();
    }

    public function test_checkReadable_shouldThrowException_IfNotIsReadable()
    {
        $this->expectException(\Piwik\Plugins\CustomJsTracker\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('not readable');

        $this->makeNotReadableFile()->checkReadable();
    }

    public function test_checkWritable_shouldThrowException_IfNotIsWritable()
    {
        $this->expectException(\Piwik\Plugins\CustomJsTracker\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('not writable');

        $this->makeNotReadableFile_inNonWritableDirectory()->checkWritable();
    }

    public function test_checkWritable_shouldNotThrowException_IfDirectoryIsWritable()
    {
        $this->expectNotToPerformAssertions();
        $this->makeNotReadableFile_inWritableDirectory()->checkWritable();
    }

    public function test_getContent()
    {
        $this->assertSame("// Hello world\nvar fooBar = 'test';", $this->makeFile()->getContent());
    }

    public function test_isFileContentSame()
    {
        $this->assertTrue($this->makeFile()->isFileContentSame("// Hello world\nvar fooBar = 'test';"));
        $this->assertFalse($this->makeFile()->isFileContentSame("// Hello world\nvar foBar = 'test';"));
        $this->assertFalse($this->makeFile()->isFileContentSame("// Hello world\nvar foBar = 'test'"));
        $this->assertFalse($this->makeFile()->isFileContentSame("Hello world\nvar foBar = 'test'"));
    }

    public function test_getContent_returnsNull_IfFileIsNotReadableOrNotExists()
    {
        $this->assertNull($this->makeNotReadableFile()->getContent());
    }

    public function test_save()
    {
        $notExistingFile = $this->makeNotReadableFile_inWritableDirectory();
        $this->assertFalse($notExistingFile->hasReadAccess());
        $this->assertTrue($notExistingFile->hasWriteAccess());

        $updatedFile =  $notExistingFile->save('myTestContent');

        $this->assertEquals([$this->dir . 'notExisTinGFile.js'], $updatedFile);
        $this->assertEquals('myTestContent', $notExistingFile->getContent());
        $this->assertTrue($notExistingFile->hasReadAccess());
        $this->assertTrue($notExistingFile->hasWriteAccess());
    }

}
