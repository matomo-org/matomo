<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Validators;

use Attribute;
use Piwik\Piwik;

/**
 * Checks that a value names one or more websites: a positive integer, a comma-separated list of them, an
 * array of them, or `all` when allowed. This is the format {@see \Piwik\Site::getIdSitesFromIdSitesString()}
 * understands.
 *
 * Only the format is checked. Whether the websites exist and the user may access them is checked by the
 * code that uses the value.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class IdSites extends BaseValidator implements AcceptsArrayValues
{
    /**
     * @var bool
     */
    private $allowAll;

    public function __construct(bool $allowAll = false)
    {
        $this->allowAll = $allowAll;
    }

    public function validate($value)
    {
        // like an empty string, an empty array is left to the method (some read it as all websites)
        if ($this->isValueBare($value) || $value === []) {
            return;
        }

        if ($this->allowAll && ($value === 'all' || $value === ['all'])) {
            return;
        }

        if (is_int($value) || is_string($value)) {
            $value = explode(',', (string) $value);
        }

        if (!is_array($value)) {
            throw new Exception($this->getErrorMessage());
        }

        $numberOfIds = 0;
        foreach ($value as $id) {
            $id = is_string($id) ? trim($id) : $id;

            // empty entries are skipped when the list is parsed, see Site::getIdSitesFromIdSitesString()
            if ($id === null || $id === '') {
                continue;
            }

            if ((!is_int($id) && !is_string($id)) || (string) (int) $id !== (string) $id || (int) $id <= 0) {
                throw new Exception($this->getErrorMessage());
            }

            $numberOfIds++;
        }

        if ($numberOfIds === 0) {
            throw new Exception($this->getErrorMessage());
        }
    }

    private function getErrorMessage(): string
    {
        return Piwik::translate($this->allowAll ? 'General_ValidatorErrorNotIdSitesOrAll' : 'General_ValidatorErrorNotIdSites');
    }
}
