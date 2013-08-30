<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Translate\Filter;

/**
 * @package Piwik
 * @subpackage Piwik_Db
 */
abstract class FilterAbstract
{
    protected $_filteredData = array();

    protected $_baseTranslations = array();

    /**
     * Sets base translations
     *
     * @param array $baseTranslations
     */
    public function __construct($baseTranslations=array())
    {
        $this->_baseTranslations = $baseTranslations;
    }

    /**
     * Filter the given translations
     *
     * @param array $translations
     *
     * @return array   filtered translations
     *
     */
    abstract public function filter($translations);

    /**
     * Returnes the data filtered out by the filter
     *
     * @return array
     */
    public function getFilteredData()
    {
        return $this->_filteredData;
    }
}