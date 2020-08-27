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
use Piwik\Piwik;

/**
 * A {@link DataTable} filter that replaces range label columns with prettier,
 * human-friendlier versions.
 *
 * When reports that summarize data over a set of ranges (such as the
 * reports in the **VisitorInterest** plugin) are archived, they are
 * archived with labels that read as: '$min-$max' or '$min+'. These labels
 * have no units and can look like '1-1'.
 *
 * This filter can be used to clean up and add units to those range labels. To
 * do this, you supply a string to use when the range specifies only
 * one unit (ie '1-1') and another format string when the range specifies
 * more than one unit (ie '2-2', '3-5' or '6+').
 *
 * This filter can be extended to vary exactly how ranges are prettified based
 * on the range values found in the DataTable. To see an example of this,
 * take a look at the {@link BeautifyTimeRangeLabels} filter.
 *
 * **Basic usage example**
 *
 *     $dataTable->queueFilter('BeautifyRangeLabels', array("1 visit", "%s visits"));
 *
 * @api
 */
class BeautifyRangeLabels extends ColumnCallbackReplace
{
    /**
     * The string to use when the range being beautified is between 1-1 units.
     * @var string
     */
    protected $labelSingular;

    /**
     * The format string to use when the range being beautified references more than
     * one unit.
     * @var string
     */
    protected $labelPlural;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered.
     * @param string $labelSingular The string to use when the range being beautified
     *                              is equal to '1-1 units', eg `"1 visit"`.
     * @param string $labelPlural The string to use when the range being beautified
     *                            references more than one unit. This must be a format
     *                            string that takes one string parameter, eg, `"%s visits"`.
     */
    public function __construct($table, $labelSingular, $labelPlural)
    {
        parent::__construct($table, 'label', array($this, 'beautify'), array());

        $this->labelSingular = $labelSingular;
        $this->labelPlural   = $labelPlural;
    }

    /**
     * Beautifies a range label and returns the pretty result. See {@link BeautifyRangeLabels}.
     *
     * @param string $value The range string. This must be in either a '$min-$max' format
     *                        a '$min+' format.
     * @return string  The pretty range label.
     */
    public function beautify($value)
    {
        // if there's more than one element, handle as a range w/ an upper bound
        if (strpos($value, "-") !== false) {
            // get the range
            sscanf($value, "%d - %d", $lowerBound, $upperBound);

            // if the lower bound is the same as the upper bound make sure the singular label
            // is used
            if ($lowerBound == $upperBound) {
                return $this->getSingleUnitLabel($value, $lowerBound);
            } else {
                return $this->getRangeLabel($value, $lowerBound, $upperBound);
            }
        } // if there's one element, handle as a range w/ no upper bound
        else {
            // get the lower bound
            sscanf($value, "%d", $lowerBound);

            if ($lowerBound !== null) {
                $plusEncoded = urlencode('+');
                $plusLen = strlen($plusEncoded);
                $len = strlen($value);

                // if the label doesn't end with a '+', append it
                if ($len < $plusLen || substr($value, $len - $plusLen) != $plusEncoded) {
                    $value .= $plusEncoded;
                }

                return $this->getUnboundedLabel($value, $lowerBound);
            } else {
                // if no lower bound can be found, this isn't a valid range. in this case
                // we assume its a translation key and try to translate it.
                return Piwik::translate(trim($value));
            }
        }
    }

    /**
     * Beautifies and returns a range label whose range spans over one unit, ie
     * 1-1, 2-2 or 3-3.
     *
     * This function can be overridden in derived types to customize beautifcation
     * behavior based on the range values.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @return string  The pretty range label.
     */
    public function getSingleUnitLabel($oldLabel, $lowerBound)
    {
        if ($lowerBound == 1) {
            return $this->labelSingular;
        } else {
            return sprintf($this->labelPlural, $lowerBound);
        }
    }

    /**
     * Beautifies and returns a range label whose range is bounded and spans over
     * more than one unit, ie 1-5, 5-10 but NOT 11+.
     *
     * This function can be overridden in derived types to customize beautifcation
     * behavior based on the range values.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @param int $upperBound The upper bound of the range.
     * @return string  The pretty range label.
     */
    public function getRangeLabel($oldLabel, $lowerBound, $upperBound)
    {
        return sprintf($this->labelPlural, $oldLabel);
    }

    /**
     * Beautifies and returns a range label whose range is unbounded, ie
     * 5+, 10+, etc.
     *
     * This function can be overridden in derived types to customize beautifcation
     * behavior based on the range values.
     *
     * @param string $oldLabel The original label value.
     * @param int $lowerBound The lower bound of the range.
     * @return string  The pretty range label.
     */
    public function getUnboundedLabel($oldLabel, $lowerBound)
    {
        return sprintf($this->labelPlural, $oldLabel);
    }
}
