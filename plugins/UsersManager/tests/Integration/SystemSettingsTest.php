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

    public function testAllowedEmailDomainGetValueWhenNothingSet()
    {
        $this->assertSame([], $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainSetAndGet()
    {
        $domains = ['matomo.org', 'example.com'];
        $this->settings->allowedEmailDomains->setValue($domains);
        $this->assertSame($domains, $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainWillStoreLowerCase()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'examPle.CoM']);
        $this->assertSame(['matomo.org', 'example.com'], $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainRemovesDuplicates()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
        $this->assertSame(['matomo.org', 'example.com'], $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainUnsetAfterSetting()
    {
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
        $this->assertNotEmpty($this->settings->allowedEmailDomains->getValue());

        $this->settings->allowedEmailDomains->setValue(['', '', '', false]);
        $this->assertSame([], $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainHandleIncorrectUserInput()
    {
        $this->settings->allowedEmailDomains->setValue(['@maToMo.org', 'foo@matomo.com', ' foo @ example.com ', ' @example.org']);
        $this->assertSame(['matomo.org', 'matomo.com', 'example.com', 'example.org'], $this->settings->allowedEmailDomains->getValue());
    }

    public function testAllowedEmailDomainWontAllowSavingDomainsIfOtherDomainsExist()
    {
        Fixture::loadAllTranslations();
        $this->expectExceptionMessage('Setting the domains is not possible as other domains (limited.com) are already in use by other users. To change this setting, you either need to delete users with other domains or you need to allow these domains as well.');
        $schema = new Mysql();
        $schema->createAnonymousUser(); // anonymous user should be ignore in checks
        UsersManagerAPI::getInstance()->addUser('randomUser', 'smartypants', 'user@limited.com');
        $this->settings->allowedEmailDomains->setValue(['maToMo.org', 'matomo.org', '', 'examPle.CoM']);
    }
}
