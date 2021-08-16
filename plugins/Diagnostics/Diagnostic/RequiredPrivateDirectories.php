<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Checks whether certain directories in Matomo that should be private are accessible through the internet.
 */
class RequiredPrivateDirectories extends PrivateDirectories
{
    protected $privatePaths = [
        ['tmp/cache/tracker/matomocache_general.php'], // tmp/empty is created by this diagnostic
        ['.git/', '.git/config'],
    ];

}
