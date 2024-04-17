<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 */
class ResponseCodeTest extends SystemTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $model = new Model();
        $model->addUser(
            Fixture::VIEW_USER_LOGIN,
            Fixture::VIEW_USER_PASSWORD,
            'hello2@example.org',
            Date::now()->getDatetime()
        );
        $model->addUserAccess(Fixture::VIEW_USER_LOGIN, 'view', [1]);
        $model->addTokenAuth(
            Fixture::VIEW_USER_LOGIN,
            Fixture::VIEW_USER_TOKEN,
            'View user token',
            Date::now()->getDatetime()
        );
    }

    public function testApiCallWithoutPermissionShouldHaveCorrectHttpStatus()
    {
        [$response, $info] = $this->curl(
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=API&method=API.getPhpVersion',
            ['token_auth' => Fixture::VIEW_USER_TOKEN]
        );

        // The user doesn't have superuser access, so status code should be 401
        $this->assertEquals(401, $info['http_code']);
    }

    public function testApiShouldHaveCorrectHttpStatus()
    {
        [$response, $info] = $this->curl(
            Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=API&method=API.getPhpVersion',
            ['token_auth' => Fixture::ADMIN_USER_TOKEN]
        );

        // User has access so status code should be 200
        $this->assertEquals(200, $info['http_code']);
    }

    public function testProcessedApiCallWithExceptionShouldHaveCorrectHttpStatus()
    {
        [$response, $info] = $this->curl(
            Fixture::getRootUrl(
            ) . 'tests/PHPUnit/proxy/index.php?module=API&method=SitesManager.getSiteUrlsFromId&idSite=1&reThrow=1',
            ['token_auth' => Fixture::VIEW_USER_TOKEN]
        );

        // The message and status code from a nested API call should be used if they are not catched.
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<result>\n	<error message=\"You can't access this resource as it requires a 'superuser' access.\" />\n</result>", $response);
        $this->assertEquals(401, $info['http_code']);
    }

    public function testProcessedApiCallWithCaughtExceptionShouldHaveCorrectHttpStatus()
    {
        [$response, $info] = $this->curl(
            Fixture::getRootUrl(
            ) . 'tests/PHPUnit/proxy/index.php?module=API&method=SitesManager.getSiteUrlsFromId&idSite=1',
            ['token_auth' => Fixture::VIEW_USER_TOKEN]
        );

        // Even though the user doesn't have superuser access, the response code should be 200 as the exception in the nested API call is caught
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<result>error caught</result>", $response);
        $this->assertEquals(200, $info['http_code']);
    }

    public function testProcessedApiShouldHaveCorrectHttpStatus()
    {
        [$response, $info] = $this->curl(
            Fixture::getRootUrl(
            ) . 'tests/PHPUnit/proxy/index.php?module=API&method=SitesManager.getSiteUrlsFromId&idSite=1',
            ['token_auth' => Fixture::ADMIN_USER_TOKEN]
        );

        // The user has access to nested API method, so it's response should be returned
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<result>no error</result>", $response);
        $this->assertEquals(200, $info['http_code']);
    }

    private function curl($url, $postParams = [])
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('Curl is not installed');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response = substr($response, $headerSize);

        $responseInfo = curl_getinfo($ch);

        curl_close($ch);

        return [$response, $responseInfo];
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => \DI\add(
                [
                    [
                        'API.Request.intercept',
                        \DI\value(
                            function (&$returnedValue, $finalParameters, $pluginName, $methodName, $parametersRequest) {
                                if ($pluginName !== 'SitesManager' || $methodName !== 'getSiteUrlsFromId') {
                                    return;
                                }

                                try {
                                    // This call requires SuperUser access
                                    Request::processRequest('API.getPhpVersion', [], $parametersRequest);
                                    $returnedValue = 'no error';
                                } catch (\Exception $e) {
                                    $returnedValue = 'error caught';
                                    if (!empty($parametersRequest['reThrow'])) {
                                        throw $e;
                                    }
                                }
                            }
                        ),
                    ],
                ]
            ),
        ];
    }
}

ResponseCodeTest::$fixture = new Fixture();
