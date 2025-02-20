<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\TranslationWriter\Validate;

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
        $invalids = ['/<script/i', '/javascript:[^"]/i', '/<.*src=/i', '/<.*background=/i', '/<.*onload=/i'];

        foreach ($invalids as $invalid) {
            if (preg_match($invalid, $serializedStrings) > 0) {
                $this->message = 'script tags restricted for language files';
                return false;
            }
        }

        return true;
    }
}
