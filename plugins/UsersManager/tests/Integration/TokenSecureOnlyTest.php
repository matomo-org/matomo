<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 * @group TokenSecureOnlyTest
 */
class TokenSecureOnlyTest extends IntegrationTestCase
{
    protected static $tokenSecureOnly = 'f3fa8c38fd277a9af0fab7e35f9736fe';

    public static function beforeTableDataCached()
    {
        self::createUserAndTokens();
    }

    private static function createUserAndTokens()
    {
        if (!Fixture::siteCreated($idSite = 1)) {
            Fixture::createWebsite('2021-01-01');
        }

        if (!UsersManagerAPI::getInstance()->userExists('user1')) {
            UsersManagerAPI::getInstance()->addUser('user1', 'user1password', 'user@limited.com');
            UsersManagerAPI::getInstance()->setUserAccess('user1', 'view', [1]);

            $userModel = new UsersManagerModel();
            $userModel->addTokenAuth('user1', self::$tokenSecureOnly, 'Secure Only', '2020-01-02 03:04:05',
                null, false, true);
        }
    }

    /**
     * Secure only tokens should return a 401 code if used in a GET request
     */
    public function test_secureOnlyToken_accessDeniedIfGet()
    {
        $url = Fixture::getTestRootUrl().'?'.http_build_query([
                'module' => 'API',
                'method' => 'API.getMatomoVersion',
                'token_auth' => self::$tokenSecureOnly,
            ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        $this->assertEquals(401, $responseInfo["http_code"]);
        $this->assertStringContainsString("or is required to be sent as a POST parameter", $out);
    }

    /**
     * Secure only tokens should return a 200 code if used in a POST request
     */
    public function test_secureOnlyToken_accessGrantedIfPost()
    {
        $url = Fixture::getTestRootUrl().'?'.http_build_query([
                'module' => 'API',
                'method' => 'API.getMatomoVersion'
            ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['token_auth' => self::$tokenSecureOnly]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        $this->assertEquals(200, $responseInfo["http_code"]);
    }

}
