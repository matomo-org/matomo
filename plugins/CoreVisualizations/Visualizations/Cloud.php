<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\View;
use Piwik\ViewDataTable\Visualization;

/**
 * Generates a tag cloud from a given data array.
 * The generated tag cloud can be in PHP format, or in HTML.
 *
 * Inspired from Derek Harvey (www.derekharvey.co.uk)
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Cloud extends Visualization
{
    const ID = 'cloud';

    /**
     * Whether to display the logo assocatied with a DataTable row (stored as 'logo' row metadata)
     * instead of the label in Tag Clouds.
     *
     * Default value: false
     */
    const DISPLAY_LOGO_INSTEAD_OF_LABEL = 'display_logo_instead_of_label';

    /** Used by integration tests to make sure output is consistent. */
    public static $debugDisableShuffle = false;

    public static $overridableProperties = array('display_logo_instead_of_label');

    protected $wordsArray = array();
    public $truncatingLimit = 50;

    protected $templateFile = "@CoreVisualizations/_dataTableViz_tagCloud.twig";

    public function init($view)
    {
        $self = $this;
        $view->after_data_loaded_functions[] = function ($dataTable, $view) use ($self) {
            if ($dataTable->getRowsCount() == 0) {
                return;
            }

            $columnToDisplay = isset($view->columns_to_display[1]) ? $view->columns_to_display[1] : 'nb_visits';

            $labelMetadata = array();
            foreach ($dataTable->getRows() as $row) {
                $logo = false;
                if ($view->visualization_properties->display_logo_instead_of_label) {
                    $logo = $row->getMetadata('logo');
                }

                $label = $row->getColumn('label');

                $labelMetadata[$label] = array(
                    'logo' => $logo,
                    'url'  => $row->getMetadata('url'),
                );

                $self->addWord($label, $row->getColumn($columnToDisplay));
            }
            $cloudValues = $self->getCloudValues();
            foreach ($cloudValues as &$value) {
                $value['logoWidth'] = round(max(16, $value['percent']));
            }

            $self->labelMetadata = $labelMetadata;
            $self->cloudValues = $cloudValues;
        };
    }

    public static function getDefaultPropertyValues()
    {
        return array(
            'show_offset_information'     => false,
            'show_exclude_low_population' => false,
            'show_limit_control'          => false,
            'visualization_properties'    => array(
                'cloud' => array(
                    'display_logo_instead_of_label' => false,
                )
            )
        );
    }

    /**
     * Assign word to array
     * @param string $word
     * @param int $value
     * @return string
     */
    public function addWord($word, $value = 1)
    {
        if (isset($this->wordsArray[$word])) {
            $this->wordsArray[$word] += $value;
        } else {
            $this->wordsArray[$word] = $value;
        }
    }

    public function getCloudValues()
    {
        $this->shuffleCloud();
        $return = array();
        if (empty($this->wordsArray)) {
            return array();
        }
        $maxValue = max($this->wordsArray);
        foreach ($this->wordsArray as $word => $popularity) {
            $wordTruncated = $word;
            if (Common::mb_strlen($word) > $this->truncatingLimit) {
                $wordTruncated = Common::mb_substr($word, 0, $this->truncatingLimit - 3) . '...';
            }

            // case hideFutureHoursWhenToday=1 shows hours with no visits
            if ($maxValue == 0) {
                $percent = 0;
            } else {
                $percent = ($popularity / $maxValue) * 100;
            }
            // CSS style value
            $sizeRange = $this->getClassFromPercent($percent);

            $return[$word] = array(
                'word'          => $word,
                'wordTruncated' => $wordTruncated,
                'value'         => $popularity,
                'size'          => $sizeRange,
                'percent'       => $percent,
            );
        }
        return $return;
    }

    /**
     * Shuffle associated names in array
     */
    protected function shuffleCloud()
    {
        if (self::$debugDisableShuffle) {
            return;
        }

        $keys = array_keys($this->wordsArray);

        shuffle($keys);

        if (count($keys) && is_array($keys)) {
            $tmpArray = $this->wordsArray;
            $this->wordsArray = array();
            foreach ($keys as $key => $value)
                $this->wordsArray[$value] = $tmpArray[$value];
        }
    }

    /**
     * Get the class range using a percentage
     *
     * @param $percent
     *
     * @return int class
     */
    protected function getClassFromPercent($percent)
    {
        $mapping = array(95, 70, 50, 30, 15, 5, 0);
        foreach ($mapping as $key => $value) {
            if ($percent >= $value) {
                return $key;
            }
        }
        return 0;
    }
}
