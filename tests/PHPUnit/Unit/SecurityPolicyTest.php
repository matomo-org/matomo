<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\View\SecurityPolicy;
use Piwik\Config;

/**
 * @group Core
 * @group SettingsServer
 */
class SecurityPolicyTest extends \PHPUnit\Framework\TestCase
{
    private $securityPolicy;
    private $defaultPolicy = "default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' 'unsafe-inline' 'unsafe-eval' data:; ";
    private $generalConfig;


    public function setUp(): void
    {
        parent::setUp();

        // set the default config explicitly so tests can change them without needing to reset them
        $this->generalConfig =& Config::getInstance()->General;
        $this->generalConfig['csp_enabled'] = 1;
        $this->generalConfig['csp_report_only'] = 1;

        $this->securityPolicy = new SecurityPolicy(Config::getInstance());
    }

    public function testDefaultSecurityPolicy()
    {
        $expectedDefault = "Content-Security-Policy-Report-Only: " . $this->defaultPolicy;
        $this->assertEquals($expectedDefault, $this->securityPolicy->createHeaderString());
    }

    public function testDefaultEnabledSecurityPolicy()
    {
        $this->generalConfig['csp_report_only'] = 0;
        $this->securityPolicy = new SecurityPolicy(Config::getInstance());

        $expectedDefaultEnabled = "Content-Security-Policy: " . $this->defaultPolicy;
        $this->assertEquals($expectedDefaultEnabled, $this->securityPolicy->createHeaderString());
    }

    public function testDisabledSecurityPolicy()
    {
        $this->generalConfig['csp_enabled'] = 0;
        $this->securityPolicy = new SecurityPolicy(Config::getInstance());

        $this->assertSame('', $this->securityPolicy->createHeaderString());
    }

    public function testCanAddNewDirectivePolicy()
    {
        $this->securityPolicy->addPolicy('script-src', "'self'");

        $this->assertStringContainsString("script-src 'self'", $this->securityPolicy->createHeaderString());
    }

    public function testCanAppendPolicy()
    {
        $this->securityPolicy->addPolicy('default-src', "'new-policy'");

        $expected = "Content-Security-Policy-Report-Only: default-src 'self' 'unsafe-inline' 'unsafe-eval' 'new-policy'; img-src 'self' 'unsafe-inline' 'unsafe-eval' data:; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }

    public function testCanOverridePolicy()
    {
        $this->securityPolicy->overridePolicy('default-src', "'self'");

        $expected = "Content-Security-Policy-Report-Only: default-src 'self'; img-src 'self' 'unsafe-inline' 'unsafe-eval' data:; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }

    public function testCanRemoveDirective()
    {
        $this->securityPolicy->removeDirective('default-src');
        $this->securityPolicy->addPolicy('script-src', "'self'");

        $expected = "Content-Security-Policy-Report-Only: img-src 'self' 'unsafe-inline' 'unsafe-eval' data:; script-src 'self'; ";
        $this->assertEquals($expected, $this->securityPolicy->createHeaderString());
    }
}
