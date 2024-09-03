<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Access;
use Piwik\Application\Environment;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Plugins\LanguagesManager\API as APILanguageManager;
use Piwik\Updater;
use Piwik\Plugins\CoreUpdater;

$subdir = str_replace(DIRECTORY_SEPARATOR, '', $argv[1]);
$dbConfig = json_decode($argv[2], $isAssoc = true);
$host = $argv[3];

define('PIWIK_DOCUMENT_ROOT', __DIR__ . '/../../' . $subdir);
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
define('PIWIK_TEST_MODE', 1); // for drop database

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

define('PIWIK_PRINT_ERROR_BACKTRACE', true);

if (!Common::isPhpCliMode()) {
    print "not available";
    exit;
}

function createFreshDatabase($config, $name) {
    Db::createDatabaseObject(array_merge($config, [
        'dbname' => null,
    ]));

    try {
        DbHelper::dropDatabase($name);
    } catch (\Exception $e) {
        print $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    }

    DbHelper::createDatabase($name);
    DbHelper::disconnectDatabase();

    print "created database $name...\n";
}

function updateDatabase() {
    Cache::deleteTrackerCache();
    Option::clearCache();

    $updater = new Updater();
    $componentsWithUpdateFile = $updater->getComponentUpdates();
    if (empty($componentsWithUpdateFile)) {
        return false;
    }

    $result = $updater->updateComponents($componentsWithUpdateFile);
    if (!empty($result['coreError'])
        || !empty($result['warnings'])
        || !empty($result['errors'])
    ) {
        throw new \Exception("Failed to update database (errors or warnings found): " . print_r($result, true));
    }

    return $result;
}

function createSuperUser() {
    $passwordHelper = new Password();

    $login    = 'superUserLogin';
    $password = $passwordHelper->hash(UsersManager::getPasswordHash('pas3!"§$%&/()=?\'ㄨ<|-_#*+~>word'));

    $model = new \Piwik\Plugins\UsersManager\Model();
    $user  = $model->getUser($login);

    if (empty($user)) {
        $model->addUser($login, $password, 'hello@example.org', Date::now()->getDatetime());
    } else {
        $model->updateUser($login, $password, 'hello@example.org');
    }

    $setSuperUser = empty($user) || !empty($user['superuser_access']);
    $model->setSuperUserAccess($login, $setSuperUser);

    return $model->getUser($login);
}

function createWebsite($dateTime)
{
    $siteName = 'Test Site Subdir';
    $idSite = SitesManagerAPI::getInstance()->addSite(
        $siteName,
        "http://piwik.net/",
        $ecommerce = 1,
        $siteSearch = null, $searchKeywordParameters = null, $searchCategoryParameters = null,
        $ips = null,
        $excludedQueryParameters = null,
        $timezone = null,
        $currency = null,
        $group = null,
        $startDate = null,
        $excludedUserAgents = null,
        $keepURLFragments = null,
        $type = null,
        $settings = null,
        $excludeUnknownUrls = 0
    );

    // Manually set the website creation date to a day earlier than the earliest day we record stats for
    Db::get()->update(Common::prefixTable("site"),
        array('ts_created' => Date::factory($dateTime)->subDay(1)->getDatetime()),
        "idsite = $idSite"
    );

    // Clear the memory Website cache
    Site::clearCache();
    Cache::deleteCacheWebsiteAttributes($idSite);
    Cache::updateGeneralCache();

    return $idSite;
}

$_SERVER['HTTP_HOST'] = $host;
$_SERVER['SERVER_NAME'] = $host;
$dbConfig['dbname'] = 'latest_stable';

file_put_contents(PIWIK_INCLUDE_PATH . "/config/config.ini.php", '');

@mkdir(PIWIK_INCLUDE_PATH . '/tmp');

$environment = new Environment($environment = null);
$environment->init();

// create database
createFreshDatabase($dbConfig, $dbConfig['dbname']);

// setup config
$config = Config::getInstance();
$config->database = $dbConfig;
$config->General['trusted_hosts'] = [
    $host,
    'localhost',
    '127.0.0.1',
];
$config->Cache['backend'] = 'file';
$config->forceSave();

print "setup config\n";

// setup db tables
Db::createDatabaseObject();
DbHelper::createTables();

print "setup tables\n";

// setup plugins
$pluginsManager = \Piwik\Plugin\Manager::getInstance();
$pluginsManager->loadActivatedPlugins();

$pluginsManager->installLoadedPlugins();
foreach($pluginsManager->getLoadedPlugins() as $plugin) {
    $name = $plugin->getPluginName();
    if (!$pluginsManager->isPluginActivated($name)) {
        $pluginsManager->activatePlugin($name);
    }
}

$pluginsManager->loadPluginTranslations();

print "setup plugins\n";

// update (required after loading plugins first time)
$updated = updateDatabase();
if (empty($updated)) {
    echo "did not update\n";
} else {
    echo "updated db\n";
}

// create root user
Access::getInstance()->setSuperUserAccess();
createSuperUser();
APILanguageManager::getInstance()->setLanguageForUser('superUserLogin', 'en');

print "created root user\n";

// create website
createWebsite('2017-01-01 00:00:00');

print "created website\n";

// copy custom release channel
copy(PIWIK_INCLUDE_PATH . '/../tests/PHPUnit/Fixtures/LatestStableInstall/GitCommitReleaseChannel.php',
    PIWIK_INCLUDE_PATH . '/plugins/CoreUpdater/ReleaseChannel/GitCommitReleaseChannel.php');

$settings = StaticContainer::get(CoreUpdater\SystemSettings::class);
$settings->releaseChannel->setValue('git_commit');
$settings->releaseChannel->save();

\Piwik\UpdateCheck::check(true); // ensure new version is detected correctly

print "set release channel\n";

// print token auth (on last line so it can be easily parsed)
print Piwik::requestTemporarySystemAuthToken('InstallerUITests', 24);