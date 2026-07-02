<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Policy\Exceptions;

use Piwik\Exception\MessageOnlyException;

class DisabledByCompliancePolicyException extends \Exception implements MessageOnlyException
{
}
