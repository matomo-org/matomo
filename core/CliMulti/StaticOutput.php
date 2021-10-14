<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Filesystem;

class StaticOutput extends Output
{

    private $content  = '';
    private $outputId = null;

    public function __construct($outputId)
    {
        if (!Filesystem::isValidFilename($outputId)) {
            throw new \Exception('The given output id has an invalid format');
        }

        $this->outputId = $outputId;
    }

    public function getOutputId()
    {
        return $this->outputId;
    }

    public function write($content)
    {
        $this->content = $content;
    }

    public function getPathToFile()
    {
        return '';
    }

    public function isAbnormal()
    {
        $size = Common::mb_strlen($this->content) / 1024 / 1024;

        return $size !== null && $size >= 100;
    }

    public function exists()
    {
        return !empty($this->content);
    }

    public function get()
    {
        return $this->content;
    }

    public function destroy()
    {
        $this->content = '';
    }
}
