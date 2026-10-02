<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\AssetManager\UIAsset;

use Exception;
use Piwik\AssetManager\UIAsset;
use Piwik\Common;
use Piwik\Filesystem;

class OnDiskUIAsset extends UIAsset
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var string
     */
    private $relativeLocation;

    /**
     * @var string
     */
    private $relativeRootDir;

    /**
     * @param string $baseDirectory
     * @param string $fileLocation
     * @param string $relativeRootDir
     */
    public function __construct($baseDirectory, $fileLocation, $relativeRootDir = '')
    {
        $this->baseDirectory = $baseDirectory;
        $this->relativeLocation = $fileLocation;

        if (
            !empty($relativeRootDir)
            && is_string($relativeRootDir)
            && !Common::stringEndsWith($relativeRootDir, '/')
        ) {
            $relativeRootDir .= '/';
        }

        $this->relativeRootDir = $relativeRootDir;
    }

    public function getAbsoluteLocation()
    {
        return $this->baseDirectory . '/' . $this->relativeLocation;
    }

    public function getRelativeLocation()
    {
        if (isset($this->relativeRootDir)) {
            return $this->relativeRootDir . $this->relativeLocation;
        }
        return $this->relativeLocation;
    }

    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    public function validateFile()
    {
        if (!$this->assetIsReadable()) {
            throw new Exception("The ui asset with 'href' = " . $this->getAbsoluteLocation() . " is not readable");
        }
    }

    public function delete()
    {
        if ($this->exists()) {
            try {
                Filesystem::remove($this->getAbsoluteLocation());
            } catch (Exception $e) {
                throw new Exception("Unable to delete merged file : " . $this->getAbsoluteLocation() . ". Please delete the file and refresh");
            }

            // try to remove compressed version of the merged file.
            Filesystem::remove($this->getAbsoluteLocation() . ".deflate", true);
            Filesystem::remove($this->getAbsoluteLocation() . ".gz", true);
        }
    }

    /**
     * @param string $content
     * @throws \Exception
     */
    public function writeContent($content): void
    {
        $location = $this->getAbsoluteLocation();

        // Write next to the file and move it in place, so a concurrent request reading the asset never
        // finds it missing or half written (rename() replaces the file atomically).
        $temporaryFile = $location . '.' . Common::getRandomString(8) . '.tmp';
        if (@file_put_contents($temporaryFile, $content) === false) {
            throw new Exception('The file : ' . $location . ' can not be opened in write mode.');
        }

        // compressed copies of the previous content would be served instead of the new one
        Filesystem::remove($location . '.deflate', true);
        Filesystem::remove($location . '.gz', true);

        if (!@rename($temporaryFile, $location)) {
            Filesystem::remove($temporaryFile, true);
            throw new Exception('The file : ' . $location . ' can not be opened in write mode.');
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return file_get_contents($this->getAbsoluteLocation());
    }

    public function exists()
    {
        return $this->assetIsReadable();
    }

    /**
     * @return boolean
     */
    private function assetIsReadable()
    {
        return is_readable($this->getAbsoluteLocation());
    }

    public function getModificationDate()
    {
        return filemtime($this->getAbsoluteLocation());
    }
}
