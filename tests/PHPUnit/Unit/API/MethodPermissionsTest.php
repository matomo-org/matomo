<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use PHPUnit\Framework\TestCase;
use Piwik\API\MethodPermissions;
use Piwik\Attributes\Permission;
use ReflectionMethod;

/**
 * @group Core
 */
class MethodPermissionsTest extends TestCase
{
    public function testParseFromAttributesParsesAttributeOnlyPermission()
    {
        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(MethodPermissionsTestFixture::class, 'attributeOnlyMethod');

        $parsed = $permissions->parseFromAttributes($method);

        $this->assertSame(['name' => 'write', 'parameter' => 'idSite'], $parsed);
    }

    public function testResolveMethodPermissionsUsesAttributeMetadataForMigratedMethod()
    {
        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(\Piwik\Plugins\API\API::class, 'getPiwikVersion');

        $resolved = $permissions->resolveMethodPermissions($method);

        $this->assertSame(['name' => 'someView', 'parameter' => null], $resolved['permission']);
    }

    public function testResolveMethodPermissionsThrowsWhenParameterlessDeclarationRequiresParameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Permission "admin" requires a parameter name.');

        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(MethodPermissionsTestFixture::class, 'parameterRequiringMethodWithoutParameter');

        $permissions->resolveMethodPermissions($method);
    }
}

class MethodPermissionsTestFixture
{
    #[Permission('write', '$idSite')]
    public function attributeOnlyMethod()
    {
    }

    #[Permission('admin')]
    public function parameterRequiringMethodWithoutParameter()
    {
    }
}
