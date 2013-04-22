<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

require_once PIWIK_INCLUDE_PATH . "/libs/pChart2.1.3/class/pDraw.class.php";
require_once PIWIK_INCLUDE_PATH . "/libs/pChart2.1.3/class/pImage.class.php";
require_once PIWIK_INCLUDE_PATH . "/libs/pChart2.1.3/class/pData.class.php";

/**
 * The Piwik_ImageGraph_StaticGraph abstract class is used as a base class for different types of static graphs.
 *
 * @package Piwik_ImageGraph
 * @subpackage Piwik_ImageGraph_StaticGraph
 */
abstract class Piwik_ImageGraph_StaticGraph
{
    const GRAPH_TYPE_BASIC_LINE = "evolution";
    const GRAPH_TYPE_VERTICAL_BAR = "verticalBar";
    const GRAPH_TYPE_HORIZONTAL_BAR = "horizontalBar";
    const GRAPH_TYPE_3D_PIE = "3dPie";
    const GRAPH_TYPE_BASIC_PIE = "pie";

    static private $availableStaticGraphTypes = array(
        self::GRAPH_TYPE_BASIC_LINE     => 'Piwik_ImageGraph_StaticGraph_Evolution',
        self::GRAPH_TYPE_VERTICAL_BAR   => 'Piwik_ImageGraph_StaticGraph_VerticalBar',
        self::GRAPH_TYPE_HORIZONTAL_BAR => 'Piwik_ImageGraph_StaticGraph_HorizontalBar',
        self::GRAPH_TYPE_BASIC_PIE      => 'Piwik_ImageGraph_StaticGraph_Pie',
        self::GRAPH_TYPE_3D_PIE         => 'Piwik_ImageGraph_StaticGraph_3DPie',
    );

    const ABSCISSA_SERIE_NAME = 'ABSCISSA';

    private $aliasedGraph;

    /**
     * @var pImage
     */
    protected $pImage;
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

    /**
     * Return the StaticGraph according to the static graph type $graphType
     *
     * @throws exception If the static graph type is unknown
     * @param string $graphType
     * @return Piwik_ImageGraph_StaticGraph
     */
    public static function factory($graphType)
    {
        if (isset(self::$availableStaticGraphTypes[$graphType])) {

            $className = self::$availableStaticGraphTypes[$graphType];
            Piwik_Loader::loadClass($className);
            return new $className;
        } else {
            throw new Exception(
                Piwik_TranslateException(
                    'General_ExceptionInvalidStaticGraphType',
                    array($graphType, implode(', ', self::getAvailableStaticGraphTypes()))
                )
            );
        }
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
     * @return rendered static graph
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
            $fontSize = Piwik_ImageGraph_API::DEFAULT_FONT_SIZE;
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
        $outputFilename = PIWIK_USER_PATH . '/tmp/assets/' . $filename;
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
        foreach ($values as $column => $data) {
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
