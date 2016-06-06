<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . "/../.."));
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);

require_once PIWIK_INCLUDE_PATH . "/index.php";

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
        $pattern = '--piwik-domain=';
        if(false !== strpos($param, $pattern)) {
            return substr($param, strlen($pattern));
        }
    }
    return null;
}

$environment = new Environment('cli');
$environment->init();

$piwikDomain = getPiwikDomain();
if($piwikDomain) {
    Url::setHost($piwikDomain);
}

$token = Db::get()->fetchOne("SELECT token_auth
                              FROM " . Common::prefixTable("user") . "
                              WHERE superuser_access = 1
                              ORDER BY date_registered ASC");

$filename = $environment->getContainer()->get('path.tmp') . '/cache/token.php';

$content  = "<?php exit; //\t" . $token;
file_put_contents($filename, $content);
echo $filename;