<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\AssetManager\UIAssetFetcher;

use Piwik\AssetManager\UIAssetFetcher;
use Piwik\Cache;
use Piwik\Config;
use Piwik\Development;
use Piwik\Plugin\Manager;

class PluginUmdAssetFetcher extends UIAssetFetcher
{
    /**
     * @var string
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

    public function __construct($plugins, $theme, $chunk, $loadIndividually = null, $chunkCount = null)
    {
        parent::__construct($plugins, $theme);

        if ($loadIndividually === null) {
            $loadIndividually = self::getDefaultLoadIndividually();
        }

        if ($chunkCount === null) {
            $chunkCount = self::getDefaultChunkCount();
        }

        $this->requestedChunk = $chunk;
        $this->loadIndividually = $loadIndividually;
        $this->chunkCount = $chunkCount;

        if (!$this->loadIndividually && (!is_int($chunkCount) || $chunkCount <= 0)) {
            throw new \Exception("Invalid chunk count: $chunkCount");
        }
    }

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

    private function getTotalChunkSize($allPluginUmds)
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

    private function getAllPluginUmds()
    {
        $plugins = self::orderPluginsByPluginDependencies($this->plugins, false);

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

    private function dividePluginUmdsByChunkCount($allPluginUmds, $totalSize)
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

            if ($currentChunkSize > $chunkSizeLimit
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

    protected function retrieveFileLocations()
    {
        if (empty($this->plugins)) {
            return;
        }

        if ($this->requestedChunk !== null && $this->requestedChunk !== '') {
            $chunkFiles = $this->getChunkFiles();

            $foundChunk = null;
            foreach ($chunkFiles as $chunk) {
                if ($chunk->getChunkName() == $this->requestedChunk) {
                    $foundChunk = $chunk;
                    break;
                }
            }

            if (!$foundChunk) {
                throw new \Exception("Could not find chunk {$this->requestedChunk}");
            }

            foreach ($foundChunk->getFiles() as $file) {
                $this->fileLocations[] = $file;
            }

            return;
        }

        // either loadFilesIndividually = true, or being called w/ disable_merged_assets=1
        $this->addUmdFilesIfDetected($this->plugins);
   }

    private function addUmdFilesIfDetected($plugins)
    {
        $plugins = self::orderPluginsByPluginDependencies($plugins, false);

        foreach ($plugins as $plugin) {
            $fileLocation = self::getUmdFileToUseForPlugin($plugin);
            if ($fileLocation) {
                $this->fileLocations[] = $fileLocation;
            }
        }
    }

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
            } else if (is_file(PIWIK_INCLUDE_PATH . '/' . $minifiedUmd)) {
                return $minifiedUmd;
            }
        }

        return null;
    }

    public static function orderPluginsByPluginDependencies($plugins, $keepUnresolved = true)
    {
        $result = [];

        while (!empty($plugins)) {
            self::visitPlugin(reset($plugins), $keepUnresolved, $plugins, $result);
        }

        return $result;
    }

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
                $pluginDependencies = json_decode(file_get_contents($umdMetadata), true);
                $pluginDependencies = $pluginDependencies['dependsOn'] ?? [];
            }
            $cache->save($cacheKey, $pluginDependencies);
        }
        return $cache->fetch($cacheKey);
    }

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
                if (!in_array($pluginDependency, $plugins)
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

    private static function getRelativePluginDirectory($plugin)
    {
        $result = self::getPluginDirectory($plugin);

        $matomoPath = rtrim(PIWIK_INCLUDE_PATH, '/') . '/';
        $webroots = array_merge(
            Manager::getAlternativeWebRootDirectories(),
            [$matomoPath => '/']
        );

        foreach ($webroots as $webrootAbsolute => $webrootRelative) {
            if (strpos($result, $webrootAbsolute) === 0) {
                $result = str_replace($webrootAbsolute, $webrootRelative, $result);
                break;
            }
        }

        $result = ltrim($result, '/');

        return $result;
    }

    private static function getPluginDirectory($plugin)
    {
        return Manager::getInstance()->getPluginDirectory($plugin);
    }

    public static function getDefaultLoadIndividually()
    {
        return (Config::getInstance()->General['assets_umd_load_individually'] ?? 0) == 1;
    }

    public static function getDefaultChunkCount()
    {
        return (int)(Config::getInstance()->General['assets_umd_chunk_count'] ?? 3);
    }
}