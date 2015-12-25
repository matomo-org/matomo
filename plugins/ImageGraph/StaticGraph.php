<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ImageGraph;

use pData;
use pImage;
use Piwik\Container\StaticContainer;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\BaseFactory;

require_once PIWIK_INCLUDE_PATH . "/libs/pChart/class/pDraw.class.php";
require_once PIWIK_INCLUDE_PATH . "/libs/pChart/class/pImage.class.php";
require_once PIWIK_INCLUDE_PATH . "/libs/pChart/class/pData.class.php";

/**
 * The StaticGraph abstract class is used as a base class for different types of static graphs.
 *
 */
abstract class StaticGraph extends BaseFactory
{
    const GRAPH_TYPE_BASIC_LINE = "evolution";
    const GRAPH_TYPE_VERTICAL_BAR = "verticalBar";
    const GRAPH_TYPE_HORIZONTAL_BAR = "horizontalBar";
    const GRAPH_TYPE_3D_PIE = "3dPie";
    const GRAPH_TYPE_BASIC_PIE = "pie";

    private static $availableStaticGraphTypes = array(
        self::GRAPH_TYPE_BASIC_LINE     => 'Evolution',
        self::GRAPH_TYPE_VERTICAL_BAR   => 'VerticalBar',
        self::GRAPH_TYPE_HORIZONTAL_BAR => 'HorizontalBar',
        self::GRAPH_TYPE_BASIC_PIE      => 'Pie',
        self::GRAPH_TYPE_3D_PIE         => 'Pie3D',
    );

    const ABSCISSA_SERIE_NAME = 'ABSCISSA';

    private $aliasedGraph;

    /**
     * @var pImage
     */
    protected $pImage;
    /**
     * @var pData
     */
    protected $pData;
    protected $ordinateLabels;
    protected $showLegend;
    protected $abscissaSeries;
    protected $abscissaLogos;
    protected $ordinateSeries;
    protected $ordinateLogos;
    protected $colors;
    protected $font;
    protected $fontSize;
    protected $textColor;
    protected $backgroundColor;
    protected $gridColor;
    protected $legendFontSize;
    protected $width;
    protected $height;
    protected $forceSkippedLabels = false;

    abstract protected function getDefaultColors();

    abstract public function renderGraph();

    protected static function getClassNameFromClassId($graphType)
    {
        $className = self::$availableStaticGraphTypes[$graphType];
        $className = __NAMESPACE__ . "\\StaticGraph\\" . $className;
        return $className;
    }

    protected static function getInvalidClassIdExceptionMessage($graphType)
    {
        return Piwik::translate(
            'General_ExceptionInvalidStaticGraphType',
            array($graphType, implode(', ', self::getAvailableStaticGraphTypes()))
        );
    }

    public static function getAvailableStaticGraphTypes()
    {
        return array_keys(self::$availableStaticGraphTypes);
    }

    /**
     * Save rendering to disk
     *
     * @param string $filename without path
     * @return string path of file
     */
    public function sendToDisk($filename)
    {
        $filePath = self::getOutputPath($filename);
        $this->pImage->Render($filePath);
        return $filePath;
    }

    /**
     * @return resource  rendered static graph
     */
    public function getRenderedImage()
    {
        return $this->pImage->Picture;
    }

    /**
     * Output rendering to browser
     */
    public function sendToBrowser()
    {
        $this->pImage->stroke();
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function setFontSize($fontSize)
    {
        if (!is_numeric($fontSize)) {
            $fontSize = API::DEFAULT_FONT_SIZE;
        }
        $this->fontSize = $fontSize;
    }

    public function setLegendFontSize($legendFontSize)
    {
        $this->legendFontSize = $legendFontSize;
    }

    public function setFont($font)
    {
        $this->font = $font;
    }

    public function setTextColor($textColor)
    {
        $this->textColor = self::hex2rgb($textColor);
    }

    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = self::hex2rgb($backgroundColor);
    }

    public function setGridColor($gridColor)
    {
        $this->gridColor = self::hex2rgb($gridColor);
    }

    public function setOrdinateSeries($ordinateSeries)
    {
        $this->ordinateSeries = $ordinateSeries;
    }

    public function setOrdinateLogos($ordinateLogos)
    {
        $this->ordinateLogos = $ordinateLogos;
    }

    public function setAbscissaLogos($abscissaLogos)
    {
        $this->abscissaLogos = $abscissaLogos;
    }

    public function setAbscissaSeries($abscissaSeries)
    {
        $this->abscissaSeries = $abscissaSeries;
    }

    public function setShowLegend($showLegend)
    {
        $this->showLegend = $showLegend;
    }

    public function setForceSkippedLabels($forceSkippedLabels)
    {
        $this->forceSkippedLabels = $forceSkippedLabels;
    }

    public function setOrdinateLabels($ordinateLabels)
    {
        $this->ordinateLabels = $ordinateLabels;
    }

    public function setAliasedGraph($aliasedGraph)
    {
        $this->aliasedGraph = $aliasedGraph;
    }

    public function setColors($colors)
    {
        $i = 0;
        foreach ($this->getDefaultColors() as $colorKey => $defaultColor) {
            if (isset($colors[$i]) && $this->hex2rgb($colors[$i])) {
                $hexColor = $colors[$i];
            } else {
                $hexColor = $defaultColor;
            }

            $this->colors[$colorKey] = $this->hex2rgb($hexColor);
            $i++;
        }
    }

    /**
     * Return $filename with temp directory and delete file
     *
     * @static
     * @param  $filename
     * @return string path of file in temp directory
     */
    protected static function getOutputPath($filename)
    {
        $outputFilename = StaticContainer::get('path.tmp') . '/assets/' . $filename;

        @chmod($outputFilename, 0600);
        @unlink($outputFilename);
        return $outputFilename;
    }

    protected function initpData()
    {
        $this->pData = new pData();

        foreach ($this->ordinateSeries as $column => $data) {
            $this->pData->addPoints($data, $column);
            $this->pData->setSerieDescription($column, $this->ordinateLabels[$column]);
            if (isset($this->ordinateLogos[$column])) {
                $ordinateLogo = $this->ordinateLogos[$column];
                $this->pData->setSeriePicture($column, $ordinateLogo);
            }
        }

        $this->pData->setAxisDisplay(0, AXIS_FORMAT_CUSTOM, '\\Piwik\\Plugins\\ImageGraph\\formatYAxis');

        $this->pData->addPoints($this->abscissaSeries, self::ABSCISSA_SERIE_NAME);
        $this->pData->setAbscissa(self::ABSCISSA_SERIE_NAME);
    }

    protected function initpImage()
    {
        $this->pImage = new pImage($this->width, $this->height, $this->pData);
        $this->pImage->Antialias = $this->aliasedGraph;

        $this->pImage->setFontProperties(
            array_merge(
                array(
                     'FontName' => $this->font,
                     'FontSize' => $this->fontSize,
                ),
                $this->textColor
            )
        );
    }

    protected function getTextWidthHeight($text, $fontSize = false)
    {
        if (!$fontSize) {
            $fontSize = $this->fontSize;
        }

        if (!$this->pImage) {
            $this->initpImage();
        }

        // could not find a way to get pixel perfect width & height info using imageftbbox
        $textInfo = $this->pImage->drawText(
            0, 0, $text,
            array(
                 'Alpha'    => 0,
                 'FontSize' => $fontSize,
                 'FontName' => $this->font
            )
        );

        return array($textInfo[1]['X'] + 1, $textInfo[0]['Y'] - $textInfo[2]['Y']);
    }

    protected function getMaximumTextWidthHeight($values)
    {
        if (array_values($values) === $values) {
            $values = array('' => $values);
        }

        $maxWidth = 0;
        $maxHeight = 0;
        foreach ($values as $data) {
            foreach ($data as $value) {
                list($valueWidth, $valueHeight) = $this->getTextWidthHeight($value);

                if ($valueWidth > $maxWidth) {
                    $maxWidth = $valueWidth;
                }

                if ($valueHeight > $maxHeight) {
                    $maxHeight = $valueHeight;
                }
            }
        }

        return array($maxWidth, $maxHeight);
    }

    protected function drawBackground()
    {
        $this->pImage->drawFilledRectangle(
            0,
            0,
            $this->width,
            $this->height,
            array_merge(array('Alpha' => 100), $this->backgroundColor)
        );
    }

    private static function hex2rgb($hexColor)
    {
        if (preg_match('/([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/', $hexColor, $matches)) {
            return array(
                'R' => hexdec($matches[1]),
                'G' => hexdec($matches[2]),
                'B' => hexdec($matches[3])
            );
        } else {
            return false;
        }
    }
}

/**
 * Global format method
 *
 * required to format y axis values using pcharts internal format callbacks
 * @param $value
 * @return mixed
 */
function formatYAxis($value)
{
    return NumberFormatter::getInstance()->format($value);
}
