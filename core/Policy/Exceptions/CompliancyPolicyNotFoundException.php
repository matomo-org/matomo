<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\Exceptions;

/**
 * Exception thrown when a provided compliance policy cannot be found.
 */
class CompliancePolicyNotFoundException extends \Exception
{
    public function __construct($policy)
    {
        parent::__construct("The compliance policy $policy cannot be found.");
    }
}
