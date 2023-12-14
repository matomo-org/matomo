<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Integration\Validators;

use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group AllowedEmailDomainTest
 * @group Plugins
 */
class AllowedEmailDomainTest extends IntegrationTestCase
{
    /**
     * @var AllowedEmailDomain
     */
    private $validator;

    /**
     * @var SystemSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2023-01-01 00:00:00');

        $this->settings = new SystemSettings();
        $this->validator = new AllowedEmailDomain($this->settings);
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();

        parent::tearDown();
    }

    /**
     * @dataProvider  getDataDomainFromEmail
     */
    public function test_getDomainFromEmail($expected, $email)
    {
        $this->assertSame($expected, $this->validator->getDomainFromEmail($email));
    }

    public function getDataDomainFromEmail()
    {
        return [
            ['matomo.org', 'foobar@matomo.org'],
            ['foo.matomo.co.uk', 'foobar@foo.matomo.co.uk'],
            ['mo.org', 'foobar@mato@mo.org'],
            ['mo.org', '   foobar @ mato  @  mo.org '],
            ['', 'foobar'],
            ['', ''],
            ['', null],
        ];
    }

    /**
     * @dataProvider  getDataDoesEmailEndWithAValidDomain
     */
    public function test_doesEmailEndWithAValidDomain($expected, $email, $domains)
    {
        $this->assertSame($expected, $this->validator->doesEmailEndWithAValidDomain($email, $domains));
    }

    public function getDataDoesEmailEndWithAValidDomain()
    {
        return [
            [false, 'foobar@matomo.org', []],
            [false, 'foobar@matomo.org', [false, null, '']],
            [true, 'foobar@matomo.org', ['matomo.org']],
            [false, 'foobar@matomo.fr', ['matomo.com', 'matomo.org', 'matomo.de']],
            [true, 'foobar@maToMo.dE', ['matomo.com', 'matomo.org', 'mAtOmo.De']],
            [true, 'foobar@maToMo.dE', ['example.org', 'mAtOmo.De']],
        ];
    }

    public function test_getEmailDomainsInUse_noUsersConfigured()
    {
        $this->assertSame([], $this->validator->getEmailDomainsInUse());
    }

    public function test_getEmailDomainsInUse_usersAddedAndInvited()
    {
        $userApi = API::getInstance();
        $userApi->addUser('foo1','foo' . time(), 'foobar@matomo.org');
        $userApi->addUser('foo2','foo' . time(), 'foobar2@matomo.org');
        $userApi->inviteUser('foo3', 'foobar@matomo.com', 1);
        $userApi->inviteUser('foo4', 'foobar3@matomo.org', 1);
        $userApi->inviteUser('foo5', 'foobar@example.com', 1);
        $userApi->addUser('foo6','foo' . time(), 'foobar2@example.org');

        $this->assertEquals([
            'matomo.org', 'matomo.com', 'example.com', 'example.org'
        ], $this->validator->getEmailDomainsInUse());
    }

    public function test_validate_noDomainsConfigured_meansAllDomainsAllowed()
    {
        $this->assertNull($this->validator->validate('foobar@matomo.org'));
        $this->assertNull($this->validator->validate('foobar@mAtomo.org'));
        $this->assertNull($this->validator->validate('foobar@eXaMPle.com'));
    }

    public function test_validate_emailsAllowed()
    {
        $this->settings->allowedEmailDomains->setValue(['MaToMo.Org', 'example.COM']);
        $this->assertNull($this->validator->validate('foobar@mAtomo.org'));
        $this->assertNull($this->validator->validate('foobar@eXaMPle.com'));
    }

    public function test_validate_noEmailsAllowed_DomainsAreConfigured()
    {
        Fixture::loadAllTranslations();
        $this->expectExceptionMessage('The email "foobar@matomo.com" cannot be used, as only emails with the domains "matomo.org, example.com" are allowed.');
        $this->settings->allowedEmailDomains->setValue(['matomo.org', 'example.com']);
        $this->validator->validate('foobar@matomo.com');
    }

}
