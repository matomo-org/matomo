<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\DbHelper;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\AssetManager;
use Piwik\Date;

/**
 * TODO
 */
class UITestFixture extends OmniFixture
{
    private static $recursiveProxyLinkNames = array('libs', 'plugins', 'tests');

    public function setUp()
    {
        parent::setUp();

        DbHelper::createAnonymousUser();
        UsersManagerAPI::getInstance()->setSuperUserAccess('superUserLogin', true);

        AssetManager::getInstance()->removeMergedAssets();
        
        // launch archiving so tests don't run out of time
        $date = Date::factory($this->dateTime)->toString();
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date);
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date, urlencode($this->segment));

        // make sure symbolic links exist (phantomjs doesn't support symlink-ing yet)
        foreach (self::$recursiveProxyLinkNames as $linkName) {
            $linkPath = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/' . $linkName;
            if (!file_exists($linkPath)) {
                symlink(PIWIK_INCLUDE_PATH . '/' . $linkName, $linkPath);
            }
        }
    }
}