<?php
/**
 * Created by PhpStorm.
 * User: thomassteur
 * Date: 10.02.14
 * Time: 14:57
 */
namespace Piwik\CliMulti;

use Piwik\Filesystem;

class Output {

    private $tmpFile  = '';

    public function __construct($outputId)
    {
        if (!Filesystem::isValidFilename($outputId)) {
            throw new \Exception('The given output id has an invalid format');
        }

        $this->tmpFile = PIWIK_INCLUDE_PATH . '/tmp/' . $outputId;
    }

    public function write($content)
    {
        file_put_contents($this->tmpFile, $content);
    }

    public function exists()
    {
        return file_exists($this->tmpFile);
    }

    public function get()
    {
        if (!$this->exists()) {
            return null;
        }

        return file_get_contents($this->tmpFile);
    }

    public function destroy()
    {
        Filesystem::deleteIfExists($this->tmpFile);
    }

}
