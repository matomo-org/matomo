<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\View\SecurityPolicy;

/**
 * @group Core
 * @group SettingsServer
 */
class SecurityPolicyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestSecurityPolicy
     */
    private $securityPolicy;

    public function setUp(): void
    {
        parent::setUp();
        $this->securityPolicy = new SecurityPolicy();
    }

    public function testDefaultSecurityPolicy() {
        $expectedDefault = "Content-Security-Policy-Report-Only: default-src 'self' 'unsafe-inline' 'unsafe-eval'; ";
        $this->assertEquals($expectedDefault, $this->securityPolicy->createHeaderString());
    }

    public function testCanAddNewDirectivePolicy() {
        $this->securityPolicy->addPolicy('script-src', "'self'");

        $this->assertStringContainsString("script-src 'self'", $this->securityPolicy->createHeaderString());
    }

    public function testCanAppendPolicy() {
        $this->securityPolicy->addPolicy('default-src', "'new-policy'");

        $expected = "Content-Security-Policy-Report-Only: default-src 'self' 'unsafe-inline' 'unsafe-eval' 'new-policy'; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }

    public function testCanOverridePolicy() {
        $this->securityPolicy->overridePolicy('default-src', "'self'");

        $expected = "Content-Security-Policy-Report-Only: default-src 'self'; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }

    public function testCanRemoveDirective() {
        $this->securityPolicy->removeDirective('default-src');
        $this->securityPolicy->addPolicy('script-src', "'self'");

        $expected = "Content-Security-Policy-Report-Only: script-src 'self'; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }
}
