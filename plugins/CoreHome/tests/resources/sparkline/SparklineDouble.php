<?php

namespace Piwik\Plugins\CoreHome\tests\resources\sparkline;

class SparklineDouble
{
    public $backgroundColor;
    public $fillColor;
    public $lineColors = [];
    public $points = [];
    public $backgroundDeactivated = false;
    public $fillDeactivated = false;

    public function setBackgroundColorHex($color)
    {
        $this->backgroundColor = $color;
    }

    public function deactivateBackgroundColor()
    {
        $this->backgroundDeactivated = true;
    }

    public function setLineColorHex($color, $seriesIndex = null)
    {
        $this->lineColors[] = [$color, $seriesIndex];
    }

    public function setFillColorHex($color)
    {
        $this->fillColor = $color;
    }

    public function deactivateFillColor()
    {
        $this->fillDeactivated = true;
    }

    public function addPoint($type, $size, $color, $seriesIndex)
    {
        $this->points[] = [$type, $size, $color, $seriesIndex];
    }
}
