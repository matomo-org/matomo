<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use InvalidArgumentException;

/**
 * Raised when a request parameter was not supplied and no default value was provided.
 *
 * Distinct from the InvalidArgumentException raised for a parameter that *was* supplied but holds an
 * unusable value, so callers can answer each differently. A `null` value counts as not supplied.
 * Extends InvalidArgumentException so existing handlers keep working.
 *
 * @since Matomo 6.0.0
 */
class MissingRequestParameterException extends InvalidArgumentException
{
}
