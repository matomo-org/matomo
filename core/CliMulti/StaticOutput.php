<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CliMulti;

use Piwik\Common;

class StaticOutput implements OutputInterface
{
    private $content  = false;
    private $outputId = null;

    public function __construct($outputId)
    {
        $this->outputId = $outputId;
    }

    public function getOutputId()
    {
        return $this->outputId;
    }

    public function write($content)
    {
        $this->content = (string) $content;
    }

    public function getPathToFile()
    {
        return '';
    }

    public function isAbnormal(): bool
    {
        $size = Common::mb_strlen($this->content);
        $hundredMb = 100 * 1024 * 1024;

        return $size >= $hundredMb;
    }

    public function exists(): bool
    {
        return $this->content !== false;
    }

    public function get()
    {
        return $this->content;
    }

    public function destroy()
    {
        $this->content = false;
    }
}
