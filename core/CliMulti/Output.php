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

class Output {

    private $tmpFile  = '';

    public function __construct($outputId)
    {
        if (!Filesystem::isValidFilename($outputId)) {
            throw new \Exception('The given output id has an invalid format');
        }

        $dir = CliMulti::getTmpPath();
        Filesystem::mkdir($dir);

        $this->tmpFile = $dir . '/' . $outputId . '.output';
    }

    public function write($content)
    {
        file_put_contents($this->tmpFile, $content);
    }

    public function getPathToFile()
    {
        return $this->tmpFile;
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
