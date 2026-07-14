<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Http\BadRequestException;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\API\API;
use Piwik\Plugins\API\BulkRequestLimit;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Request\AuthenticationToken;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group APITest
 * @group APITestBulkRequest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    private $hasSuperUserAccess = false;

    private $bulkRequestLimitBackup;

    private $anonymousAccessBackup;

    private $createdAnonymousUser = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        Fixture::createSuperUser(true);

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $this->bulkRequestLimitBackup = Config::getInstance()->General['API_bulk_request_limit'] ?? null;

        $this->makeSureTestRunsInContextOfAnonymousUser();
    }

    public function tearDown(): void
    {
        if ($this->anonymousAccessBackup !== null) {
            $model = new UsersManagerModel();
            $model->deleteUserAccess('anonymous', [1]);
            if ($this->anonymousAccessBackup !== 'noaccess') {
                $model->addUserAccess('anonymous', $this->anonymousAccessBackup, [1]);
            }
            $this->setAnonymousContext();
            $this->anonymousAccessBackup = null;
        }

        if ($this->createdAnonymousUser) {
            $model = new UsersManagerModel();
            $model->deleteUser('anonymous');
            $this->createdAnonymousUser = false;
        }

        if ($this->bulkRequestLimitBackup !== null) {
            Config::getInstance()->General['API_bulk_request_limit'] = $this->bulkRequestLimitBackup;
        }

        Access::getInstance()->hasSuperUserAccess($this->hasSuperUserAccess);
        parent::tearDown();
    }

    public function testGetBulkRequestIsAbleToHandleManyDifferentRequests()
    {
        $token = Fixture::getTokenAuth();
        $urls = array(
            "method%3dVisitsSummary.get%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26token_auth%3d$token%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26idSite%3d1%26token_auth%3danonymous%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26token_auth%3d$token%26idSite%3d1%26date%3d2015-01-26%26period%3dday%26segment%3dvisitDuration%3d%3d30%3bactions%3e2",
        );
        $response = $this->api->getBulkRequest($urls);

        $this->assertResponseIsPermissionError($response[0]);
        $this->assertResponseIsSuccess($response[1]);
        $this->assertSame(0, $response[1]['nb_visits']);
        $this->assertResponseIsPermissionError($response[2]);
        $this->assertResponseIsPermissionError($response[3]);
        $this->assertResponseIsSuccess($response[4]);
    }

    public function testGetBulkRequestDeniesArchiveReportsForViewOnlyUser()
    {
        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $response = $this->getBulkRequestAsRootApiRequest([$this->makeArchiveReportsBulkUrl(1)]);
            $this->assertCount(1, $response);
            $this->assertResponseIsSuperUserPermissionError($response[0]);
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testArchiveReportsRequestWithWhitespacePaddedMethodDeniesViewOnlyUser()
    {
        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $response = $this->processRootApiRequest([
                'module' => 'API',
                'method' => ' CoreAdminHome.archiveReports ',
                'idSite' => '1',
                'period' => 'day',
                'date' => '2012-01-01',
                'format' => 'json',
            ]);

            $this->assertResponseIsSuperUserPermissionError($response);
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestWithWhitespacePaddedMethodsDeniesArchiveReportsForViewOnlyUser()
    {
        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $response = $this->processRootApiRequest([
                'module' => 'API',
                'method' => ' API.getBulkRequest ',
                'format' => 'json',
                'urls' => [$this->makeArchiveReportsBulkUrl(1, null, ' CoreAdminHome.archiveReports ')],
            ]);

            $this->assertCount(1, $response);
            $this->assertResponseIsSuperUserPermissionError($response[0]);
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestWithHtmlEntityMethodDeniesArchiveReportsForViewOnlyUser()
    {
        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $response = $this->processRootApiRequest([
                'module' => 'API',
                'method' => 'API.getBulkRequest',
                'format' => 'json',
                'urls' => [$this->makeArchiveReportsBulkUrl(1, null, 'CoreAdminHome.&#97;rchiveReports', 'CoreHome')],
            ]);

            $this->assertCount(1, $response);
            $this->assertResponseIsSuperUserPermissionError($response[0]);
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestAllowsArchiveReportsForSuperUser()
    {
        $this->setSuperUserContext();

        try {
            $response = $this->getBulkRequestAsRootApiRequest([$this->makeArchiveReportsBulkUrl(1)]);
            $this->assertCount(1, $response);
            $this->assertResponseIsSuccess($response[0]);
        } finally {
            $this->setAnonymousContext();
        }
    }

    public function testGetBulkRequestAllowsArchiveReportsForSuperUserTokenInBulkUrl()
    {
        $response = $this->getBulkRequestAsRootApiRequest([$this->makeArchiveReportsBulkUrl(1, Fixture::getTokenAuth())]);
        $this->assertCount(1, $response);
        $this->assertResponseIsSuccess($response[0]);
    }

    /**
     * In a session, a nested force_api_session differing from the root aborts the whole bulk request.
     */
    public function testGetBulkRequestSessionRootRejectsNestedForceApiSessionDowngrade()
    {
        $this->enterSessionRootScope();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));

        try {
            $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'force_api_session' => 0]),
            ]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * In a session, a nested token_auth differing from the root aborts the request, even when it
     * also downgrades force_api_session.
     */
    public function testGetBulkRequestSessionRootRejectsNestedDifferentTokenAuthWithDowngrade()
    {
        $this->enterSessionRootScope();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));

        try {
            $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'token_auth' => 'aDifferentToken', 'force_api_session' => 0]),
            ]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * In a session, a nested token_auth differing from the root aborts the request on its own.
     */
    public function testGetBulkRequestSessionRootRejectsNestedDifferentTokenAuthWithoutDowngrade()
    {
        $this->enterSessionRootScope();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));

        try {
            $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'token_auth' => 'aDifferentToken']),
            ]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * With a real-token (non-session) root, a nested force_api_session aborts the whole bulk request.
     */
    public function testGetBulkRequestRealTokenRootRejectsNestedForceApiSessionUpgrade()
    {
        $this->enterRealTokenRootScope();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));

        try {
            $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'force_api_session' => 1]),
            ]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * With an anonymous (non-session) root, a nested force_api_session aborts the whole bulk request.
     */
    public function testGetBulkRequestAnonymousRootRejectsNestedForceApiSessionUpgrade()
    {
        $this->enterAnonymousRootScope();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Piwik::translate('General_ConflictingAuthenticationParametersProvided'));

        try {
            $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'token_auth' => 'aDifferentToken', 'force_api_session' => 1]),
            ]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * A session root allows a nested sub-request that carries no auth params (inherits the root).
     */
    public function testGetBulkRequestSessionRootAllowsInheritedNestedAuth()
    {
        $this->enterSessionRootScope();

        try {
            $response = $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01']),
            ]);
            $this->assertCount(1, $response);
            $this->assertResponseIsSuccess($response[0]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * A session root allows a nested sub-request that repeats the root's own token_auth and
     * force_api_session.
     */
    public function testGetBulkRequestSessionRootAllowsMatchingNestedAuth()
    {
        $this->enterSessionRootScope();

        try {
            $response = $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'VisitsSummary.get', 'idSite' => 1, 'period' => 'day', 'date' => '2012-01-01', 'token_auth' => Fixture::getTokenAuth(), 'force_api_session' => 1]),
            ]);
            $this->assertCount(1, $response);
            $this->assertResponseIsSuccess($response[0]);
        } finally {
            $this->leaveRootScope();
        }
    }

    /**
     * Outside a session, a nested sub-request may authenticate with its own token_auth and then runs
     * under that identity (here a view-only user, so only site 1), not the root's.
     */
    public function testGetBulkRequestRealTokenRootAllowsNestedDifferentTokenAuth()
    {
        $secondToken = $this->createSecondUserTokenWithViewAccessToSite1();
        $this->enterRealTokenRootScope();

        try {
            $response = $this->getBulkRequestAsRootApiRequest([
                $this->makeBulkUrl(['method' => 'SitesManager.getSitesIdWithAtLeastViewAccess', 'token_auth' => $secondToken]),
            ]);
            $this->assertCount(1, $response);
            // the sub-request ran as the view-only user (site 1 only), not the super user root
            $this->assertEquals([1], $response[0]);
        } finally {
            $this->leaveRootScope();
        }
    }

    private function makeBulkUrl(array $params): string
    {
        return rawurlencode(http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    private function enterSessionRootScope(): void
    {
        $_POST['token_auth'] = Fixture::getTokenAuth();
        $_POST['force_api_session'] = 1;
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
        $this->setSuperUserContext();
    }

    private function enterRealTokenRootScope(): void
    {
        $_POST['token_auth'] = Fixture::getTokenAuth();
        unset($_POST['force_api_session']);
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
        $this->setSuperUserContext();
    }

    private function enterAnonymousRootScope(): void
    {
        unset($_POST['token_auth'], $_POST['force_api_session']);
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
        $this->setAnonymousContext();
    }

    private function leaveRootScope(): void
    {
        unset($_POST['token_auth'], $_POST['force_api_session']);
        StaticContainer::getContainer()->set(AuthenticationToken::class, new AuthenticationToken());
        $this->setAnonymousContext();
    }

    private function createSecondUserTokenWithViewAccessToSite1(): string
    {
        Access::doAsSuperUser(function () {
            $api = UsersManagerAPI::getInstance();
            if (!$api->userExists('bulk_viewer')) {
                $api->addUser('bulk_viewer', 'BulkViewer!2026abc', 'bulk_viewer@example.test');
            }
            $api->setUserAccess('bulk_viewer', 'view', [1]);
        });

        // the token is created from an unauthenticated request using the user's credentials
        $this->setAnonymousContext();

        return UsersManagerAPI::getInstance()->createAppSpecificTokenAuth('bulk_viewer', 'BulkViewer!2026abc', 'bulk viewer token');
    }

    public function testArchiveReportsAllowsViewAccessForNonBulkRootApiMethod()
    {
        $this->setAnonymousAccessForSite(1, 'view');
        $rootApiMethod = Request::getRootApiRequestMethod();

        try {
            Request::setIsRootRequestApiRequest('VisitsSummary.get');
            $response = CoreAdminHomeAPI::getInstance()->archiveReports(1, 'day', '2012-01-01');
            $this->assertIsArray($response);
        } finally {
            Request::setIsRootRequestApiRequest($rootApiMethod ?: '');
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestAllowsViewOnlyUserWhenReportFetchTriggersBrowserArchiving()
    {
        $this->setAnonymousAccessForSite(1, 'view');
        $previousBrowserArchivingSetting = Option::get(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);

        try {
            Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 1);

            $this->setSuperUserContext();
            CoreAdminHomeAPI::getInstance()->invalidateArchivedReports('1', '2012-01-01', 'day');

            $this->setAnonymousContext();
            $response = $this->getBulkRequestAsRootApiRequest([$this->makeVisitsSummaryBulkUrl(1, '2012-01-01')]);
            $this->assertCount(1, $response);
            $this->assertResponseIsSuccess($response[0]);
        } finally {
            if ($previousBrowserArchivingSetting === false) {
                Option::delete(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);
            } else {
                Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, $previousBrowserArchivingSetting);
            }

            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestLimitForAnonymousWithoutViewAccess()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = -1;

        $response = $this->api->getBulkRequest($this->makeBulkUrls(10));
        $this->assertCount(10, $response);

        $this->expectException(BadRequestException::class);
        $this->api->getBulkRequest($this->makeBulkUrls(11));
    }

    public function testGetBulkRequestLimitForAnonymousWithViewAccess()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = -1;

        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $response = $this->api->getBulkRequest($this->makeBulkUrls(50));
            $this->assertCount(50, $response);

            $this->expectException(BadRequestException::class);
            $this->api->getBulkRequest($this->makeBulkUrls(51));
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestLimitForAuthenticatedUsers()
    {
        $this->setSuperUserContext();
        Config::getInstance()->General['API_bulk_request_limit'] = 3;

        try {
            $response = $this->api->getBulkRequest($this->makeBulkUrls(3));
            $this->assertCount(3, $response);

            $this->expectException(BadRequestException::class);
            $this->api->getBulkRequest($this->makeBulkUrls(4));
        } finally {
            $this->setAnonymousContext();
        }
    }

    public function testGetBulkRequestLimitForAnonymousUsesLowerConfiguredLimit()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = 2;

        $response = $this->api->getBulkRequest($this->makeBulkUrls(2));
        $this->assertCount(2, $response);

        $this->expectException(BadRequestException::class);
        $this->api->getBulkRequest($this->makeBulkUrls(3));
    }

    public function testGetBulkRequestLimitReturnsDefaultForAnonymousWithoutViewAccess()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = -1;

        $this->assertSame(10, BulkRequestLimit::getCurrentLimit());
    }

    public function testGetBulkRequestLimitReturnsDefaultForAnonymousWithViewAccess()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = -1;
        $this->setAnonymousAccessForSite(1, 'view');

        try {
            $this->assertSame(50, BulkRequestLimit::getCurrentLimit());
        } finally {
            $this->restoreAnonymousAccessForSite(1);
        }
    }

    public function testGetBulkRequestLimitReturnsConfiguredLimitForAuthenticatedUsers()
    {
        $this->setSuperUserContext();
        Config::getInstance()->General['API_bulk_request_limit'] = 3;

        try {
            $this->assertSame(3, BulkRequestLimit::getCurrentLimit());
        } finally {
            $this->setAnonymousContext();
        }
    }

    public function testGetJsGlobalVariablesIncludesBulkRequestLimit()
    {
        Config::getInstance()->General['API_bulk_request_limit'] = 2;

        $out = '';
        $plugin = new \Piwik\Plugins\API\Plugin();
        $plugin->getJsGlobalVariables($out);

        $this->assertStringContainsString('piwik.apiBulkRequestLimit = 2;', $out);
    }

    public function testGetBulkRequestLimitIsDisabledForAuthenticatedUsersWhenConfigIsMinusOne()
    {
        $this->setSuperUserContext();
        Config::getInstance()->General['API_bulk_request_limit'] = -1;

        try {
            $response = $this->api->getBulkRequest($this->makeBulkUrls(12));
            $this->assertCount(12, $response);
        } finally {
            $this->setAnonymousContext();
        }
    }

    public function testGetSuggestedValuesForSegmentSupportsAllIdSiteWhenBrowserArchivingDisabled()
    {
        $this->setSuperUserContext();
        $previousBrowserArchivingSetting = Option::get(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);

        try {
            Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 0);

            try {
                $result = $this->api->getSuggestedValuesForSegment('pageTitle', 'all');
                $this->assertIsArray($result);
            } catch (\Throwable $e) {
                $this->assertNotInstanceOf(\TypeError::class, $e);
                $this->assertStringNotContainsString('SitesManager::getSiteFromId', $e->getMessage());
                $this->assertStringNotContainsString('must be of the type int', $e->getMessage());
            }
        } finally {
            if ($previousBrowserArchivingSetting === false) {
                Option::delete(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);
            } else {
                Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, $previousBrowserArchivingSetting);
            }

            $this->setAnonymousContext();
        }
    }

    private function assertResponseIsPermissionError($response)
    {
        $this->assertSame('error', $response['result']);
        $this->assertStringStartsWith('General_YouMustBeLoggedIn', $response['message']);
    }

    private function assertResponseIsSuccess($response)
    {
        $this->assertArrayNotHasKey('result', $response);
    }

    private function assertResponseIsSuperUserPermissionError($response)
    {
        $this->assertSame('error', $response['result']);

        $message = strtolower((string) ($response['message'] ?? ''));
        $this->assertStringContainsString('general_exceptionprivilege', $message);
    }

    private function makeBulkUrls(int $count): array
    {
        return array_fill(0, $count, 'method%3dAPI.getMatomoVersion');
    }

    private function getBulkRequestAsRootApiRequest(array $urls): array
    {
        $rootApiMethod = Request::getRootApiRequestMethod();

        try {
            Request::setIsRootRequestApiRequest('API.getBulkRequest');
            return $this->api->getBulkRequest($urls);
        } finally {
            Request::setIsRootRequestApiRequest($rootApiMethod ?: '');
        }
    }

    private function processRootApiRequest(array $params)
    {
        $rootApiMethod = Request::getRootApiRequestMethod();

        try {
            Request::setIsRootRequestApiRequest((string) ($params['method'] ?? ''));
            return json_decode((string) (new Request($params))->process(), true);
        } finally {
            Request::setIsRootRequestApiRequest($rootApiMethod ?: '');
        }
    }

    private function makeArchiveReportsBulkUrl(
        int $idSite,
        ?string $tokenAuth = null,
        string $method = 'CoreAdminHome.archiveReports',
        string $module = 'API'
    ): string {
        $params = [
            'module' => $module,
            'method' => $method,
            'idSite' => $idSite,
            'period' => 'day',
            'date' => '2012-01-01',
        ];

        if (!empty($tokenAuth)) {
            $params['token_auth'] = $tokenAuth;
        }

        return rawurlencode(http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    private function makeVisitsSummaryBulkUrl(int $idSite, string $date): string
    {
        return sprintf(
            'method%%3dVisitsSummary.get%%26idSite%%3d%d%%26period%%3dday%%26date%%3d%s',
            $idSite,
            $date
        );
    }

    private function makeSureTestRunsInContextOfAnonymousUser()
    {
        Piwik::postEvent('Request.initAuthenticationObject');

        $access = Access::getInstance();
        $this->hasSuperUserAccess = $access->hasSuperUserAccess();
        $access->setSuperUserAccess(false);
        $access->reloadAccess(StaticContainer::get('Piwik\Auth'));
        Request::reloadAuthUsingTokenAuth(array('token_auth' => 'anonymous'));
    }

    private function setAnonymousContext(): void
    {
        Piwik::postEvent('Request.initAuthenticationObject');

        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        $access->reloadAccess(StaticContainer::get('Piwik\Auth'));
        Request::reloadAuthUsingTokenAuth(['token_auth' => 'anonymous']);
    }

    private function setSuperUserContext(): void
    {
        Piwik::postEvent('Request.initAuthenticationObject');

        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->reloadAccess(StaticContainer::get('Piwik\Auth'));
        Request::reloadAuthUsingTokenAuth(['token_auth' => Fixture::getTokenAuth()]);
    }

    private function setAnonymousAccessForSite(int $idSite, string $access): void
    {
        $model = new UsersManagerModel();
        if (!$model->userExists('anonymous')) {
            $model->addUser('anonymous', 'not_a_hash', 'anonymous@example.com', Date::now()->getDatetime());
            $this->createdAnonymousUser = true;
        }

        if ($this->anonymousAccessBackup === null) {
            $usersAccess                 = $model->getUsersAccessFromSite($idSite);
            $this->anonymousAccessBackup = $usersAccess['anonymous'] ?? 'noaccess';
        }

        $model->deleteUserAccess('anonymous', [$idSite]);
        if ($access !== 'noaccess') {
            $model->addUserAccess('anonymous', $access, [$idSite]);
        }
        $this->setAnonymousContext();
    }

    private function restoreAnonymousAccessForSite(int $idSite): void
    {
        if ($this->anonymousAccessBackup === null) {
            return;
        }

        $model = new UsersManagerModel();
        $model->deleteUserAccess('anonymous', [$idSite]);
        if ($this->anonymousAccessBackup !== 'noaccess') {
            $model->addUserAccess('anonymous', $this->anonymousAccessBackup, [$idSite]);
        }
        $this->setAnonymousContext();
        $this->anonymousAccessBackup = null;
    }
}
