<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework;

use Piwik\Access;

// needed by tests that use stored segments w/ the proxy index.php
class OverrideLogin extends Access
{
    public function getLogin()
    {
        return 'superUserLogin';
    }
}