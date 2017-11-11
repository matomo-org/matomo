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
    const DEFAULT_WIDTH = 100;
    const DEFAULT_HEIGHT = 25;

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
        $sparkline->setWidth($this->getWidth());
        $sparkline->setHeight($this->getHeight());
        $this->setSparklineColors($sparkline);

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
                'lineColor' => '#1388db',
                'minPointColor' => '#ff7f7f',
                'lastPointColor' => '#55AAFF',
                'maxPointColor' => '#75BF7C',
                'fillColor' => '#e6f2fa'
            );
        }

        if ($colors['backgroundColor'] and strtolower($colors['backgroundColor']) !== '#ffffff') {
            $sparkline->setBackgroundColorHex($colors['backgroundColor']);
        } else {
            $sparkline->deactivateBackgroundColor();
        }
        $sparkline->setLineColorHex($colors['lineColor']);
        $sparkline->setFillColorHex($colors['fillColor']);
        $sparkline->setMinimumColorHex($colors["minPointColor"]);
        $sparkline->setMaximumColorHex($colors["maxPointColor"]);
    }

    public function render() {
        if (self::$enableSparklineImages) {
            $this->sparkline->display();
        }
        $this->sparkline->destroy();
    }
}
