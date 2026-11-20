<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

/**
 * Thrown when a failed rotation could not put the old encryption key back in the config file.
 */
class EncryptionKeyRestoreFailedException extends \Exception
{
}
