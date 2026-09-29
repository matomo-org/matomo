<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

use Matomo\Ini\IniReader;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Cache as PiwikCache;
use Piwik\CacheId;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Config;
use Piwik\Db;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Settings\Storage\Backend\Cache as SettingsCache;
use Piwik\Settings\Storage\Factory as SettingsStorageFactory;

/**
 * Replaces a plugin's encryption key and re-encrypts every value the plugin declares.
 *
 * The plugin describes where its encrypted values live and how to encrypt and decrypt them (see the
 * `CoreAdminHome.getEncryptionKeyRotationTargets` event). When the rotation fails with an error, nothing
 * changes, unless the old key cannot be written back to the config file. A process killed between saving the
 * new key and committing the re-encrypted values leaves the values encrypted with the old key, which only the
 * local config file (config/config.ini.php by default) from before the rotation can decrypt.
 */
class EncryptionKeyRotator
{
    /**
     * @var bool
     */
    private $isListeningForConfigWrites = false;

    /**
     * @var string|null
     */
    private $writtenConfigPath;

    /**
     * Rotates the encryption key of one plugin.
     *
     * @param string $pluginName The plugin the target belongs to. Used to find its plugin and site settings.
     * @param mixed $target A target as described in the `CoreAdminHome.getEncryptionKeyRotationTargets` event.
     * @param bool $dryRun When true, only checks that every value can be decrypted with the current key.
     * @return int|null The number of values re-encrypted (or that would be, on a dry run), or null when the
     *                  plugin has no encryption key configured, in which case nothing is checked or changed.
     */
    public function rotate(string $pluginName, mixed $target, bool $dryRun = false): ?int
    {
        $this->checkTarget($pluginName, $target);

        $config = Config::getInstance();
        $oldKey = $this->getConfiguredKey($target);

        if ($oldKey === null) {
            return null;
        }

        // fail before touching the database, and on a dry run, rather than in the middle of the rotation
        $config->checkConfigIsWritable();
        $this->checkTablesAreTransactional();

        $values = $this->getEncryptedValues($pluginName, $target);

        foreach ($values as &$value) {
            try {
                $value['plaintext'] = $target['decrypt']($value['ciphertext'], $oldKey);
            } catch (\Throwable $e) {
                throw new \Exception("Could not decrypt the {$value['label']} with the current key.", 0, $e);
            }

            if (!is_string($value['plaintext'])) {
                throw new \Exception("Could not decrypt the {$value['label']} with the current key.");
            }
        }
        unset($value);

        if ($dryRun) {
            return count($values);
        }

        $newKey = base64_encode(random_bytes(32));

        foreach ($values as &$value) {
            try {
                $value['newCiphertext'] = $target['encrypt']($value['plaintext'], $newKey);
            } catch (\Throwable $e) {
                throw new \Exception("Could not encrypt the {$value['label']} with the new key.", 0, $e);
            }

            try {
                $isRoundTrip = is_string($value['newCiphertext'])
                    && $target['decrypt']($value['newCiphertext'], $newKey) === $value['plaintext'];
            } catch (\Throwable $e) {
                throw new \Exception("Could not decrypt the re-encrypted {$value['label']} with the new key.", 0, $e);
            }

            if (!$isRoundTrip) {
                throw new \Exception("Re-encrypting the {$value['label']} with the new key did not round-trip.");
            }
        }
        unset($value);

        $this->saveValuesAndKey($values, $target, $oldKey, $newKey);

        // the rotation is committed by now, so a failure here must not be reported as nothing having changed
        foreach ($this->getCacheClearingSteps($target) as $step) {
            $this->runFollowUpStep($pluginName, $step);
        }
        $this->runFollowUpStep($pluginName, function () use ($pluginName): void {
            $this->postRotatedEvent($pluginName);
        });

        return count($values);
    }

    /**
     * Whether rotate() would rotate the key rather than skip the target. A target too malformed to tell
     * counts as having one, so that rotate() reports what is wrong with it.
     */
    public function hasConfiguredKey(mixed $target): bool
    {
        if (
            !is_array($target)
            || !$this->isValidConfigName($target['configSection'] ?? null)
            || !$this->isValidConfigName($target['configKey'] ?? null)
        ) {
            return true;
        }

        return $this->getConfiguredKey($target) !== null;
    }

    private function isValidConfigName(mixed $name): bool
    {
        // Config strips other characters from names when saving, so the key would be written under another name
        return is_string($name) && preg_match('/^[a-zA-Z0-9_-]+$/', $name) === 1;
    }

    private function getConfiguredKey(array $target): ?string
    {
        $section = Config::getInstance()->{$target['configSection']};
        $key = $section[$target['configKey']] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    private function runFollowUpStep(string $pluginName, callable $step): void
    {
        try {
            $step();
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'The encryption key of {plugin} was rotated, but a follow-up step failed: {message}',
                ['plugin' => $pluginName, 'message' => $e->getMessage()]
            );
        }
    }

    /**
     * @return callable[] each clears one cache, and runs even when an earlier one failed
     */
    private function getCacheClearingSteps(array $target): array
    {
        return [
            function (): void {
                // the factory keeps every settings storage it loaded for the rest of the request, old ciphertext included
                StaticContainer::getContainer()->set(SettingsStorageFactory::class, new SettingsStorageFactory());
            },
            function (): void {
                // the system settings objects cached here hold storages from the replaced factory
                PiwikCache::getTransientCache()->delete(CacheId::languageAware('AllSystemSettings'));
            },
            function () use ($target): void {
                foreach ($target['options'] ?? [] as $optionName) {
                    Option::clearCachedOption($optionName);
                }
            },
            function (): void {
                SettingsCache::clearCache();
            },
        ];
    }

    private function postRotatedEvent(string $pluginName): void
    {
        /**
         * Triggered after a plugin's encryption key was replaced and all of its stored values were
         * re-encrypted with the new key.
         *
         * Plugins can use this to discard anything they cached that was decrypted or encrypted with the
         * old key. Option and settings caches are already cleared when this event is posted, unless clearing one
         * failed, which is logged as a warning. A settings object created before the rotation still holds the
         * old values, so create it again before saving it.
         * An exception thrown by a listener is logged as a warning and does not undo the rotation.
         *
         * **Example**
         *
         *     public function onEncryptionKeyRotated(string $pluginName): void
         *     {
         *         if ($pluginName === 'MyPlugin') {
         *             $this->clearCachedAccessToken();
         *         }
         *     }
         *
         * @param string $pluginName The plugin whose key was rotated.
         */
        Piwik::postEvent('CoreAdminHome.encryptionKeyRotated', [$pluginName]);
    }

    /**
     * @phpstan-assert array $target
     */
    private function checkTarget(string $pluginName, mixed $target): void
    {
        if (!is_array($target)) {
            throw new \Exception("The encryption key rotation target of $pluginName is not an array.");
        }

        foreach (['configSection', 'configKey'] as $key) {
            if (!$this->isValidConfigName($target[$key] ?? null)) {
                throw new \Exception("The encryption key rotation target of $pluginName has no valid '$key'.");
            }
        }

        if (!empty(Config::getInstance()->getFromGlobalConfig($target['configSection']))) {
            throw new \Exception(
                "The encryption key rotation target of $pluginName points at the core config section [{$target['configSection']}], which cannot be rotated."
            );
        }

        foreach (['isEncrypted', 'decrypt', 'encrypt'] as $key) {
            if (!isset($target[$key]) || !is_callable($target[$key])) {
                throw new \Exception("The encryption key rotation target of $pluginName has no callable '$key'.");
            }
        }

        foreach (['options', 'pluginSettings', 'siteSettings'] as $key) {
            if (!isset($target[$key])) {
                continue;
            }

            $isListOfNames = is_array($target[$key]) && [] === array_filter($target[$key], function ($name): bool {
                return !is_string($name) || $name === '';
            });
            if (!$isListOfNames) {
                throw new \Exception("The encryption key rotation target of $pluginName has a '$key' that is not a list of names.");
            }
        }
    }

    /**
     * @return array<int, array{table: string, where: array<string, string|int>, column: string, ciphertext: string, label: string}>
     */
    private function getEncryptedValues(string $pluginName, array $target): array
    {
        $values = [];

        // a name listed twice would read the same row twice, and the second compare-and-swap would always fail
        foreach (array_unique($target['options'] ?? []) as $optionName) {
            $optionValue = Db::fetchOne(
                'SELECT `option_value` FROM `' . Common::prefixTable('option') . '` WHERE `option_name` = ?',
                [$optionName]
            );

            $values[] = [
                'table' => 'option',
                'where' => ['option_name' => $optionName],
                'column' => 'option_value',
                'ciphertext' => $optionValue,
                'label' => "option '$optionName'",
            ];
        }

        $this->checkNoUserSettingIsEncrypted($pluginName, $target);

        foreach (array_unique($target['pluginSettings'] ?? []) as $settingName) {
            $rows = Db::fetchAll(
                'SELECT `idplugin_setting`, `setting_value`, `json_encoded` FROM `' . Common::prefixTable('plugin_setting') . '`'
                . ' WHERE `plugin_name` = ? AND `user_login` = ? AND `setting_name` = ?',
                [$pluginName, '', $settingName]
            );

            foreach ($rows as $row) {
                $label = "system setting '$settingName'";
                $this->checkIsNotJsonEncoded($row, $label);

                $values[] = [
                    'table' => 'plugin_setting',
                    // by id: legacy multi-row settings can hold several rows with identical ciphertext
                    'where' => ['idplugin_setting' => (int) $row['idplugin_setting']],
                    'column' => 'setting_value',
                    'ciphertext' => $row['setting_value'],
                    'label' => $label,
                ];
            }
        }

        $siteSettingNames = array_values(array_unique($target['siteSettings'] ?? []));
        if (!empty($siteSettingNames)) {
            // one query for all names, as each one scans the table: its index starts with idsite, not plugin_name
            $rows = Db::fetchAll(
                'SELECT `idsite_setting`, `idsite`, `setting_name`, `setting_value`, `json_encoded` FROM `' . Common::prefixTable('site_setting') . '`'
                . ' WHERE `plugin_name` = ? AND `setting_name` IN (' . Common::getSqlStringFieldsArray($siteSettingNames) . ')',
                array_merge([$pluginName], $siteSettingNames)
            );

            foreach ($rows as $row) {
                $label = "site setting '{$row['setting_name']}' of site {$row['idsite']}";
                $this->checkIsNotJsonEncoded($row, $label);

                $values[] = [
                    'table' => 'site_setting',
                    'where' => ['idsite_setting' => (int) $row['idsite_setting']],
                    'column' => 'setting_value',
                    'ciphertext' => $row['setting_value'],
                    'label' => $label,
                ];
            }
        }

        return array_values(array_filter($values, function (array $value) use ($target): bool {
            return is_string($value['ciphertext'])
                && $value['ciphertext'] !== ''
                && $target['isEncrypted']($value['ciphertext']);
        }));
    }

    /**
     * A failed rotation is undone by rolling back its transaction, which leaves rows in any other table
     * encrypted with the new key after the old key is restored.
     */
    private function checkTablesAreTransactional(): void
    {
        $rows = Db::fetchAll(
            'SELECT t.`TABLE_NAME` AS `tableName`, t.`ENGINE` AS `engine` FROM `information_schema`.`TABLES` t'
            . ' LEFT JOIN `information_schema`.`ENGINES` e ON e.`ENGINE` = t.`ENGINE`'
            . ' WHERE t.`TABLE_SCHEMA` = DATABASE() AND t.`TABLE_NAME` IN (?, ?, ?)'
            . " AND (e.`TRANSACTIONS` IS NULL OR e.`TRANSACTIONS` <> 'YES')",
            [Common::prefixTable('option'), Common::prefixTable('plugin_setting'), Common::prefixTable('site_setting')]
        );

        if (!empty($rows)) {
            $tables = array_map(function (array $row): string {
                return "{$row['tableName']} ({$row['engine']})";
            }, $rows);
            throw new \Exception(
                'These tables do not support transactions, so a failed rotation could not be undone: '
                . implode(', ', $tables) . '. Convert them to InnoDB before rotating.'
            );
        }
    }

    /**
     * Per-user settings are not re-encrypted, so an encrypted one would stay encrypted with the old key.
     */
    private function checkNoUserSettingIsEncrypted(string $pluginName, array $target): void
    {
        $rows = Db::fetchAll(
            'SELECT `setting_name`, `setting_value`, `json_encoded` FROM `' . Common::prefixTable('plugin_setting') . '`'
            . ' WHERE `plugin_name` = ? AND `user_login` <> ?',
            [$pluginName, '']
        );

        foreach ($rows as $row) {
            if (!is_string($row['setting_value'])) {
                continue;
            }

            $strings = [$row['setting_value']];
            // wrapped, as array_walk_recursive() only accepts an array and a JSON value can be a plain string
            $decoded = empty($row['json_encoded']) ? null : [json_decode($row['setting_value'], true)];
            // a value that is not valid JSON is checked as it is, like a row that is not JSON-encoded
            if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                $strings = [];
                array_walk_recursive($decoded, function ($leaf) use (&$strings): void {
                    if (is_string($leaf)) {
                        $strings[] = $leaf;
                    }
                });
            }

            foreach ($strings as $string) {
                if ($target['isEncrypted']($string)) {
                    throw new \Exception("The per-user setting '{$row['setting_name']}' is encrypted, but only system settings can be re-encrypted.");
                }
            }
        }
    }

    /**
     * Skipping the row instead would rotate the key while the row stays encrypted with the old one.
     */
    private function checkIsNotJsonEncoded(array $row, string $label): void
    {
        if (!empty($row['json_encoded'])) {
            throw new \Exception("The $label is stored JSON-encoded, which cannot be re-encrypted.");
        }
    }

    private function saveValuesAndKey(
        #[\SensitiveParameter]
        array $values,
        array $target,
        #[\SensitiveParameter]
        string $oldKey,
        #[\SensitiveParameter]
        string $newKey
    ): void {
        $db = Db::get();
        if (!$db instanceof \Zend_Db_Adapter_Abstract) {
            throw new \Exception('Encryption keys can only be rotated through the regular database connection.');
        }

        $db->beginTransaction();
        $configFileBeforeSave = null;

        try {
            foreach ($values as $value) {
                $table = Common::prefixTable($value['table']);
                $conditions = [];
                $bind = [$value['newCiphertext']];

                foreach ($value['where'] as $column => $columnValue) {
                    $conditions[] = "`$column` = ?";
                    $bind[] = $columnValue;
                }

                // compare-and-swap: a value written since it was read must not be overwritten
                $conditions[] = "`{$value['column']}` = ?";
                $bind[] = $value['ciphertext'];

                $statement = Db::query(
                    "UPDATE `$table` SET `{$value['column']}` = ? WHERE " . implode(' AND ', $conditions),
                    $bind
                );

                if ($statement->rowCount() < 1) {
                    throw new \Exception("The {$value['label']} changed while rotating the encryption key. Please run the command again.");
                }
            }

            // read before saving: forceSave() can write the file and still throw from a Core.configFileChanged listener
            $configFileBeforeSave = $this->readConfigFile();
            $this->saveKey($target, $newKey);

            $db->commit();
        } catch (\Throwable $e) {
            $this->rollBack($db, $target, $oldKey, $configFileBeforeSave, $e);
        }
    }

    private function rollBack(
        \Zend_Db_Adapter_Abstract $db,
        array $target,
        #[\SensitiveParameter]
        string $oldKey,
        ?string $configFileBeforeSave,
        \Throwable $failure
    ): never {
        try {
            $db->rollBack();
        } catch (\Throwable $rollBackFailure) {
            // keep reporting the original failure, a transaction that cannot be rolled back ends with its connection
        }

        // restore the old key in memory even if the file was not written, or the next config save would write the new one
        $this->setKey($target, $oldKey);

        if ($configFileBeforeSave !== null) {
            try {
                $this->restoreConfigFile($configFileBeforeSave);
            } catch (\Throwable $restoreFailure) {
                throw new EncryptionKeyRestoreFailedException(
                    'Rotating the encryption key failed, and the old key could not be restored in ' . $this->getConfigFilePath() . '. '
                    . "Restore '{$target['configKey']}' in the [{$target['configSection']}] section from your backup. "
                    . 'The rotation failed because: ' . $failure->getMessage(),
                    0,
                    $failure
                );
            }
        }

        throw $failure;
    }

    private function saveKey(
        array $target,
        #[\SensitiveParameter]
        string $key
    ): void {
        if (!$this->isListeningForConfigWrites) {
            Piwik::addAction('Core.configFileChanged', function ($localPath) {
                $this->writtenConfigPath = $localPath;
            });
            $this->isListeningForConfigWrites = true;
        }

        $this->writtenConfigPath = null;
        $this->setKey($target, $key);
        Config::getInstance()->forceSave();

        // the event is posted only after a real write
        if ($this->writtenConfigPath === null) {
            throw new \Exception('The new encryption key was not written to ' . $this->getConfigFilePath() . '.');
        }

        // writeConfig() only logs a file it could not write correctly, so read the key back from it. Not with
        // sanityCheck(): the dumpConfig() it compares against would post Config.beforeSave a second time.
        $written = (new IniReader())->readFile($this->writtenConfigPath);
        if (($written[$target['configSection']][$target['configKey']] ?? null) !== $key) {
            throw new \Exception(
                'The new encryption key is missing from ' . $this->getConfigFilePath() . ' after saving it. The file may not have been written correctly, or a Config.beforeSave listener may keep the key out of it.'
            );
        }
    }

    private function readConfigFile(): string
    {
        $contents = @file_get_contents(Config::getInstance()->getLocalPath());
        if ($contents === false) {
            throw new \Exception($this->getConfigFilePath() . ' could not be read, so it could not be restored if saving the new key failed.');
        }

        return $contents;
    }

    /**
     * Not done with forceSave(): the config only writes what differs from the file as it was first read, and
     * with the old key back in memory nothing does, so the new key would stay in the file.
     */
    private function restoreConfigFile(string $contents): void
    {
        $config = Config::getInstance();
        $localPath = $config->getLocalPath();

        if (@file_get_contents($localPath) === $contents) {
            return;
        }

        if (@file_put_contents($localPath, $contents, LOCK_EX) === false || !$config->sanityCheck($localPath, $contents)) {
            throw new \Exception('The config file could not be written.');
        }

        // another request may have cached the config holding the new key since it was written
        try {
            StaticContainer::get(GlobalSettingsProvider::class)->getIniFileChain()->deleteConfigCache();
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'The old encryption key was restored in {path}, but the config cache could not be cleared: {message}. Run `./console core:clear-caches`.',
                ['path' => $localPath, 'message' => $e->getMessage()]
            );
        }
    }

    private function getConfigFilePath(): string
    {
        return Config::getInstance()->getLocalPath();
    }

    private function setKey(
        array $target,
        #[\SensitiveParameter]
        string $key
    ): void {
        $config = Config::getInstance();
        $section = $config->{$target['configSection']};
        $section[$target['configKey']] = $key;
        $config->{$target['configSection']} = $section;
    }
}
