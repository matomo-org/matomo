<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Unit\Dimension;

use Piwik\Plugins\CustomDimensions\Dimension\Scope;

/**
 * @group CustomDimensions
 * @group ScopeTest
 * @group Scope
 * @group Plugins
 */
class ScopeTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckShouldFailWhenScopeIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid value '' for 'scope' specified. Available scopes are: visit, action, conversion");

        $this->buildScope('')->check();
    }

    public function testCheckShouldFailWhenScopeIsNotValid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid value 'anyScoPe' for 'scope' specified. Available scopes are: visit, action, conversion");

        $this->buildScope('anyScoPe')->check();
    }

    public function testCheckShouldNotFailWhenScopeIsValid()
    {
        self::expectNotToPerformAssertions();

        $this->buildScope('action')->check();
        $this->buildScope('visit')->check();
        $this->buildScope('conversion')->check();
    }

    private function buildScope($scope)
    {
        return new Scope($scope);
    }
}
