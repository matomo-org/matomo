<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Matomo\Cache\Transient;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Cache as PiwikCache;
use Piwik\CacheId;
use Piwik\Common;
use Piwik\Config\IniFileChain;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRotationOutcomeUnknownException;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRotator;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\FileBackedConfig;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Factory;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreAdminHome
 * @group EncryptionKeyRotator
 */
class EncryptionKeyRotatorTest extends IntegrationTestCase
{
    private const PLUGIN_NAME = 'RotationTestPlugin';
    private const OLD_KEY = 'old-key';

    /**
     * @var FileBackedConfig
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2012-01-01 00:00:00');
        Fixture::createWebsite('2012-01-01 00:00:00');

        Config::getInstance()->RotationTestPlugin = ['encryption_key' => self::OLD_KEY];
        $this->config = FileBackedConfig::replaceTestConfig();
    }

    public function tearDown(): void
    {
        $this->config->deleteFile();

        parent::tearDown();
    }

    public function testRotateReEncryptsEveryStoredValueAndReplacesTheKey()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        Option::set('RotationTestPlugin.plain', 'not encrypted');
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', self::encrypt('api-secret', self::OLD_KEY));
        $this->insertPluginSetting(self::PLUGIN_NAME, 'userPreference', 'not encrypted', 'someuser');
        $this->insertSiteSetting(1, self::PLUGIN_NAME, 'siteToken', self::encrypt('site-1-secret', self::OLD_KEY));
        $this->insertSiteSetting(2, self::PLUGIN_NAME, 'siteToken', '0');
        $otherPluginValue = self::encrypt('other-secret', self::OLD_KEY);
        $this->insertSiteSetting(1, 'OtherPlugin', 'siteToken', $otherPluginValue);

        $rotatedPlugins = [];
        Piwik::addAction('CoreAdminHome.encryptionKeyRotated', function ($pluginName) use (&$rotatedPlugins) {
            $rotatedPlugins[] = $pluginName;
        });

        $count = (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());

        $this->assertSame(3, $count);
        $this->assertSame([self::PLUGIN_NAME], $rotatedPlugins);

        $newKey = $this->getConfiguredKey();
        $this->assertNotSame(self::OLD_KEY, $newKey);
        $this->assertNotEmpty($newKey);

        $this->assertSame('token-secret', self::decrypt(Option::get('RotationTestPlugin.token'), $newKey));
        $this->assertSame('api-secret', self::decrypt($this->getPluginSetting(self::PLUGIN_NAME, 'apiKey'), $newKey));
        $this->assertSame('site-1-secret', self::decrypt($this->getSiteSetting(1, self::PLUGIN_NAME, 'siteToken'), $newKey));

        $this->assertSame('not encrypted', Option::get('RotationTestPlugin.plain'));
        $this->assertSame('0', $this->getSiteSetting(2, self::PLUGIN_NAME, 'siteToken'));
        $this->assertSame($otherPluginValue, $this->getSiteSetting(1, 'OtherPlugin', 'siteToken'));
    }

    public function testSettingsStorageReadAfterRotationReturnsTheNewCiphertext()
    {
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', self::encrypt('api-secret', self::OLD_KEY));
        $this->getApiKeyThroughSettingsStorage();

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());

        $this->assertSame('api-secret', self::decrypt($this->getApiKeyThroughSettingsStorage(), $this->getConfiguredKey()));
    }

    public function testRotateDiscardsTheCachedSystemSettingsObjects()
    {
        $cacheId = CacheId::languageAware('AllSystemSettings');
        PiwikCache::getTransientCache()->save($cacheId, ['settings created before the rotation']);

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());

        $this->assertFalse(PiwikCache::getTransientCache()->contains($cacheId));
    }

    public function testRotateReturnsNullAndChangesNothingWhenNoKeyIsConfigured()
    {
        Config::getInstance()->RotationTestPlugin = [];
        $value = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $value);

        $this->assertNull((new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));

        $this->assertSame([], Config::getInstance()->RotationTestPlugin);
        $this->assertSame($value, Option::get('RotationTestPlugin.token'));
    }

    public function testRotateStillReplacesTheKeyWhenNoValueIsStored()
    {
        $this->assertSame(0, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));

        $this->assertNotSame(self::OLD_KEY, $this->getConfiguredKey());
    }

    public function testDryRunCountsValuesWithoutChangingAnything()
    {
        $value = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $value);

        $this->assertSame(1, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget(), true));

        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
        $this->assertSame($value, Option::get('RotationTestPlugin.token'));
    }

    public function testRotateRefusesATableWithoutTransactions()
    {
        $table = Common::prefixTable('site_setting');
        Db::exec("ALTER TABLE `$table` ENGINE=MyISAM");

        try {
            $this->assertRotationFailsWithoutChangingAnything(
                "These tables do not support transactions, so a failed rotation could not be undone: $table (MyISAM)."
            );
        } finally {
            Db::exec("ALTER TABLE `$table` ENGINE=InnoDB");
        }
    }

    public function testDryRunFailsWhenDecryptReturnsSomethingOtherThanAString()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $target = array_merge($this->getTarget(), [
            'decrypt' => function (): bool {
                return false;
            },
        ]);

        $this->expectExceptionMessage("Could not decrypt the option 'RotationTestPlugin.token' with the current key.");
        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target, true);
    }

    public function testRotateChangesNothingWhenAValueCannotBeDecrypted()
    {
        $value = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $value);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', self::encrypt('api-secret', 'some-other-key'));

        try {
            (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
            $this->fail('Expected the rotation to fail');
        } catch (\Exception $e) {
            $this->assertSame("Could not decrypt the system setting 'apiKey' with the current key.", $e->getMessage());
        }

        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
        $this->assertSame($value, Option::get('RotationTestPlugin.token'));
    }

    public function testRotateRollsBackWhenAValueChangesWhileRotating()
    {
        $tokenValue = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $tokenValue);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', self::encrypt('api-secret', self::OLD_KEY));
        $concurrentValue = self::encrypt('new-api-secret', self::OLD_KEY);

        $target = $this->getTarget();
        $target['encrypt'] = function (string $value, string $key) use ($concurrentValue): string {
            if ($value === 'token-secret') {
                $this->updatePluginSetting(self::PLUGIN_NAME, 'apiKey', $concurrentValue);
            }

            return self::encrypt($value, $key);
        };

        try {
            (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
            $this->fail('Expected the rotation to fail');
        } catch (\Exception $e) {
            $this->assertStringContainsString("system setting 'apiKey' changed while rotating", $e->getMessage());
        }

        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
        Option::clearCachedOption('RotationTestPlugin.token');
        $this->assertSame($tokenValue, Option::get('RotationTestPlugin.token'));
        $this->assertSame($concurrentValue, $this->getPluginSetting(self::PLUGIN_NAME, 'apiKey'));
    }

    public function testRotateRestoresTheOldKeyWhenSavingTheConfigFails()
    {
        $this->config->onSave(function () {
            throw new \Exception('config save failed');
        });

        $this->assertRotationFailsWithoutChangingAnything('config save failed');
    }

    public function testRotateKeepsTheNewKeyWhenACommitReportingAnErrorWasApplied()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $rotator = new class extends EncryptionKeyRotator {
            protected function commit(\Zend_Db_Adapter_Abstract $db): void
            {
                $db->commit();
                throw new \Exception('connection lost');
            }
        };

        $this->assertSame(1, $rotator->rotate(self::PLUGIN_NAME, $this->getTarget()));

        $newKey = $this->getConfiguredKey();
        $this->assertNotSame(self::OLD_KEY, $newKey);
        Option::clearCachedOption('RotationTestPlugin.token');
        $this->assertSame('token-secret', self::decrypt(Option::get('RotationTestPlugin.token'), $newKey));
    }

    public function testRotateRestoresTheOldKeyWhenAFailedCommitWasNotApplied()
    {
        $rotator = new class extends EncryptionKeyRotator {
            protected function commit(\Zend_Db_Adapter_Abstract $db): void
            {
                // the transaction stays open, holding its row locks
                throw new \Exception('commit failed');
            }
        };

        $this->assertRotationFailsWithoutChangingAnything('commit failed', $rotator);
    }

    public function testRotateKeepsTheNewKeyWhenItCannotTellWhetherAFailedCommitWasApplied()
    {
        $tokenValue = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $tokenValue);
        $rotator = new class extends EncryptionKeyRotator {
            protected function commit(\Zend_Db_Adapter_Abstract $db): void
            {
                $db->rollBack();
                throw new \Exception('commit failed');
            }

            protected function fetchStoredCiphertext(array $value)
            {
                throw new \Exception('server gone away');
            }
        };

        try {
            $rotator->rotate(self::PLUGIN_NAME, $this->getTarget());
            $this->fail('Expected the rotation to fail');
        } catch (EncryptionKeyRotationOutcomeUnknownException $e) {
            $this->assertStringContainsString("If the option 'RotationTestPlugin.token' still holds the value it had before the rotation", $e->getMessage());
            $this->assertStringContainsString('Committing failed because: commit failed', $e->getMessage());
        }

        $this->assertNotSame(self::OLD_KEY, $this->getConfiguredKey());
        Option::clearCachedOption('RotationTestPlugin.token');
        $this->assertSame($tokenValue, Option::get('RotationTestPlugin.token'));
    }

    public function testRotateRestoresTheConfigFileWhenAConfigFileChangedListenerThrows()
    {
        Piwik::addAction('Core.configFileChanged', function () {
            throw new \Exception('listener failed');
        });

        $this->assertRotationFailsWithoutChangingAnything('listener failed');
    }

    public function testRotateReportsTheOriginalFailureWhenOnlyClearingTheConfigCacheFailsAfterRestoring()
    {
        Piwik::addAction('Core.configFileChanged', function () {
            $iniFileChain = $this->createMock(IniFileChain::class);
            $iniFileChain->method('deleteConfigCache')->willThrowException(new \Exception('cache clear failed'));
            $settingsProvider = $this->createMock(GlobalSettingsProvider::class);
            $settingsProvider->method('getIniFileChain')->willReturn($iniFileChain);
            StaticContainer::getContainer()->set(GlobalSettingsProvider::class, $settingsProvider);

            throw new \Exception('listener failed');
        });

        $this->assertSame('listener failed', $this->assertRotationFailsWithoutChangingAnything('listener failed'));
    }

    public function testRotateRestoresTheConfigFileWhenItIsNotWrittenCorrectly()
    {
        $this->config->onSave(function (Config $config) {
            file_put_contents($config->getLocalPath(), 'truncated');
            Piwik::postEvent('Core.configFileChanged', [$config->getLocalPath()]);
        });

        $this->assertRotationFailsWithoutChangingAnything(
            'The new encryption key is missing from ' . $this->config->getLocalPath() . ' after saving it. The file may not have been written correctly'
        );
    }

    public function testRotatePostsConfigBeforeSaveOnce()
    {
        $calls = 0;
        Piwik::addAction('Config.beforeSave', function () use (&$calls) {
            $calls++;
        });

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());

        $this->assertSame(1, $calls);
    }

    public function testRotateFailsWhenTheConfigIsNotWritten()
    {
        $this->config->onSave(function () {
        });

        $this->assertRotationFailsWithoutChangingAnything('The new encryption key was not written to ' . $this->config->getLocalPath() . '.');
    }

    public function testRotateFailsWhenAConfigBeforeSaveListenerKeepsTheNewKeyOutOfTheFile()
    {
        Piwik::addAction('Config.beforeSave', function (&$values) {
            unset($values['RotationTestPlugin']['encryption_key']);
        });

        $this->assertRotationFailsWithoutChangingAnything('The new encryption key is missing from ' . $this->config->getLocalPath() . ' after saving it.');
    }

    public function testRotateSaysToRestoreFromBackupWhenTheConfigFileCannotBeRestored()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        // a directory in place of the file makes restoring it fail
        $this->config->onSave(function (Config $config) {
            unlink($config->getLocalPath());
            mkdir($config->getLocalPath());
            throw new \Exception('config save failed');
        });

        try {
            (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
            $this->fail('Expected the rotation to fail');
        } catch (\Exception $e) {
            $this->assertStringContainsString('the old key could not be restored', $e->getMessage());
            $this->assertStringContainsString('config save failed', $e->getMessage());
        }
    }

    public function testRotateReEncryptsLegacySettingRowsHoldingTheSameValue()
    {
        $value = self::encrypt('api-secret', self::OLD_KEY);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', $value);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', $value);

        $this->assertSame(2, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));

        $rows = array_column(Db::fetchAll(
            'SELECT `setting_value` FROM `' . Common::prefixTable('plugin_setting') . '` WHERE `plugin_name` = ? AND `setting_name` = ?',
            [self::PLUGIN_NAME, 'apiKey']
        ), 'setting_value');
        $newValue = self::encrypt('api-secret', $this->getConfiguredKey());
        $this->assertSame([$newValue, $newValue], $rows);
    }

    public function testRotateToleratesANameDeclaredTwice()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $this->insertPluginSetting(self::PLUGIN_NAME, 'apiKey', self::encrypt('api-secret', self::OLD_KEY));
        $target = $this->getTarget();
        $target['options'][] = 'RotationTestPlugin.token';
        $target['pluginSettings'][] = 'apiKey';

        $this->assertSame(2, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target));

        $this->assertSame('api-secret', self::decrypt($this->getPluginSetting(self::PLUGIN_NAME, 'apiKey'), $this->getConfiguredKey()));
    }

    public function testRotateRefusesAnEncryptedPerUserSetting()
    {
        $userValue = self::encrypt('user-secret', self::OLD_KEY);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'userToken', $userValue, 'someuser');

        try {
            (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
            $this->fail('Expected the rotation to fail');
        } catch (\Exception $e) {
            $this->assertSame("The per-user setting 'userToken' is encrypted, but only system settings can be re-encrypted.", $e->getMessage());
        }

        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
    }

    public function testRotateRefusesAJsonEncodedPerUserSettingHoldingAnEncryptedValue()
    {
        $value = json_encode(['label' => 'mine', 'tokens' => [self::encrypt('user-secret', self::OLD_KEY)]]);
        $this->insertPluginSetting(self::PLUGIN_NAME, 'userTokens', $value, 'someuser', true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The per-user setting 'userTokens' is encrypted, but only system settings can be re-encrypted.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
    }

    public function testRotateRefusesAnEncryptedPerUserSettingMarkedJsonEncodedThatIsNotValidJson()
    {
        $this->insertPluginSetting(self::PLUGIN_NAME, 'userToken', self::encrypt('user-secret', self::OLD_KEY), 'someuser', true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The per-user setting 'userToken' is encrypted, but only system settings can be re-encrypted.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
    }

    public function testRotateAllowsAJsonEncodedPerUserSettingWithoutAnEncryptedValue()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $this->insertPluginSetting(self::PLUGIN_NAME, 'userColumns', json_encode(['label', ['nb_visits', 3]]), 'someuser', true);

        $this->assertSame(1, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));
    }

    public function testRotateRefusesAJsonEncodedSetting()
    {
        $value = json_encode([self::encrypt('api-secret', self::OLD_KEY)]);
        Db::query(
            'INSERT INTO `' . Common::prefixTable('plugin_setting') . '` (`plugin_name`, `setting_name`, `setting_value`, `json_encoded`, `user_login`) VALUES (?, ?, ?, 1, ?)',
            [self::PLUGIN_NAME, 'apiKey', $value, '']
        );

        try {
            (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
            $this->fail('Expected the rotation to fail');
        } catch (\Exception $e) {
            $this->assertSame("The system setting 'apiKey' is stored JSON-encoded, which cannot be re-encrypted.", $e->getMessage());
        }

        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
        $this->assertSame($value, $this->getPluginSetting(self::PLUGIN_NAME, 'apiKey'));
    }

    public function testRotateRefusesACoreConfigSection()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('points at the core config section [General]');

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, self::makeTarget('General'));
    }

    public function testRotateKeepsTheRotationWhenAnEncryptionKeyRotatedListenerThrows()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));

        Piwik::addAction('CoreAdminHome.encryptionKeyRotated', function () {
            throw new \Exception('listener failed');
        });

        $this->assertSame(1, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));

        $newKey = $this->getConfiguredKey();
        $this->assertNotSame(self::OLD_KEY, $newKey);
        $this->assertSame('token-secret', self::decrypt(Option::get('RotationTestPlugin.token'), $newKey));
    }

    public function testRotatePostsTheRotatedEventWhenClearingACacheFails()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $transientCache = $this->createPartialMock(Transient::class, ['delete']);
        $transientCache->method('delete')->willThrowException(new \Exception('cache delete failed'));
        StaticContainer::getContainer()->set('Matomo\Cache\Transient', $transientCache);

        $rotatedPlugins = [];
        Piwik::addAction('CoreAdminHome.encryptionKeyRotated', function ($pluginName) use (&$rotatedPlugins) {
            $rotatedPlugins[] = $pluginName;
        });

        $this->assertSame(1, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget()));
        $this->assertSame([self::PLUGIN_NAME], $rotatedPlugins);
        // the option cache is cleared after the failing step, so reading the old ciphertext would mean it was skipped
        $this->assertSame('token-secret', self::decrypt(Option::get('RotationTestPlugin.token'), $this->getConfiguredKey()));
    }

    public function testRotateSaysWhenTheReEncryptedValueCannotBeDecrypted()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $target = $this->getTarget();
        $target['decrypt'] = function (string $value, string $key): string {
            if ($key !== self::OLD_KEY) {
                throw new \Exception('decrypt failed');
            }
            return self::decrypt($value, $key);
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Could not decrypt the re-encrypted option 'RotationTestPlugin.token' with the new key.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
    }

    public function testRotateFailsWhenEncryptReturnsSomethingOtherThanAString()
    {
        Option::set('RotationTestPlugin.token', self::encrypt('token-secret', self::OLD_KEY));
        $target = $this->getTarget();
        $target['encrypt'] = function () {
            return 123;
        };
        $target['decrypt'] = function ($value, string $key): string {
            return is_string($value) ? self::decrypt($value, $key) : 'token-secret';
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Re-encrypting the option 'RotationTestPlugin.token' with the new key did not round-trip.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
    }

    public function testRotateReEncryptsEveryDeclaredSiteSetting()
    {
        $this->insertSiteSetting(1, self::PLUGIN_NAME, 'siteToken', self::encrypt('site-token', self::OLD_KEY));
        $this->insertSiteSetting(2, self::PLUGIN_NAME, 'siteSecret', self::encrypt('site-secret', self::OLD_KEY));
        $undeclaredValue = self::encrypt('undeclared', self::OLD_KEY);
        $this->insertSiteSetting(1, self::PLUGIN_NAME, 'undeclared', $undeclaredValue);
        $target = array_merge($this->getTarget(), ['siteSettings' => ['siteToken', 'siteSecret']]);

        $this->assertSame(2, (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target));

        $newKey = $this->getConfiguredKey();
        $this->assertSame('site-token', self::decrypt($this->getSiteSetting(1, self::PLUGIN_NAME, 'siteToken'), $newKey));
        $this->assertSame('site-secret', self::decrypt($this->getSiteSetting(2, self::PLUGIN_NAME, 'siteSecret'), $newKey));
        $this->assertSame($undeclaredValue, $this->getSiteSetting(1, self::PLUGIN_NAME, 'undeclared'));
    }

    public function testRotateThrowsForAnInvalidTarget()
    {
        $target = $this->getTarget();
        unset($target['decrypt']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The encryption key rotation target of RotationTestPlugin has no callable 'decrypt'.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
    }

    /**
     * @dataProvider getConfigNamesConfigCannotSave
     */
    public function testRotateThrowsForAConfigNameConfigCannotSave(string $key, string $name)
    {
        $target = array_merge($this->getTarget(), [$key => $name]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The encryption key rotation target of RotationTestPlugin has no valid '$key'.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
    }

    public function getConfigNamesConfigCannotSave(): array
    {
        return [
            ['configSection', 'Rotation.TestPlugin'],
            ['configKey', 'encryption.key'],
            ['configKey', ''],
        ];
    }

    public function testRotateThrowsForAListHoldingSomethingOtherThanNames()
    {
        $target = $this->getTarget();
        $target['pluginSettings'][] = ['apiKey'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The encryption key rotation target of RotationTestPlugin has a 'pluginSettings' that is not a list of names.");

        (new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $target);
    }

    public static function encrypt(string $value, string $key): string
    {
        return 'test-enc:' . md5($key) . ':' . $value;
    }

    public static function decrypt(string $value, string $key): string
    {
        $prefix = 'test-enc:' . md5($key) . ':';
        if (strpos($value, $prefix) !== 0) {
            throw new \Exception('Wrong key');
        }

        return substr($value, strlen($prefix));
    }

    public static function makeTarget(string $configSection): array
    {
        return [
            'configSection' => $configSection,
            'configKey' => 'encryption_key',
            'isEncrypted' => function ($value): bool {
                return strpos($value, 'test-enc:') === 0;
            },
            'decrypt' => [self::class, 'decrypt'],
            'encrypt' => [self::class, 'encrypt'],
        ];
    }

    /**
     * @return string The message of the exception the rotation failed with.
     */
    private function assertRotationFailsWithoutChangingAnything(string $expectedMessage, ?EncryptionKeyRotator $rotator = null): string
    {
        $tokenValue = self::encrypt('token-secret', self::OLD_KEY);
        Option::set('RotationTestPlugin.token', $tokenValue);
        $configBefore = file_get_contents($this->config->getLocalPath());

        $message = null;
        try {
            ($rotator ?? new EncryptionKeyRotator())->rotate(self::PLUGIN_NAME, $this->getTarget());
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertNotNull($message, 'Expected the rotation to fail');
        $this->assertStringContainsString($expectedMessage, $message);
        $this->assertSame($configBefore, file_get_contents($this->config->getLocalPath()));
        $this->assertSame(self::OLD_KEY, $this->getConfiguredKey());
        Option::clearCachedOption('RotationTestPlugin.token');
        $this->assertSame($tokenValue, Option::get('RotationTestPlugin.token'));

        return $message;
    }

    private function getTarget(): array
    {
        return self::makeTarget('RotationTestPlugin') + [
            'options' => ['RotationTestPlugin.token', 'RotationTestPlugin.plain', 'RotationTestPlugin.missing'],
            'pluginSettings' => ['apiKey'],
            'siteSettings' => ['siteToken'],
        ];
    }

    private function getApiKeyThroughSettingsStorage(): string
    {
        return StaticContainer::get(Factory::class)
            ->getPluginStorage(self::PLUGIN_NAME, '')
            ->getValue('apiKey', '', FieldConfig::TYPE_STRING);
    }

    private function getConfiguredKey(): string
    {
        $key = Config::getInstance()->RotationTestPlugin['encryption_key'];
        $this->assertSame($key, $this->config->readFile()['RotationTestPlugin']['encryption_key'] ?? null, 'The config file holds another key');

        return $key;
    }

    private function insertPluginSetting(string $pluginName, string $settingName, string $value, string $userLogin = '', bool $jsonEncoded = false): void
    {
        Db::query(
            'INSERT INTO `' . Common::prefixTable('plugin_setting') . '` (`plugin_name`, `setting_name`, `setting_value`, `json_encoded`, `user_login`) VALUES (?, ?, ?, ?, ?)',
            [$pluginName, $settingName, $value, (int) $jsonEncoded, $userLogin]
        );
    }

    private function updatePluginSetting(string $pluginName, string $settingName, string $value): void
    {
        Db::query(
            'UPDATE `' . Common::prefixTable('plugin_setting') . '` SET `setting_value` = ? WHERE `plugin_name` = ? AND `setting_name` = ?',
            [$value, $pluginName, $settingName]
        );
    }

    private function getPluginSetting(string $pluginName, string $settingName): string
    {
        return Db::fetchOne(
            'SELECT `setting_value` FROM `' . Common::prefixTable('plugin_setting') . '` WHERE `plugin_name` = ? AND `setting_name` = ?',
            [$pluginName, $settingName]
        );
    }

    private function insertSiteSetting(int $idSite, string $pluginName, string $settingName, string $value): void
    {
        Db::query(
            'INSERT INTO `' . Common::prefixTable('site_setting') . '` (`idsite`, `plugin_name`, `setting_name`, `setting_value`, `json_encoded`) VALUES (?, ?, ?, ?, 0)',
            [$idSite, $pluginName, $settingName, $value]
        );
    }

    private function getSiteSetting(int $idSite, string $pluginName, string $settingName): string
    {
        return Db::fetchOne(
            'SELECT `setting_value` FROM `' . Common::prefixTable('site_setting') . '` WHERE `idsite` = ? AND `plugin_name` = ? AND `setting_name` = ?',
            [$idSite, $pluginName, $settingName]
        );
    }
}
