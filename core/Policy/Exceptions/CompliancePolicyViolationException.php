<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\Exceptions;

/**
 * Exception thrown when a setting value would break a compliance policy that is currently enforced.
 */
class CompliancePolicyViolationException extends \Exception
{
}
