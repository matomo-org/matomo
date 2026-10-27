<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Validators;

/**
 * Marks a validator that expects array values as well as single values.
 *
 * When validators are used as API parameter attributes, an array sent for a parameter is rejected unless
 * every validator on that parameter implements this interface.
 */
interface AcceptsArrayValues
{
}
