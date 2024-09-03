<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Fixtures\ThreeSitesWithSharedVisitors;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group AllWebsitesTest
 */
class AllWebsitesTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition
    protected static $userTokenAuth = '34bcc4d2c330259d6f37bc3d98d425f4';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::createUser();
    }

    private static function createUser()
    {
        if (!UsersManagerAPI::getInstance()->userExists('limitedUser')) {
            // create non super user
            UsersManagerAPI::getInstance()->addUser('limitedUser', 'smartypants', 'user@limited.com');
            UsersManagerAPI::getInstance()->setUserAccess('limitedUser', 'view', array(2, 3));
            $userModel = new UsersManagerModel();
            $userModel->addTokenAuth('limitedUser', self::$userTokenAuth, 'desc', '2020-01-02 03:04:05');
        }
    }

    public function getApiForTesting()
    {
        $dateTime = substr(self::$fixture->dateTime, 0, 10);

        return [
            // should return all websites as super user has access to all
            ['VisitsSummary.get', ['idSite' => 'all',
                                     'date' => $dateTime,
                                     'period' => 'day',
                                     'format' => 'csv'], [
                                     'testSuffix' => 'superuser']
            ],

            // should only return results for sites the user has access to (2,3)
            ['VisitsSummary.get', ['idSite' => 'all',
                                     'date' => $dateTime,
                                     'period' => 'day',
                                     'format' => 'csv',
                                     'token_auth' => self::$userTokenAuth], [
                                     'testSuffix' => 'user']
            ],
        ];
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params, $options)
    {
        $this->runAnyApiTest($api, '', $params, $options);
    }
}

AllWebsitesTest::$fixture = new ThreeSitesWithSharedVisitors();
