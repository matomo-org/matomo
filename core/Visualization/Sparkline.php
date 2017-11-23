<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Visualization;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View\ViewInterface;

/**
 * Renders a sparkline image given a PHP data array.
 * Using the Sparkline PHP Graphing Library sparkline.org
 */
class Sparkline implements ViewInterface
{
    const DEFAULT_WIDTH = 200;
    const DEFAULT_HEIGHT = 50;

    public static $enableSparklineImages = true;

    /**
     * Width of the sparkline
     * @var int
     */
    protected $_width = self::DEFAULT_WIDTH;
    /**
     * Height of sparkline
     * @var int
     */
    protected $_height = self::DEFAULT_HEIGHT;
    private $values = array();
    /**
     * @var \Davaxi\Sparkline
     */
    private $sparkline;

    /**
     * Array with format: array( x, y, z, ... )
     * @param array $data
     */
    public function setValues($data) {
        $this->values = $data;
    }

    public function main() {

        $sparkline = new \Davaxi\Sparkline();
        $sparkline->setWidth($this->getWidth() - 10);
        $sparkline->setHeight($this->getHeight() - 10);
        $this->setSparklineColors($sparkline);
        $sparkline->setLineThickness(3);
        $sparkline->setDotRadius(5);
        $sparkline->setPadding(5);
        $sparkline->setTopOffset(5);

        $seconds = Piwik::translate('Intl_NSecondsShort');
        $toRemove = array('%', str_replace('%s', '', $seconds));
        $values = [];
        foreach ($this->values as $value) {
            // 50% and 50s should be plotted as 50
            $value = str_replace($toRemove, '', $value);
            // replace localized decimal separator
            $value = str_replace(',', '.', $value);
            if ($value == '') {
                $value = 0;
            }
            $values[] = $value;
        }
        $sparkline->setData($values);
        $this->sparkline = $sparkline;
    }

    /**
     * Returns the width of the sparkline
     * @return int
     */
    public function getWidth() {
        return $this->_width;
    }

    /**
     * Sets the width of the sparkline
     * @param int $width
     */
    public function setWidth($width) {
        if (!is_numeric($width) || $width <= 0) {
            return;
        }

        $this->_width = (int)$width;
    }

    /**
     * Returns the height of the sparkline
     * @return int
     */
    public function getHeight() {
        return $this->_height;
    }

    /**
     * Sets the height of the sparkline
     * @param int $height
     */
    public function setHeight($height) {
        if (!is_numeric($height) || $height <= 0) {
            return;
        }

        $this->_height = (int)$height;
    }

    /**
     * Sets the sparkline colors
     *
     * @param \Davaxi\Sparkline $sparkline
     */
    private function setSparklineColors($sparkline) {
        $colors = Common::getRequestVar('colors', false, 'json');

        if (empty($colors)) { // quick fix so row evolution sparklines will have color in widgetize's iframes
            $colors = array(
                'backgroundColor' => '#ffffff',
                'lineColor' => '#162C4A',
                'minPointColor' => '#ff7f7f',
                'maxPointColor' => '#75BF7C',
                'lastPointColor' => '#55AAFF',
                'fillColor' => '#fce8e7'
            );
        }

        if (strtolower($colors['backgroundColor']) !== '#ffffff') {
            $sparkline->setBackgroundColorHex($colors['backgroundColor']);
        } else {
            $sparkline->deactivateBackgroundColor();
        }
        $sparkline->setLineColorHex($colors['lineColor']);
        if (strtolower($colors['fillColor'] !== "#ffffff")) {
            $sparkline->setFillColorHex($colors['fillColor']);
        } else {
            $sparkline->deactivateFillColor();
        }
        if (strtolower($colors['minPointColor'] !== "#ffffff")) {
            $sparkline->setMinimumColorHex($colors['minPointColor']);
        }
        if (strtolower($colors['maxPointColor'] !== "#ffffff")) {
            $sparkline->setMaximumColorHex($colors['maxPointColor']);
        }
        if (strtolower($colors['lastPointColor'] !== "#ffffff")) {
            $sparkline->setLastPointColorHex($colors['lastPointColor']);
        }
    }

    public function render() {
        if (self::$enableSparklineImages) {
            $this->sparkline->display();
        }
        $this->sparkline->destroy();
    }
}
