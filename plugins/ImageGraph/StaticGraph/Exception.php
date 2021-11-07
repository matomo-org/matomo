<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph\StaticGraph;

use CpChart\Data;
use Piwik\Plugins\ImageGraph\StaticGraph;

/**
 *
 */
class Exception extends StaticGraph
{
    const MESSAGE_RIGHT_MARGIN = 5;

    /**
     * @var \Exception
     */
    private $exception;

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    protected function getDefaultColors()
    {
        return array();
    }

    public function setWidth($width)
    {
        if (empty($width)) {
            $width = 450;
        }
        parent::setWidth($width);
    }

    public function setHeight($height)
    {
        if (empty($height)) {
            $height = 300;
        }
        parent::setHeight($height);
    }

    public function renderGraph()
    {
        $this->pData = new Data();

        $message = $this->exception->getMessage();
        list($textWidth, $textHeight) = $this->getTextWidthHeight($message);

        if ($this->width == null) {
            $this->width = $textWidth + self::MESSAGE_RIGHT_MARGIN;
        }

        if ($this->height == null) {
            $this->height = $textHeight;
        }

        $this->initpImage();

        $this->drawBackground();

        $this->pImage->drawText(
            0,
            $textHeight,
            $message,
            $this->textColor
        );
    }
}
