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
    const MAX_WIDTH = 1000;
    const MAX_HEIGHT = 1000;


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

        $seconds = Piwik::translate('Intl_NSecondsShort');
        $percent = Piwik::translate('Intl_NumberSymbolPercent');
        $thousandSeparator = Piwik::translate('Intl_NumberSymbolGroup');
        $decimalSeparator = Piwik::translate('Intl_NumberSymbolGroup');
        $toRemove = array('%', $percent, str_replace('%s', '', $seconds));
        $values = [];
        foreach ($this->values as $value) {
            // 50% and 50s should be plotted as 50
            $value = str_replace($toRemove, '', $value);
            // replace localized decimal separator
            $value = str_replace($thousandSeparator, '', $value);
            $value = str_replace($decimalSeparator, '.', $value);
            if ($value == '') {
                $value = 0;
            }
            $values[] = $value;
        }
        $sparkline->setData($values);

        $sparkline->setWidth($this->getWidth());
        $sparkline->setHeight($this->getHeight());
        $this->setSparklineColors($sparkline);
        $sparkline->setLineThickness(1);
        $sparkline->setPadding('5');

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
        if ($width > self::MAX_WIDTH) {
            $this->_width = self::MAX_WIDTH;
        } else {
            $this->_width = (int)$width;
        }
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
        if ($height > self::MAX_HEIGHT) {
            $this->_height = self::MAX_HEIGHT;
        } else {
            $this->_height = (int)$height;
        }
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
                'fillColor' => '#ffffff'
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
            $sparkline->addPoint("minimum", 5, $colors['minPointColor']);
        }
        if (strtolower($colors['maxPointColor'] !== "#ffffff")) {
            $sparkline->addPoint("maximum", 5, $colors['maxPointColor']);
        }
        if (strtolower($colors['lastPointColor'] !== "#ffffff")) {
            $sparkline->addPoint("last", 5, $colors['lastPointColor']);
        }
    }

    public function render() {
        $this->sparkline->display();
        $this->sparkline->destroy();
    }
}
