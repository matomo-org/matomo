<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

if (!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', realpath(dirname(__FILE__) . "/../.."));
}

define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);

require_once PIWIK_DOCUMENT_ROOT . "/index.php";

if (!Common::isPhpCliMode()) {
    return;
}

$testmode = in_array('--testmode', $_SERVER['argv']);
if ($testmode) {
    define('PIWIK_TEST_MODE', true);

    Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables()));
}

function getPiwikDomain()
{
    foreach($_SERVER['argv'] as $param) {
        $pattern = '--matomo-domain=';
        if(false !== strpos($param, $pattern)) {
            return substr($param, strlen($pattern));
        }
    }
    return null;
}


$piwikDomain = getPiwikDomain();
if($piwikDomain) {
    Url::setHost($piwikDomain);
}

$environment = new Environment('cli');
$environment->init();

$token = Piwik::requestTemporarySystemAuthToken('LogImporter', 48);

$filename = $environment->getContainer()->get('path.tmp') . '/cache/token.php';

$content  = "<?php exit; //\t" . $token;
file_put_contents($filename, $content);
echo $filename;
