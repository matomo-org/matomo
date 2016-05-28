<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\CliMulti;
use Piwik\Filesystem;

class Output
{

    private $tmpFile  = '';
    private $outputId = null;

    public function __construct($outputId)
    {
        if (!Filesystem::isValidFilename($outputId)) {
            throw new \Exception('The given output id has an invalid format');
        }

        $dir = CliMulti::getTmpPath();
        Filesystem::mkdir($dir);

        $this->tmpFile  = $dir . '/' . $outputId . '.output';
        $this->outputId = $outputId;
    }

    public function getOutputId()
    {
        return $this->outputId;
    }

    public function write($content)
    {
        file_put_contents($this->tmpFile, $content);
    }

    public function getPathToFile()
    {
        return $this->tmpFile;
    }

    public function isAbnormal()
    {
        $size = Filesystem::getFileSize($this->tmpFile, 'MB');

        return $size !== null && $size >= 100;
    }

    public function exists()
    {
        return file_exists($this->tmpFile);
    }

    public function get()
    {
        return @file_get_contents($this->tmpFile);
    }

    public function destroy()
    {
        Filesystem::deleteFileIfExists($this->tmpFile);
    }
}
