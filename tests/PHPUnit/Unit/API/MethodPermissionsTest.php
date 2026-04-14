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
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use ReflectionMethod;

/**
 * @group Core
 */
class MethodPermissionsTest extends TestCase
{
    public function testParseFromDocCommentParsesSingleTag()
    {
        $permissions = new MethodPermissions();
        $parsed = $permissions->parseFromDocComment(<<<'DOC'
/**
 * @matomo-permission view(idSite)
 */
DOC
        );

        $this->assertSame(['name' => 'view', 'parameter' => 'idSite'], $parsed);
    }

    public function testParseFromDocCommentNormalizesPrefixedParameterName()
    {
        $permissions = new MethodPermissions();
        $parsed = $permissions->parseFromDocComment(<<<'DOC'
/**
 * @matomo-permission admin($idSite)
 */
DOC
        );

        $this->assertSame(['name' => 'admin', 'parameter' => 'idSite'], $parsed);
    }

    public function testParseFromDocCommentThrowsWhenMultipleTagsArePresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only one @matomo-permission tag is supported per method.');

        $permissions = new MethodPermissions();
        $permissions->parseFromDocComment(<<<'DOC'
/**
 * @matomo-permission notAnonymous
 * @matomo-permission someView
 */
DOC
        );
    }

    public function testParseFromDocCommentThrowsWhenPermissionParameterIsMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Permission "admin" requires a parameter name.');

        $permissions = new MethodPermissions();
        $permissions->parseFromDocComment(<<<'DOC'
/**
 * @matomo-permission admin
 */
DOC
        );
    }

    public function testParseFromAttributesParsesAttributeOnlyPermission()
    {
        if (!method_exists(ReflectionMethod::class, 'getAttributes')) {
            $this->markTestSkipped('Reflection attributes are not available in this runtime.');
        }

        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(MethodPermissionsTestFixture::class, 'attributeOnlyMethod');

        $parsed = $permissions->parseFromAttributes($method);

        $this->assertSame(['name' => 'write', 'parameter' => 'idSite'], $parsed);
    }

    public function testResolveMethodPermissionsUsesDocblockAndAttributeMetadataForMigratedMethod()
    {
        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(\Piwik\Plugins\API\API::class, 'getPiwikVersion');

        $resolved = $permissions->resolveMethodPermissions($method);

        $this->assertSame(['name' => 'someView', 'parameter' => null], $resolved['permission']);
    }

    public function testResolveMethodPermissionsLogsAndIgnoresMismatchedMetadata()
    {
        if (!method_exists(ReflectionMethod::class, 'getAttributes')) {
            $this->markTestSkipped('Reflection attributes are not available in this runtime.');
        }

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('Ignoring mismatched API permission metadata'),
                $this->callback(function ($context) {
                    return $context['class'] === MethodPermissionsTestFixture::class
                        && $context['method'] === 'mismatchedMethod'
                        && $context['docblockPermission'] === 'someView'
                        && $context['attributePermission'] === 'superuser';
                })
            );
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $permissions = new MethodPermissions();
        $method = new ReflectionMethod(MethodPermissionsTestFixture::class, 'mismatchedMethod');

        $resolved = $permissions->resolveMethodPermissions($method);

        $this->assertNull($resolved['permission']);
        $this->assertFalse($resolved['hasPermissionMetadata']);
    }
}

class MethodPermissionsTestFixture
{
    #[Permission('write', '$idSite')]
    public function attributeOnlyMethod()
    {
    }

    /**
     * @matomo-permission someView
     */
    #[Permission('superuser')]
    public function mismatchedMethod()
    {
    }
}
