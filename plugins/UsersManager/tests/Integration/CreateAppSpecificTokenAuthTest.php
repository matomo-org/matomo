<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Access;
use Piwik\API\Request as ApiRequest;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\UsersManager\API as UsersAPI;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group UsersManager
 */
class CreateAppSpecificTokenAuthTest extends IntegrationTestCase
{
    private const LOGIN = 'sometokenuser';
    private const PASSWORD = '123abcDk3_l3';

    /**
     * @var Model
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Model();
        UsersAPI::getInstance()->addUser(self::LOGIN, self::PASSWORD, self::LOGIN . '@matomo.org');
    }

    public function tearDown(): void
    {
        ApiRequest::setIsRootRequestApiRequest(null);
        parent::tearDown();
    }

    public function testCreateAppSpecificTokenAuthIsRejectedAsAChildOfAnApiRequest()
    {
        // Make the bulk request's children count as nested API requests.
        ApiRequest::setIsRootRequestApiRequest('API.getBulkRequest');

        $result = ApiRequest::processRequest('API.getBulkRequest', [
            'urls' => [
                'method=UsersManager.createAppSpecificTokenAuth'
                    . '&userLogin=' . self::LOGIN
                    . '&passwordConfirmation=' . urlencode(self::PASSWORD)
                    . '&description=test',
            ],
        ]);

        $this->assertSame('error', $result[0]['result'] ?? null);
        // No token may have been created for the user.
        $this->assertEmpty($this->model->getAllNonSystemTokensForLogin(self::LOGIN));
    }

    public function testCreateAppSpecificTokenAuthWorksAsATopLevelRequest()
    {
        $this->setAnonymousUser();
        $token = UsersAPI::getInstance()->createAppSpecificTokenAuth(self::LOGIN, self::PASSWORD, 'test');

        $this->assertNotEmpty($token);
        $this->assertNotEmpty($this->model->getAllNonSystemTokensForLogin(self::LOGIN));
    }

    private function setAnonymousUser(): void
    {
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $auth->setPasswordHash(null);

        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess($auth);
    }
}
