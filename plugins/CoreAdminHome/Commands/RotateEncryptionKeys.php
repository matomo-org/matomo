<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRestoreFailedException;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRotationOutcomeUnknownException;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRotator;

class RotateEncryptionKeys extends ConsoleCommand
{
    // renewed before each plugin and far longer than one plugin's rotation takes, so it only outlives a run that was killed
    private const LOCK_TTL_IN_SECONDS = 3600;

    protected function configure()
    {
        $this->setName('core:rotate-encryption-keys');
        $this->setDescription('Replaces the encryption keys plugins use for sensitive values and re-encrypts those values with the new keys.');

        $this->addRequiredValueOption(
            'plugin',
            null,
            'Only rotate the encryption key of the specified plugin. Can be used multiple times to target multiple plugins.',
            null,
            true
        );

        $this->addNoValueOption(
            'dry-run',
            null,
            'Check that the config file is writable and every stored value can be decrypted with the current keys, without changing anything.'
        );

        $this->setHelp(
            'Plugins that store sensitive values such as OAuth tokens and API keys encrypt them with a key kept in '
            . 'the local config file (config/config.ini.php by default). This command gives every activated plugin that supports it a new key, and '
            . 're-encrypts the values it declares with that key. If rotating a plugin\'s key fails with '
            . 'an error, its values and key are left unchanged, unless the command reports that the config file could '
            . 'not be restored, or that it could not tell whether the database saved the re-encrypted values, in which case it '
            . 'stops without rotating the remaining plugins. A clean-up step failing after the key was rotated is logged as a warning and does not '
            . 'undo the rotation.

⚠  Back up the local config file before rotating. Once rotated, values can no longer be decrypted with the old keys. ⚠

Set `maintenance_mode = 1` in the [General] section of the local config file, wait for running requests to finish, '
            . 'and pause archiving, queued tracking workers and other scheduled console commands while rotating. This '
            . 'console command still runs in maintenance mode. A web request or process that started with the old keys '
            . 'could otherwise save a value encrypted with an old key after the rotation, or save the config with the old '
            . 'keys and overwrite the new ones, which are stored nowhere else. Copy the local config file straight after '
            . 'rotating so the new keys can be put back if that happens, then set `maintenance_mode = 0` again.

When Matomo runs on more than one server, copy the updated local config file to every server straight after '
            . 'rotating, then run `./console core:clear-caches` on each of them. Servers still using the old keys '
            . 'cannot decrypt the re-encrypted values, and cached settings still hold the old encrypted values.

A target declared for a plugin that is not activated, or an encryption key shared by two plugins, stops the '
            . 'command before anything is rotated, even when --plugin names other plugins or with --dry-run.

The option, plugin_setting and site_setting tables must use a transactional engine such as InnoDB, so that a '
            . 'rotation that fails part-way can be undone. The command checks this before changing anything.

Usage examples:

- Check that all values can be decrypted with the current keys:
  `./console core:rotate-encryption-keys --dry-run`

- Rotate the keys of all supporting plugins:
  `./console core:rotate-encryption-keys`

- Rotate the key of one plugin only:
  `./console core:rotate-encryption-keys --plugin=Slack`'
        );
    }

    protected function doExecute(): int
    {
        $dryRun = (bool) $this->getInput()->getOption('dry-run');
        $targets = $this->getTargets();

        if (empty($targets)) {
            $this->getOutput()->writeln('No activated plugin stores values encrypted with a rotatable key.');
            return self::SUCCESS;
        }

        $pluginNamesToRotate = array_keys(array_filter($targets, [new EncryptionKeyRotator(), 'hasConfiguredKey']));
        if (!$dryRun && !empty($pluginNamesToRotate) && !$this->askForRotateConfirmation($pluginNamesToRotate)) {
            return self::SUCCESS;
        }

        // each rotation saves the whole config, so two runs at once could overwrite each other's new keys
        $lock = new Lock(StaticContainer::get(LockBackend::class), 'CoreAdminHome.rotateEncryptionKeys');
        if (!$dryRun && !$lock->acquireLock('', self::LOCK_TTL_IN_SECONDS)) {
            $this->getOutput()->writeln(
                '<error>Another encryption key rotation is running. If an earlier run was interrupted, try again in an hour.</error>'
            );
            return self::FAILURE;
        }

        try {
            if (!$dryRun) {
                // the config was loaded before the lock was taken, so it can hold keys another run has replaced since
                StaticContainer::get(GlobalSettingsProvider::class)->reload();
            }

            return $this->rotateAll($targets, $dryRun, $lock);
        } finally {
            $lock->unlock();
        }
    }

    private function rotateAll(array $targets, bool $dryRun, Lock $lock): int
    {
        $rotator = new EncryptionKeyRotator();
        $hasFailed = false;

        foreach ($targets as $pluginName => $target) {
            if (!$dryRun && !$lock->extendLock(self::LOCK_TTL_IN_SECONDS)) {
                $this->getOutput()->writeln(
                    "<error>The lock expired before rotating $pluginName, so another run may have started. Stopped without rotating the remaining plugins.</error>"
                );
                return self::FAILURE;
            }

            try {
                $count = $rotator->rotate($pluginName, $target, $dryRun);
            } catch (\Throwable $e) {
                $hasFailed = true;
                $this->getOutput()->writeln("<error>$pluginName: failed. {$e->getMessage()}</error>");
                if ($e->getPrevious() !== null && $this->getOutput()->isVerbose()) {
                    $this->getOutput()->writeln("<error>$pluginName: caused by: {$e->getPrevious()->getMessage()}</error>");
                }
                if ($e instanceof EncryptionKeyRestoreFailedException) {
                    $this->getOutput()->writeln('<error>Stopped without rotating the remaining plugins, as the config file needs restoring first.</error>');
                    break;
                }
                if ($e instanceof EncryptionKeyRotationOutcomeUnknownException) {
                    $this->getOutput()->writeln('<error>Stopped without rotating the remaining plugins, as whether this rotation was saved needs checking first.</error>');
                    break;
                }
                continue;
            }

            if ($count === null) {
                $this->getOutput()->writeln("$pluginName: skipped, no encryption key is configured.");
            } elseif ($dryRun) {
                $this->getOutput()->writeln("$pluginName: $count value(s) can be decrypted and would be re-encrypted.");
            } else {
                $this->getOutput()->writeln("<info>$pluginName: key rotated, $count value(s) re-encrypted.</info>");
            }
        }

        return $hasFailed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, array>
     */
    private function getTargets(): array
    {
        $targets = [];

        /**
         * Triggered to collect the plugins whose encryption key can be rotated by the
         * `core:rotate-encryption-keys` command.
         *
         * A plugin that stores values encrypted with a key from the local config file (`config/config.ini.php` by default) adds an entry keyed
         * by its plugin name. Every listed value that `isEncrypted` accepts is decrypted with the old key and
         * re-encrypted with a newly generated one, and the new key is saved to the config.
         *
         * Only system plugin settings, site settings and options with a fixed name can be declared, and a
         * declared setting must hold a single string rather than a list. Values in per-user plugin settings,
         * or in options named at runtime, would not be re-encrypted and could not be decrypted after the
         * rotation, so a plugin storing any must not opt in. A rotation fails while a per-user setting of the
         * plugin holds a value `isEncrypted` accepts.
         *
         * **Example**
         *
         *     public function getEncryptionKeyRotationTargets(array &$targets): void
         *     {
         *         $targets['MyPlugin'] = [
         *             'configSection' => 'MyPlugin',
         *             'configKey' => 'encryption_key',
         *             'options' => ['MyPlugin.oauthToken'],
         *             'pluginSettings' => ['apiKey'],
         *             'siteSettings' => ['siteToken'],
         *             'isEncrypted' => function (string $value): bool { ... },
         *             'decrypt' => function (string $value, string $key): string { ... },
         *             'encrypt' => function (string $value, string $key): string { ... },
         *         ];
         *     }
         *
         * @param array &$targets An array keyed by plugin name. Each entry holds:
         *
         *                        - **configSection** (string): the config section holding the key. It must not
         *                          be a section that config/global.ini.php also defines.
         *                        - **configKey** (string): the name of the key within that section.
         *                        - **options** (string[], optional): names of options holding encrypted values.
         *                        - **pluginSettings** (string[], optional): names of the plugin's system
         *                          settings holding encrypted values.
         *                        - **siteSettings** (string[], optional): names of the plugin's measurable
         *                          settings holding encrypted values, for every site.
         *                        - **isEncrypted** (callable): receives a stored value and returns whether it
         *                          is encrypted. Values it rejects are left untouched.
         *                        - **decrypt** (callable): receives an encrypted value and the raw key from
         *                          the config, and returns the plaintext. Throws when it cannot decrypt.
         *                        - **encrypt** (callable): receives a plaintext value and a raw key, and
         *                          returns the encrypted value. New keys are 32 random bytes, base64 encoded.
         */
        Piwik::postEvent('CoreAdminHome.getEncryptionKeyRotationTargets', [&$targets]);

        $this->checkEveryPluginIsActivated($targets);
        $this->checkNoKeyIsShared($targets);

        $pluginNames = $this->getInput()->getOption('plugin');
        if (empty($pluginNames)) {
            return $targets;
        }

        $unknownPluginNames = array_diff($pluginNames, array_keys($targets));
        if (!empty($unknownPluginNames)) {
            throw new \Exception(
                'These plugins are not activated or do not store values encrypted with a rotatable key: '
                . implode(', ', $unknownPluginNames)
            );
        }

        return array_intersect_key($targets, array_flip($pluginNames));
    }

    /**
     * Settings are looked up by the plugin name a target is filed under, so a misspelt name would find none
     * and still rotate the key they are encrypted with.
     */
    private function checkEveryPluginIsActivated(array $targets): void
    {
        $pluginManager = PluginManager::getInstance();
        $unknownPluginNames = array_filter(array_keys($targets), function ($pluginName) use ($pluginManager): bool {
            return !is_string($pluginName) || !$pluginManager->isPluginActivated($pluginName);
        });

        if (!empty($unknownPluginNames)) {
            throw new \Exception(
                'Encryption key rotation targets were declared for plugins that are not activated: ' . implode(', ', $unknownPluginNames)
            );
        }
    }

    /**
     * Rotating a key shared by two plugins would leave the second plugin's values encrypted with a key that
     * no longer exists.
     */
    private function checkNoKeyIsShared(array $targets): void
    {
        $pluginNamesByKey = [];
        foreach ($targets as $pluginName => $target) {
            if (is_string($target['configSection'] ?? null) && is_string($target['configKey'] ?? null)) {
                $pluginNamesByKey["[{$target['configSection']}] {$target['configKey']}"][] = $pluginName;
            }
        }

        foreach ($pluginNamesByKey as $key => $pluginNames) {
            if (count($pluginNames) > 1) {
                throw new \Exception("These plugins share the encryption key $key, so it cannot be rotated: " . implode(', ', $pluginNames));
            }
        }
    }

    /**
     * @param string[] $pluginNames
     */
    private function askForRotateConfirmation(array $pluginNames): bool
    {
        if (!$this->getInput()->isInteractive()) {
            return true;
        }

        return $this->askForConfirmation(
            '<comment>You are about to rotate the encryption keys of ' . implode(', ', $pluginNames)
            . '. Values can no longer be decrypted with the old keys afterwards. Have you backed up '
            . Config::getInstance()->getLocalPath() . ' and do you want to continue? (y/N)</comment> ',
            false
        );
    }
}
