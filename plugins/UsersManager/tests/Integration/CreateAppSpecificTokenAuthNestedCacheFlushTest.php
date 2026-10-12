<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\API\Request as ApiRequest;
use Piwik\Cache;
use Piwik\DI;
use Piwik\Plugins\UsersManager\API as UsersAPI;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * createAppSpecificTokenAuth may only run as a top-level request. Whether it is nested must be
 * decided from the actual API call nesting, not from request-scoped cache state that another API
 * method may clear while the request is still running.
 *
 * @group UsersManager
 */
class CreateAppSpecificTokenAuthNestedCacheFlushTest extends IntegrationTestCase
{
    private const LOGIN = 'sometokenuser';

    /**
     * Generated per run so the test carries no fixed credential.
     *
     * @var string
     */
    private $password;

    /**
     * @var Model
     */
    private $model;

    /**
     * Whether the sibling sub-request clears all caches while it runs.
     *
     * @var bool
     */
    private $clearCachesInSiblingRequest = false;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess(true);

        // A valid Matomo password, generated so no fixed credential is stored in the test.
        $this->password = 'Aa1' . bin2hex(random_bytes(8));

        $this->model = new Model();
        UsersAPI::getInstance()->addUser(self::LOGIN, $this->password, self::LOGIN . '@matomo.org');

        // Act as anonymous within the sub-request so that nothing but the nesting check can refuse
        // the nested call.
        FakeAccess::clearAccess(false, [], [], 'anonymous', [1]);
    }

    public function tearDown(): void
    {
        ApiRequest::setIsRootRequestApiRequest(null);
        parent::tearDown();
    }

    public function testCreateAppSpecificTokenAuthIsRejectedAsAChildOfAnApiRequestWhenASiblingClearedAllCaches()
    {
        $this->clearCachesInSiblingRequest = true;

        $result = $this->createTokenAuthWithinBulkRequest();

        $this->assertNestedRequestWasRefused($result);
    }

    public function testCreateAppSpecificTokenAuthIsRejectedAsAChildOfAnApiRequestWithoutACacheClear()
    {
        $result = $this->createTokenAuthWithinBulkRequest();

        $this->assertNestedRequestWasRefused($result);
    }

    /**
     * @param mixed $result The decoded response of the nested sub-request.
     */
    private function assertNestedRequestWasRefused($result): void
    {
        // The strongest assertion first: whatever the response looks like, no token may exist.
        $this->assertEmpty(
            $this->model->getAllNonSystemTokensForLogin(self::LOGIN),
            'a nested request created an app-specific token'
        );

        $this->assertIsArray($result, 'the nested request was answered instead of being refused');
        $this->assertSame('error', $result['result'] ?? null);
        // Development mode appends a debugging hint to the message, so match on the key only.
        $this->assertStringContainsString(
            'UsersManager_ExceptionCreateTokenAuthWithinNestedRequest',
            (string) ($result['message'] ?? '')
        );
    }

    /**
     * Runs the API method as the second sub-request of a bulk request, ie as a nested API request.
     *
     * @return mixed The decoded response of that sub-request.
     */
    private function createTokenAuthWithinBulkRequest()
    {
        // Make the bulk request's children count as nested API requests.
        ApiRequest::setIsRootRequestApiRequest('API.getBulkRequest');

        $result = ApiRequest::processRequest('API.getBulkRequest', [
            'urls' => [
                // a sibling sub-request that may clear caches while it runs
                'method=API.getMatomoVersion',
                'method=UsersManager.createAppSpecificTokenAuth'
                    . '&userLogin=' . self::LOGIN
                    . '&passwordConfirmation=' . urlencode($this->password)
                    . '&description=test',
            ],
        ]);

        return $result[1] ?? null;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'observers.global' => DI::add([
                ['API.API.getMatomoVersion.end', DI::value(function () {
                    if (!$this->clearCachesInSiblingRequest) {
                        return;
                    }

                    // Some API methods clear caches while they run; model that here.
                    Cache::getTransientCache()->flushAll();
                })],
            ]),
        ];
    }
}
