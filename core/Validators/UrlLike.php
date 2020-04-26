<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Piwik\Piwik;
use Piwik\UrlHelper;

class UrlLike extends BaseValidator
{
    public function validate($value)
    {
        if (!UrlHelper::isLookLikeUrl($value)) {
            throw new Exception(Piwik::translate('ValidatorErrorNotUrlLike', $value));
        }
    }
}