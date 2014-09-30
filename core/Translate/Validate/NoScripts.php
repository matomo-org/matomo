<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Translate\Validate;

/**
 */
class NoScripts extends ValidateAbstract
{
    /**
     * Validates the given translations
     *  * No script like parts should be present in any part of the translations
     *
     * @param array $translations
     *
     * @return boolean
     */
    public function isValid($translations)
    {
        $this->message = null;

        // check if any translation contains restricted script tags
        $serializedStrings = serialize($translations);
        $invalids = array("<script", 'document.', 'javascript:', 'src=', 'background=', 'onload=');

        foreach ($invalids as $invalid) {
            if (stripos($serializedStrings, $invalid) !== false) {
                $this->message = 'script tags restricted for language files';
                return false;
            }
        }

        return true;
    }
}
