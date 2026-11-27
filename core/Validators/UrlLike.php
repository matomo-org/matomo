<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Validators;

use Piwik\Piwik;
use Piwik\UrlHelper;

/**
 * Validator that fails for a value that does not look like a URL. Empty values fail too.
 *
 * @api
 */
class UrlLike extends BaseValidator
{
    /**
     * Validates the given value and throws an exception if it is invalid.
     *
     * @param mixed $value The value to validate.
     */
    public function validate($value)
    {
        if (!UrlHelper::isLookLikeUrl($value)) {
            throw new Exception(Piwik::translate('ValidatorErrorNotUrlLike', $value));
        }
    }
}
