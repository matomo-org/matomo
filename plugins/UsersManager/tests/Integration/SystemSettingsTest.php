<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration;

use Piwik\Db\Schema\Mysql;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group BruteForceDetection
 */
class SystemSettingsTest extends IntegrationTestCase
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function test_allowedEmailDomain_getValue_whenNothingSet()
    {
        $this->assertSame([], $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_setAndGet()
    {
        $domains = ['matomo.org', 'example.com'];
        $this->settings->allowedEmailDomains->setValue($domains);
        $this->assertSame($domains, $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_willStoreLowerCase()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'examPle.CoM']);
        $this->assertSame(['matomo.org', 'example.com'], $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_removesDuplicates()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
        $this->assertSame(['matomo.org', 'example.com'], $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_unsetAfterSetting()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
        $this->assertNotEmpty($this->settings->allowedEmailDomains->getValue());

        $this->settings->allowedEmailDomains->setValue(['', '', '', false]);
        $this->assertSame([], $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_HandleIncorrectUserInput()
    {
        $this->settings->allowedEmailDomains->setValue(['@maToMo.org', 'foo@matomo.com', ' foo @ example.com ', ' @example.org']);
        $this->assertSame(['matomo.org', 'matomo.com', 'example.com', 'example.org'], $this->settings->allowedEmailDomains->getValue());
    }

    public function test_allowedEmailDomain_wontAllowSavingDomainsIfOtherDomainsExist()
    {
        Fixture::loadAllTranslations();
        $this->expectExceptionMessage('Setting the domains is not possible as other domains (limited.com) are already in use by other users. To change this setting, you either need to delete users with other domains or you need to allow these domains as well.');
        $schema = new Mysql();
        $schema->createAnonymousUser(); // anonymous user should be ignore in checks
        UsersManagerAPI::getInstance()->addUser('randomUser', 'smartypants', 'user@limited.com');
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
    }
}
