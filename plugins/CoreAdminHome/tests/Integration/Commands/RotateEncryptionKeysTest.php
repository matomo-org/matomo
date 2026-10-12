<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\FileBackedConfig;
use Piwik\Plugins\CoreAdminHome\tests\Integration\EncryptionKeyRotatorTest;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group CoreAdminHome
 * @group RotateEncryptionKeys
 */
class RotateEncryptionKeysTest extends ConsoleCommandTestCase
{
    private $targets = [];

    /**
     * @var FileBackedConfig
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        Config::getInstance()->Annotations = ['encryption_key' => 'key-a'];
        Config::getInstance()->Contents = ['encryption_key' => 'key-b'];
        Option::set('Annotations.token', EncryptionKeyRotatorTest::encrypt('secret-a', 'key-a'));
        Option::set('Contents.token', EncryptionKeyRotatorTest::encrypt('secret-b', 'key-b'));
        $this->config = FileBackedConfig::replaceTestConfig();

        $this->targets = [
            'Annotations' => EncryptionKeyRotatorTest::makeTarget('Annotations') + ['options' => ['Annotations.token']],
            'Contents' => EncryptionKeyRotatorTest::makeTarget('Contents') + ['options' => ['Contents.token']],
        ];

        // replaces rather than adds to the targets, so activated plugins' own targets don't leak into these tests
        Piwik::addAction('CoreAdminHome.getEncryptionKeyRotationTargets', function (&$targets) {
            $targets = $this->targets;
        });
    }

    public function tearDown(): void
    {
        $this->config->deleteFile();

        parent::tearDown();
    }

    public function testRotatesTheKeysOfAllRegisteredPlugins()
    {
        $code = $this->runCommand();

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('Annotations: key rotated, 1 value(s) re-encrypted.', $output);
        $this->assertStringContainsString('Contents: key rotated, 1 value(s) re-encrypted.', $output);

        $newKeyA = Config::getInstance()->Annotations['encryption_key'];
        $newKeyB = Config::getInstance()->Contents['encryption_key'];
        $this->assertNotSame('key-a', $newKeyA);
        $this->assertNotSame('key-b', $newKeyB);
        $this->assertStringNotContainsString($newKeyA, $output);
        $this->assertStringNotContainsString($newKeyB, $output);
        $this->assertStringNotContainsString('secret-a', $output);

        $this->assertSame('secret-a', EncryptionKeyRotatorTest::decrypt(Option::get('Annotations.token'), $newKeyA));
        $this->assertSame('secret-b', EncryptionKeyRotatorTest::decrypt(Option::get('Contents.token'), $newKeyB));
    }

    public function testPluginOptionRestrictsWhichKeysAreRotated()
    {
        $code = $this->runCommand(['--plugin' => ['Contents']]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringNotContainsString('Annotations', $this->applicationTester->getDisplay());
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertNotSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testUnknownPluginFailsWithoutRotatingAnything()
    {
        $code = $this->runCommand(['--plugin' => ['Contents', 'Unknown']]);

        $this->assertNotEquals(0, $code);
        // the console wraps long error messages, sometimes mid-word
        $this->assertStringContainsString(
            'Thesepluginsarenotactivatedordonotstorevaluesencryptedwitharotatablekey:Unknown',
            preg_replace('/\s+/', '', $this->applicationTester->getDisplay())
        );
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testDryRunChangesNothing()
    {
        $code = $this->runCommand(['--dry-run' => true]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString(
            'Annotations: 1 value(s) can be decrypted and would be re-encrypted.',
            $this->applicationTester->getDisplay()
        );
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testAFailingPluginDoesNotStopTheOthersButFailsTheCommand()
    {
        Option::set('Annotations.token', EncryptionKeyRotatorTest::encrypt('secret-a', 'some-other-key'));

        $code = $this->runCommand();

        $this->assertEquals(1, $code);
        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString(
            "Annotations: failed. Could not decrypt the option 'Annotations.token' with the current key.",
            $output
        );
        $this->assertStringNotContainsString('caused by', $output);
        $this->assertStringContainsString('Contents: key rotated, 1 value(s) re-encrypted.', $output);
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertNotSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testVerboseOutputNamesTheCauseOfAFailure()
    {
        Option::set('Annotations.token', EncryptionKeyRotatorTest::encrypt('secret-a', 'some-other-key'));

        $this->applicationTester->run(
            ['command' => 'core:rotate-encryption-keys', '--plugin' => ['Annotations']],
            ['interactive' => false, 'verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        $this->assertStringContainsString('Annotations: caused by: Wrong key', $this->applicationTester->getDisplay());
    }

    public function testAPluginFailingAfterWritingItsKeyKeepsTheKeyWrittenBeforeIt()
    {
        $writes = 0;
        Piwik::addAction('Core.configFileChanged', function () use (&$writes) {
            if (++$writes === 2) {
                throw new \Exception('listener failed');
            }
        });

        $code = $this->runCommand();

        $this->assertEquals(1, $code);
        $this->assertStringContainsString('Contents: failed. listener failed', $this->applicationTester->getDisplay());
        $newKeyA = Config::getInstance()->Annotations['encryption_key'];
        $this->assertNotSame('key-a', $newKeyA);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);

        $file = $this->config->readFile();
        $this->assertSame($newKeyA, $file['Annotations']['encryption_key']);
        $this->assertSame('key-b', $file['Contents']['encryption_key']);
    }

    public function testStopsWhenTheConfigFileCouldNotBeRestored()
    {
        // a directory in place of the file makes restoring it fail
        $this->config->onSave(function (Config $config) {
            unlink($config->getLocalPath());
            mkdir($config->getLocalPath());
            throw new \Exception('config save failed');
        });

        $code = $this->runCommand();

        $this->assertEquals(1, $code);
        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('Stopped without rotating the remaining plugins', $output);
        $this->assertStringNotContainsString('Contents:', $output);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testKeepsAKeyAnotherRunSavedAfterTheCommandStarted()
    {
        $path = $this->config->getLocalPath();
        $contents = file_get_contents($path);
        $this->assertSame(1, substr_count($contents, 'key-a'));
        file_put_contents($path, str_replace('key-a', 'key-from-another-run', $contents));

        $code = $this->runCommand(['--plugin' => ['Contents']]);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $file = $this->config->readFile();
        $this->assertSame('key-from-another-run', $file['Annotations']['encryption_key']);
        $this->assertNotSame('key-b', $file['Contents']['encryption_key']);
    }

    public function testATargetThatIsNotAnArrayFailsOnlyThatPlugin()
    {
        $this->targets['Annotations'] = null;

        $code = $this->runCommand();

        $this->assertEquals(1, $code);
        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('Annotations: failed. The encryption key rotation target of Annotations is not an array.', $output);
        $this->assertStringContainsString('Contents: key rotated, 1 value(s) re-encrypted.', $output);
    }

    public function testATargetDeclaredForAPluginThatIsNotActivatedFailsWithoutRotatingAnything()
    {
        $this->targets['MsTeams'] = EncryptionKeyRotatorTest::makeTarget('MsTeams');

        $code = $this->runCommand();

        $this->assertNotEquals(0, $code);
        $this->assertStringContainsString(
            'Encryptionkeyrotationtargetsweredeclaredforpluginsthatarenotactivated:MsTeams',
            preg_replace('/\s+/', '', $this->applicationTester->getDisplay())
        );
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testPluginWithoutAKeyIsSkipped()
    {
        Config::getInstance()->Annotations = [];
        Config::getInstance()->forceSave();

        $code = $this->runCommand();

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString(
            'Annotations: skipped, no encryption key is configured.',
            $this->applicationTester->getDisplay()
        );
    }

    public function testDecliningTheConfirmationChangesNothing()
    {
        $this->applicationTester->setInputs(['n']);

        $code = $this->runCommand([], true);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testTheConfirmationOnlyNamesPluginsWithAKey()
    {
        Config::getInstance()->Annotations = [];
        $this->applicationTester->setInputs(['n']);

        $this->runCommand([], true);

        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('You are about to rotate the encryption keys of Contents. Values', $output);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testTheConfirmationNamesAPluginWhoseTargetHasAnInvalidConfigName()
    {
        $this->targets['Annotations']['configSection'] = 'Annotations Rotation';
        $this->applicationTester->setInputs(['n']);

        $this->runCommand([], true);

        $output = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('You are about to rotate the encryption keys of Annotations, Contents. Values', $output);
    }

    public function testDoesNotAskForConfirmationWhenNoPluginHasAKey()
    {
        Config::getInstance()->Annotations = [];
        Config::getInstance()->Contents = [];
        Config::getInstance()->forceSave();

        $code = $this->runCommand([], true);

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $output = $this->applicationTester->getDisplay();
        $this->assertStringNotContainsString('You are about to rotate', $output);
        $this->assertStringContainsString('Contents: skipped, no encryption key is configured.', $output);
    }

    public function testReportsWhenNoPluginIsRegistered()
    {
        $this->targets = [];

        $code = $this->runCommand();

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());
        $this->assertStringContainsString(
            'No activated plugin stores values encrypted with a rotatable key.',
            $this->applicationTester->getDisplay()
        );
    }

    public function testTwoPluginsSharingAKeyFailWithoutRotatingAnything()
    {
        $this->targets['Dashboard'] = EncryptionKeyRotatorTest::makeTarget('Annotations');

        $code = $this->runCommand(['--plugin' => ['Contents']]);

        $this->assertNotEquals(0, $code);
        $this->assertStringContainsString(
            'Thesepluginssharetheencryptionkey[Annotations]encryption_key,soitcannotberotated:Annotations,Dashboard',
            preg_replace('/\s+/', '', $this->applicationTester->getDisplay())
        );
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testFailsWithoutRotatingAnythingWhileAnotherRotationHoldsTheLock()
    {
        $otherRun = $this->makeLock();
        $this->assertTrue($otherRun->acquireLock(''));

        try {
            $code = $this->runCommand();
        } finally {
            $otherRun->unlock();
        }

        $this->assertEquals(1, $code);
        $this->assertStringContainsString('Another encryption key rotation is running.', $this->applicationTester->getDisplay());
        $this->assertSame('key-a', Config::getInstance()->Annotations['encryption_key']);
    }

    public function testStopsWhenTheLockWasTakenOverWhileRotating()
    {
        $otherRun = $this->makeLock();
        $this->targets['Annotations']['encrypt'] = function (string $value, string $key) use ($otherRun): string {
            // as if this run's lock expired and another run acquired it
            $backend = StaticContainer::get(LockBackend::class);
            $lockKey = 'CoreAdminHome.rotateEncryptionKeys';
            $backend->deleteIfKeyHasValue($lockKey, $backend->get($lockKey));
            $this->assertTrue($otherRun->acquireLock(''));

            return EncryptionKeyRotatorTest::encrypt($value, $key);
        };

        try {
            $code = $this->runCommand();
        } finally {
            $otherRun->unlock();
        }

        $this->assertEquals(1, $code);
        $this->assertStringContainsString('The lock expired before rotating Contents', $this->applicationTester->getDisplay());
        $this->assertNotSame('key-a', Config::getInstance()->Annotations['encryption_key']);
        $this->assertSame('key-b', Config::getInstance()->Contents['encryption_key']);
    }

    public function testReleasesTheLockAfterRotating()
    {
        $this->runCommand();

        $nextRun = $this->makeLock();
        $this->assertTrue($nextRun->acquireLock(''));
        $nextRun->unlock();
    }

    private function makeLock(): Lock
    {
        return new Lock(StaticContainer::get(LockBackend::class), 'CoreAdminHome.rotateEncryptionKeys');
    }

    private function runCommand(array $options = [], bool $interactive = false): int
    {
        return $this->applicationTester->run(
            ['command' => 'core:rotate-encryption-keys'] + $options,
            ['interactive' => $interactive]
        );
    }
}
