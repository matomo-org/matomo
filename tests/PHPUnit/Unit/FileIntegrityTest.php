<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\FileIntegrity;

/**
 * @group Core
 * @group FileIntegrity
 */
class FileIntegrityTest extends \PHPUnit\Framework\TestCase
{
    public function testNoUnexpectedFilesGivesNoMessagesOrNotes()
    {
        $this->assertSame([[], []], TestFileIntegrity::getMessagesUnexpectedFiles([], []));
    }

    public function testOnlyLeftoverDeveloperDocsAreReportedAsNotes()
    {
        [$messages, $notes] = TestFileIntegrity::getMessagesUnexpectedFiles(
            [],
            ['AGENTS.md', 'CHANGELOG.md', 'CONTRIBUTING.md']
        );

        $this->assertSame([], $messages);
        $this->assertCount(1, $notes);
        $this->assertStringContainsString('General_LeftoverDeveloperDocsFound', $notes[0]);
        $this->assertStringContainsString('AGENTS.md<br/>CHANGELOG.md<br/>CONTRIBUTING.md<br/>', $notes[0]);
    }

    public function testOtherFilesKeepTheUnexpectedFileMessage()
    {
        [$messages, $notes] = TestFileIntegrity::getMessagesUnexpectedFiles(
            ['existing message'],
            ['AGENTS.md', 'foo.php']
        );

        $this->assertCount(2, $messages);
        $this->assertSame('existing message', $messages[0]);
        $this->assertStringContainsString('General_ExceptionUnexpectedFile', $messages[1]);
        $this->assertStringContainsString('foo.php', $messages[1]);
        $this->assertStringNotContainsString('AGENTS.md', $messages[1]);

        $this->assertCount(1, $notes);
        $this->assertStringContainsString('AGENTS.md', $notes[0]);
        $this->assertStringNotContainsString('foo.php', $notes[0]);
    }

    public function testSameNamesOutsideTheRootAreNotLeftoverDeveloperDocs()
    {
        [$messages, $notes] = TestFileIntegrity::getMessagesUnexpectedFiles(
            [],
            ['vendor/foo/CHANGELOG.md', 'plugins/Foo/CONTRIBUTING.md', 'agents.md']
        );

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('General_ExceptionUnexpectedFile', $messages[0]);
        $this->assertSame([], $notes);
    }
}

class TestFileIntegrity extends FileIntegrity
{
    public static function getMessagesUnexpectedFiles(array $messages, array $filesFoundButNotExpected): array
    {
        return parent::getMessagesUnexpectedFiles($messages, $filesFoundButNotExpected);
    }
}
