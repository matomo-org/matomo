<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\AssetManager\UIAsset;

use Exception;
use Piwik\AssetManager\UIAsset;

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
     * @param string $baseDirectory
     * @param string $fileLocation
     */
    function __construct($baseDirectory, $fileLocation)
    {
        $this->baseDirectory = $baseDirectory;
        $this->relativeLocation = $fileLocation;
    }

    public function getAbsoluteLocation()
    {
        return $this->baseDirectory . '/' . $this->relativeLocation;
    }

    public function getRelativeLocation()
    {
        return $this->relativeLocation;
    }

    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    public function validateFile()
    {
        if (!$this->assetIsReadable())
            throw new Exception("The ui asset with 'href' = " . $this->getAbsoluteLocation() . " is not readable");
    }

    public function delete()
    {
        if ($this->exists()) {

            if (!unlink($this->getAbsoluteLocation()))
                throw new Exception("Unable to delete merged file : " . $this->getAbsoluteLocation() . ". Please delete the file and refresh");

            // try to remove compressed version of the merged file.
            @unlink($this->getAbsoluteLocation() . ".deflate");
            @unlink($this->getAbsoluteLocation() . ".gz");
        }
    }

    /**
     * @param string $content
     * @throws \Exception
     */
    public function writeContent($content)
    {
        $this->delete();

        $newFile = @fopen($this->getAbsoluteLocation(), "w");

        if (!$newFile)
            throw new Exception ("The file : " . $newFile . " can not be opened in write mode.");

        fwrite($newFile, $content);

        fclose($newFile);
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
