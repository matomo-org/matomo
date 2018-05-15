<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\TranslationWriter\Validate;

abstract class ValidateAbstract
{
    protected $message = null;

    /**
     * Returns if the given translations are valid
     *
     * @param array $translations
     *
     * @return boolean
     */
    abstract public function isValid($translations);

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false.
     *
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }
}
