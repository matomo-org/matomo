<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

/**
 * Thrown when committing a rotation failed and the database could not be read to tell whether it was applied.
 */
class EncryptionKeyRotationOutcomeUnknownException extends \Exception
{
}
