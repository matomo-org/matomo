<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\AssetManager\UIAssetFetcher;

class Chunk
{
    /**
     * @var string
     */
    private $chunkName;

    /**
     * @var string[]
     */
    private $files;

    public function __construct($chunkName, $files)
    {
        $this->chunkName = $chunkName;
        $this->files = $files;
    }

    /**
     * @return string
     */
    public function getOutputFile(): string
    {
        return "asset_manager_chunk.{$this->chunkName}.js";
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param string[] $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * @return string
     */
    public function getChunkName(): string
    {
        return $this->chunkName;
    }

    /**
     * @param string $chunkName
     */
    public function setChunkName(string $chunkName): void
    {
        $this->chunkName = $chunkName;
    }
}