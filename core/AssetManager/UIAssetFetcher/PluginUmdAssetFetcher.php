<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\AssetManager\UIAssetFetcher;

use Piwik\AssetManager\UIAssetFetcher;
use Piwik\Cache;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Plugin\Manager;
use Piwik\Theme;

class PluginUmdAssetFetcher extends UIAssetFetcher
{
    /**
     * @var string|null
     */
    private $requestedChunk;

    /**
     * @var boolean
     */
    private $loadIndividually;

    /**
     * @var int|null
     */
    private $chunkCount;

    /**
     * @param string[] $plugins
     * @param Theme|null $theme
     * @param string|null $chunk
     * @param bool|null $loadIndividually
     * @param int|null $chunkCount
     */
    public function __construct($plugins, $theme, $chunk, $loadIndividually = null, $chunkCount = null)
    {
        parent::__construct($plugins, $theme);

        if ($loadIndividually === null) {
            $loadIndividually = self::getDefaultLoadIndividually();
        }

        if ($chunkCount === null) {
            $chunkCount = self::getDefaultChunkCount();
        }

        $this->requestedChunk = ($chunk !== null && $chunk !== '') ? (string)$chunk : null;
        $this->loadIndividually = $loadIndividually;
        $this->chunkCount = $chunkCount;

        if (!$this->loadIndividually && (!is_int($chunkCount) || $chunkCount <= 0)) {
            throw new \Exception("Invalid chunk count: $chunkCount");
        }
    }

    /**
     * @return string
     */
    public function getRequestedChunkOutputFile()
    {
        return "asset_manager_chunk.{$this->requestedChunk}.js";
    }

    /**
     * @return Chunk[]
     */
    public function getChunkFiles()
    {
        $allPluginUmds = $this->getAllPluginUmds();

        if ($this->loadIndividually) {
            return $allPluginUmds;
        }

        $totalSize = $this->getTotalChunkSize($allPluginUmds);

        $chunkFiles = $this->dividePluginUmdsByChunkCount($allPluginUmds, $totalSize);

        $chunks = [];
        foreach ($chunkFiles as $index => $jsFiles) {
            $chunks[] = new Chunk($index, $jsFiles);
        }
        return $chunks;
    }

    /**
     * @param Chunk[] $allPluginUmds
     */
    private function getTotalChunkSize(array $allPluginUmds): int
    {
        $totalSize = 0;
        foreach ($allPluginUmds as $chunk) {
            $path = PIWIK_INCLUDE_PATH . '/' . $chunk->getFiles()[0];
            if (is_file($path)) {
                $totalSize += filesize($path);
            }
        }
        return $totalSize;
    }

    /**
     * @return Chunk[]
     */
    private function getAllPluginUmds(): array
    {
        $pluginsToLoadOnInit = $this->getPluginsToLoadOnInit();
        $this->checkForMissingPluginDependencies($pluginsToLoadOnInit);

        $plugins = self::orderPluginsByPluginDependencies($pluginsToLoadOnInit, false);

        $allPluginUmds = [];
        foreach ($plugins as $plugin) {
            $pluginDir = self::getRelativePluginDirectory($plugin);
            $minifiedUmd = "$pluginDir/vue/dist/$plugin.umd.min.js";
            if (!is_file(PIWIK_INCLUDE_PATH . '/' . $minifiedUmd)) {
                continue;
            }

            $allPluginUmds[] = new Chunk($plugin, [$minifiedUmd]);
        }
        return $allPluginUmds;
    }

    /**
     * @param string[] $pluginsToLoadOnInit
     */
    private function checkForMissingPluginDependencies(array $pluginsToLoadOnInit): void
    {
        $allPlugins = $this->getPluginsWithUmdsToUse();
        foreach ($pluginsToLoadOnInit as $pluginName) {
            $pluginDependencies = self::getPluginDependencies($pluginName);

            // if the plugin dependencies has a plugin that is in $allPlugins, but not in $pluginsToLoadOnInit,
            // the dependency was set to load on demand, and we report an error since this should only happen
            // during development.
            //
            // if it's not in either $allPlugins and not in $pluginsToLoadOnInit, then it's not activated
            // and we ignore it.
            if (
                !empty(array_diff($pluginDependencies, $pluginsToLoadOnInit))
                && empty(array_diff($pluginDependencies, $allPlugins))
            ) {
                throw new \Exception("Missing plugin dependency: $pluginName requires plugins "
                    . implode(', ', $pluginDependencies) . ', but one or more of these is set to load on demand. '
                    . 'Use the importPluginUmd() function to asynchronously load those plugins, instead of '
                    . 'a normal ES import ... statement.');
            }
        }
    }

    /**
     * @return string[]
     */
    private function getPluginsToLoadOnInit(): array
    {
        $plugins = $this->getPluginsWithUmdsToUse();
        $plugins = array_filter($plugins, function ($pluginName) {
            if ($pluginName === 'Login') {
                return true;
            }

            return Manager::getInstance()->isPluginLoaded($pluginName)
                && !$this->shouldLoadUmdOnDemand($pluginName);
        });
        return $plugins;
    }

    /**
     * @param Chunk[] $allPluginUmds
     * @return array<string[]>
     */
    private function dividePluginUmdsByChunkCount(array $allPluginUmds, int $totalSize): array
    {
        $chunkSizeLimit = floor($totalSize / $this->chunkCount);

        $chunkFiles = [];

        $currentChunkIndex = 0;
        $currentChunkSize = 0;
        foreach ($allPluginUmds as $pluginChunk) {
            $path = PIWIK_INCLUDE_PATH . '/' . $pluginChunk->getFiles()[0];
            if (!is_file($path)) {
                continue;
            }

            $size = filesize($path);
            $currentChunkSize += $size;

            if (
                $currentChunkSize > $chunkSizeLimit
                && !empty($chunkFiles[$currentChunkIndex])
                && $currentChunkIndex < $this->chunkCount - 1
            ) {
                ++$currentChunkIndex;
                $currentChunkSize = $size;
            }

            $chunkFiles[$currentChunkIndex][] = $pluginChunk->getFiles()[0];
        }

        return $chunkFiles;
    }

    /**
     * @return void
     */
    protected function retrieveFileLocations()
    {
        $plugins = $this->getPluginsWithUmdsToUse();
        if (empty($plugins)) {
            return;
        }

        if ($this->requestedChunk !== null && $this->requestedChunk !== '') {
            $chunkFiles = $this->getChunkFiles();

            $foundChunk = null;
            foreach ($chunkFiles as $chunk) {
                if ($chunk->getChunkName() === $this->requestedChunk) {
                    $foundChunk = $chunk;
                    break;
                }
            }

            if (!$foundChunk) {
                throw new ThingNotFoundException("Could not find chunk {$this->requestedChunk}");
            }

            foreach ($foundChunk->getFiles() as $file) {
                $this->fileLocations[] = $file;
            }

            return;
        }

        // either loadFilesIndividually = true, or being called w/ disable_merged_assets=1
        $this->addUmdFilesIfDetected($this->getPluginsWithUmdsToUse());
    }

    /**
     * @param string[] $plugins
     */
    private function addUmdFilesIfDetected(array $plugins): void
    {
        $plugins = self::orderPluginsByPluginDependencies($plugins, false);

        foreach ($plugins as $plugin) {
            if (
                Manager::getInstance()->isPluginLoaded($plugin)
                && $this->shouldLoadUmdOnDemand($plugin)
            ) {
                continue;
            }

            $fileLocation = self::getUmdFileToUseForPlugin($plugin);
            if ($fileLocation) {
                $this->fileLocations[] = $fileLocation;
            }
        }
    }

    /**
     * @param string $plugin
     * @return string|null
     */
    public static function getUmdFileToUseForPlugin($plugin)
    {
        $pluginDir = self::getRelativePluginDirectory($plugin);

        $devUmd = "$pluginDir/vue/dist/$plugin.development.umd.js";
        $minifiedUmd = "$pluginDir/vue/dist/$plugin.umd.min.js";
        $umdSrcFolder = "$pluginDir/vue/src";

        // in case there are dist files but no src files, which can happen during development
        if (is_dir(PIWIK_INCLUDE_PATH . '/' . $umdSrcFolder)) {
            if (Development::isEnabled() && is_file(PIWIK_INCLUDE_PATH . '/' . $devUmd)) {
                return $devUmd;
            } elseif (is_file(PIWIK_INCLUDE_PATH . '/' . $minifiedUmd)) {
                return $minifiedUmd;
            }
        }

        return null;
    }

    /**
     * @param string[] $plugins
     * @param bool $keepUnresolved
     * @return string[]
     */
    public static function orderPluginsByPluginDependencies($plugins, $keepUnresolved = true)
    {
        $result = [];

        while (!empty($plugins)) {
            self::visitPlugin(reset($plugins), $keepUnresolved, $plugins, $result);
        }

        return $result;
    }

    /**
     * @param string $plugin
     * @return string[]
     */
    public static function getPluginDependencies($plugin)
    {
        $pluginDir = self::getPluginDirectory($plugin);
        $umdMetadata = "$pluginDir/vue/dist/umd.metadata.json";

        $cache = Cache::getTransientCache();
        $cacheKey = 'PluginUmdAssetFetcher.pluginDependencies.' . $plugin;

        $pluginDependencies = $cache->fetch($cacheKey);
        if (!is_array($pluginDependencies)) {
            $pluginDependencies = [];
            if (is_file($umdMetadata)) {
                $dependencies = json_decode(file_get_contents($umdMetadata) ?: '', true);
                if (!empty($dependencies['dependsOn']) && is_array($dependencies['dependsOn'])) {
                    $pluginDependencies = $dependencies['dependsOn'];
                }
            }
            $cache->save($cacheKey, $pluginDependencies);
        }
        return $cache->fetch($cacheKey);
    }

    /**
     * @param string $plugin
     * @param bool $keepUnresolved
     * @param string[] $plugins
     * @param string[] $result
     * @return void
     */
    private static function visitPlugin($plugin, $keepUnresolved, &$plugins, &$result)
    {
        // remove the plugin from the array of plugins to visit
        $index = array_search($plugin, $plugins);
        if ($index !== false) {
            unset($plugins[$index]);
        } else {
            return; // already visited
        }

        // read the plugin dependencies, if any
        $pluginDependencies = self::getPluginDependencies($plugin);

        if (!empty($pluginDependencies)) {
            // visit each plugin this one depends on first, so it is loaded first
            foreach ($pluginDependencies as $pluginDependency) {
                // check if dependency is not activated
                if (
                    !in_array($pluginDependency, $plugins)
                    && !in_array($pluginDependency, $result)
                    && !$keepUnresolved
                ) {
                    return;
                }

                self::visitPlugin($pluginDependency, $keepUnresolved, $plugins, $result);
            }
        }

        // add the plugin to the load order after visiting its dependencies
        $result[] = $plugin;
    }

    protected function getPriorityOrder()
    {
        // the JS files are already ordered properly so this result doesn't matter
        return [];
    }

    private static function getRelativePluginDirectory(string $plugin): string
    {
        return Manager::getRelativePluginDirectory($plugin);
    }

    private static function getPluginDirectory(string $plugin): string
    {
        return Manager::getInstance()->getPluginDirectory($plugin);
    }

    /**
     * @return bool
     */
    public static function getDefaultLoadIndividually()
    {
        return (Config::getInstance()->General['assets_umd_load_individually'] ?? 0) == 1;
    }

    /**
     * @return int
     */
    public static function getDefaultChunkCount()
    {
        return (int)(Config::getInstance()->General['assets_umd_chunk_count'] ?? 3);
    }

    private function shouldLoadUmdOnDemand(string $pluginName): bool
    {
        try {
            /** @var string[] $pluginsToNotLoadOnDemand */
            $pluginsToNotLoadOnDemand = StaticContainer::get('plugins.shouldNotLoadOnDemand');
        } catch (\Exception $e) {
            // ignore errors, as this might be loaded during the update, before it is defined
            $pluginsToNotLoadOnDemand = [];
        }
        if (in_array($pluginName, $pluginsToNotLoadOnDemand)) {
            return false;
        }

        try {
            /** @var string[] $pluginsToLoadOnDemand */
            $pluginsToLoadOnDemand = StaticContainer::get('plugins.shouldLoadOnDemand');
        } catch (\Exception $e) {
            // ignore errors, as this might be loaded during the update, before it is defined
            $pluginsToLoadOnDemand = [];
        }
        if (in_array($pluginName, $pluginsToLoadOnDemand)) {
            return true;
        }

        // check if method exists before calling since during the update from the previous version to this one,
        // there may be a Plugin instance in memory that does not have this method.
        $plugin = Manager::getInstance()->getLoadedPlugin($pluginName);
        return method_exists($plugin, 'shouldLoadUmdOnDemand') && $plugin->shouldLoadUmdOnDemand();
    }

    /**
     * @return string[]
     */
    private function getPluginsWithUmdsToUse(): array
    {
        $plugins = $this->plugins;
        // Login UMDs must always be used, even if there's another login plugin being used
        $plugins[] = 'Login';
        return array_unique($plugins);
    }
}
