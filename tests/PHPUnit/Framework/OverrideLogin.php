<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Access;

// needed by tests that use stored segments w/ the proxy index.php
/**
 * @since 2.8.0
 */
class OverrideLogin extends Access
{
    public function getLogin()
    {
        return 'superUserLogin';
    }
}
