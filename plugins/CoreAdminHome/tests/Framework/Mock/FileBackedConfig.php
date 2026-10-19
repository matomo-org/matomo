<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Framework\Mock;

use Matomo\Ini\IniReader;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;
use Piwik\Container\StaticContainer;

/**
 * A config that reads and really writes a temp file of its own, for tests that need a save to behave as it does
 * outside tests. TestConfig never writes, and as its in-memory test settings always differ from its file, it
 * would also write when nothing changed.
 */
class FileBackedConfig extends Config
{
    /**
     * @var callable|null
     */
    private $save;

    /**
     * Replaces the config of the current test with one holding the same values, read from a new temp file.
     * The container is rebuilt for every test, so the replacement lasts until the end of the test.
     */
    public static function replaceTestConfig(): self
    {
        $testConfig = Config::getInstance();
        $path = tempnam(sys_get_temp_dir(), 'matomo-config');
        file_put_contents($path, StaticContainer::get(GlobalSettingsProvider::class)->getIniFileChain()->dump());

        $settings = new GlobalSettingsProvider($testConfig->getGlobalPath(), $path, $testConfig->getCommonPath());
        $config = new self($settings);
        StaticContainer::getContainer()->set(GlobalSettingsProvider::class, $settings);
        StaticContainer::getContainer()->set(Config::class, $config);

        return $config;
    }

    /**
     * Makes forceSave() call $save with this config instead of writing the file.
     */
    public function onSave(callable $save): void
    {
        $this->save = $save;
    }

    public function forceSave()
    {
        if ($this->save === null) {
            parent::forceSave();
        } else {
            ($this->save)($this);
        }
    }

    public function deleteFile(): void
    {
        $path = $this->getLocalPath();
        if (is_dir($path)) {
            rmdir($path);
        } elseif (file_exists($path)) {
            unlink($path);
        }
    }

    public function readFile(): array
    {
        return (new IniReader())->readFile($this->getLocalPath());
    }
}
