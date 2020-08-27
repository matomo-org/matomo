<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;

/**
 * A {@link DataTable} filter that replaces range labels whose values are in seconds with
 * prettier, human-friendlier versions.
 *
 * This filter customizes the behavior of the {@link BeautifyRangeLabels} filter
 * so range values that are less than one minute are displayed in seconds but
 * other ranges are displayed in minutes.
 *
 * **Basic usage**
 *
 *     $dataTable->filter('BeautifyTimeRangeLabels', array("%1$s-%2$s min", "1 min", "%s min"));
 *
 * @api
 */
class BeautifyTimeRangeLabels extends BeautifyRangeLabels
{
    /**
     * A format string used to create pretty range labels when the range's
     * lower bound is between 0 and 60.
     *
     * This format string must take two numeric parameters, one for each
     * range bound.
     */
    protected $labelSecondsPlural;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable this filter will run over.
     * @param string $labelSecondsPlural A string to use when beautifying range labels
     *                                   whose lower bound is between 0 and 60. Must be
     *                                   a format string that takes two numeric params.
     * @param string $labelMinutesSingular A string to use when replacing a range that
     *                                     equals 60-60 (or 1 minute - 1 minute).
     * @param string $labelMinutesPlural A string to use when replacing a range that
     *                                   spans multiple minutes. This must be a
     *                                   format string that takes one string parameter.
     */
    public function __construct($table, $labelSecondsPlural, $labelMinutesSingular, $labelMinutesPlural)
    {
        parent::__construct($table, $labelMinutesSingular, $labelMinutesPlural);

        $this->labelSecondsPlural = $labelSecondsPlural;
    }

    /**
     * Beautifies and returns a range label whose range spans over one unit, ie
     * 1-1, 2-2 or 3-3.
     *
     * If the lower bound of the range is less than 60 the pretty range label
     * will be in seconds. Otherwise, it will be in minutes.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @return string  The pretty range label.
     */
    public function getSingleUnitLabel($oldLabel, $lowerBound)
    {
        if ($lowerBound < 60) {
            return sprintf($this->labelSecondsPlural, $lowerBound, $lowerBound);
        } elseif ($lowerBound == 60) {
            return $this->labelSingular;
        } else {
            return sprintf($this->labelPlural, ceil($lowerBound / 60));
        }
    }

    /**
     * Beautifies and returns a range label whose range is bounded and spans over
     * more than one unit, ie 1-5, 5-10 but NOT 11+.
     *
     * If the lower bound of the range is less than 60 the pretty range label
     * will be in seconds. Otherwise, it will be in minutes.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @param int $upperBound The upper bound of the range.
     * @return string  The pretty range label.
     */
    public function getRangeLabel($oldLabel, $lowerBound, $upperBound)
    {
        if ($lowerBound < 60) {
            return sprintf($this->labelSecondsPlural, $lowerBound, $upperBound);
        } else {
            return sprintf($this->labelPlural, ceil($lowerBound / 60) . "-" . ceil($upperBound / 60));
        }
    }

    /**
     * Beautifies and returns a range label whose range is unbounded, ie
     * 5+, 10+, etc.
     *
     * If the lower bound of the range is less than 60 the pretty range label
     * will be in seconds. Otherwise, it will be in minutes.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @return string  The pretty range label.
     */
    public function getUnboundedLabel($oldLabel, $lowerBound)
    {
        if ($lowerBound < 60) {
            return sprintf($this->labelSecondsPlural, $lowerBound);
        } else {
            // since we're using minutes, we use floor so 1801s+ will be 30m+ and not 31m+
            return sprintf($this->labelPlural, "" . floor($lowerBound / 60) . urlencode('+'));
        }
    }
}
