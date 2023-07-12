<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\Plugin\Visualization;

/**
 * Generates a tag cloud from a given data array.
 * The generated tag cloud can be in PHP format, or in HTML.
 *
 * Inspired from Derek Harvey (www.derekharvey.co.uk)
 *
 * @property Cloud\Config $config
 *
 */
class Cloud extends Visualization
{
    const ID = 'cloud';
    const TEMPLATE_FILE     = "@CoreVisualizations/_dataTableViz_tagCloud.twig";
    const FOOTER_ICON       = 'icon-tag-cloud';
    const FOOTER_ICON_TITLE = 'General_TagCloud';

    /** Used by system tests to make sure output is consistent. */
    public static $debugDisableShuffle = false;
    public $truncatingLimit = 50;

    protected $wordsArray = [];
    private $rawValues = []; // Raw values stored before metric formatting
    private $formattedValues = []; // Formatted values stored after metric formatting

    public static function getDefaultConfig()
    {
        return new Cloud\Config();
    }

    public function beforeLoadDataTable()
    {
        $this->requestConfig->request_parameters_to_modify['format_metrics'] = 0;
        $this->checkRequestIsNotForMultiplePeriods();
    }

    /**
     * First pass: metric formatting filters have not been applied, store the raw values for each word
     */
    public function afterAllFiltersAreApplied()
    {
        if ($this->dataTable->getRowsCount() == 0) {
            return;
        }

        $columnToDisplay = isset($this->config->columns_to_display[1]) ? $this->config->columns_to_display[1] : 'nb_visits';
        foreach ($this->dataTable->getRows() as $row) {
            $label = $row->getColumn('label');
            $this->rawValues[$label] = $row->getColumn($columnToDisplay);
        }
    }

    public function beforeRender()
    {
        $this->config->show_exclude_low_population = false;
        $this->config->show_offset_information     = false;
        $this->config->show_limit_control          = false;

        // Second pass: enable metric formatting, reapply the filters and then generate the tag cloud data
        $this->requestConfig->request_parameters_to_modify['format_metrics'] = 1;
        $this->applyMetricsFormatting();
        $this->generateCloudData();
    }

    private function generateCloudData()
    {
        if ($this->dataTable->getRowsCount() == 0) {
            return;
        }

        $columnToDisplay = isset($this->config->columns_to_display[1]) ? $this->config->columns_to_display[1] : 'nb_visits';
        $labelMetadata   = [];

        foreach ($this->dataTable->getRows() as $row) {
            $logo = false;
            if ($this->config->display_logo_instead_of_label) {
                $logo = $row->getMetadata('logo');
            }

            $label = $row->getColumn('label');

            $labelMetadata[$label] = [
                'logo' => $logo,
                'url'  => $row->getMetadata('url'),
            ];

            $this->addWord($label, $this->rawValues[$label]);

            $this->formattedValues[$label] = $row->getColumn($columnToDisplay);
        }

        $cloudValues = $this->getCloudValues();
        foreach ($cloudValues as &$value) {
            $value['logoWidth'] = round(max(16, $value['percent']));
        }

        $this->assignTemplateVar('labelMetadata', $labelMetadata);
        $this->assignTemplateVar('cloudColumn', $columnToDisplay);
        $this->assignTemplateVar('cloudValues', $cloudValues);
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

    private function getCloudValues()
    {
        $this->shuffleCloud();

        if (empty($this->wordsArray)) {
            return [];
        }

        $maxValue = max($this->wordsArray);

        $return = [];
        foreach ($this->wordsArray as $word => $popularity) {

            $wordTruncated = $this->truncateWordIfNeeded($word);
            $percent       = $this->getPercentage($popularity, $maxValue);
            $sizeRange     = $this->getClassFromPercent($percent);

            $return[$word] = [
                'word'          => $word,
                'wordTruncated' => $wordTruncated,
                'size'          => $sizeRange,
                'value'         => $this->formattedValues[$word],
                'percent'       => $percent,
            ];
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

            $this->wordsArray = [];
            foreach ($keys as $value) {
                $this->wordsArray[$value] = $tmpArray[$value];
            }

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
        $mapping = [95, 70, 50, 30, 15, 5, 0];
        foreach ($mapping as $key => $value) {
            if ($percent >= $value) {
                return $key;
            }
        }
        return 0;
    }

    /**
     * @param $word
     * @return string
     */
    private function truncateWordIfNeeded($word)
    {
        $word = Common::unsanitizeInputValue($word);

        if (mb_strlen($word) > $this->truncatingLimit) {
            return mb_substr($word, 0, $this->truncatingLimit - 3) . '...';
        }

        return $word;
    }

    private function getPercentage($popularity, $maxValue)
    {
        // case hideFutureHoursWhenToday=1 shows hours with no visits
        if ($maxValue == 0) {
            return 0;
        }

        $percent = ($popularity / $maxValue) * 100;

        return $percent;
    }
}
