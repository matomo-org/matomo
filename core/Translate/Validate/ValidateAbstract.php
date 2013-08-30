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

namespace Piwik\Translate\Validate;

/**
 * @package Piwik
 * @subpackage Piwik_Db
 */
abstract class ValidateAbstract
{
    protected $_baseTranslations = array();

    protected $_error = null;

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
     * Returns if the given translations are valid
     *
     * @param array $translations
     *
     * @return boolean
     *
     */
    abstract public function isValid($translations);

    /**
     * Returns the error occured while validating
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }
}