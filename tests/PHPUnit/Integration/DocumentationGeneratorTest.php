<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\EventDispatcher;
use ReflectionClass;

/**
 * @group Core
 */
class DocumentationGeneratorTest extends TestCase
{
    public function testCheckIfModuleContainsHideAnnotation()
    {
        $reflection = new ReflectionClass(DocumentationGenerator::class);
        $documentationGenerator = new DocumentationGenerator();
        $this->assertTrue($documentationGenerator->checkIfClassCommentContainsHideAnnotation($reflection));
    }

    public function testCheckDocumentation()
    {
        $moduleToCheck = 'this is documentation which contains @hideExceptForSuperUser';
        $documentationAfterCheck = 'this is documentation which contains ';
        $documentationGenerator = new DocumentationGenerator();
        $this->assertEquals($documentationGenerator->checkDocumentation($moduleToCheck), $documentationAfterCheck);
    }

    public function testCheckIfMethodCommentContainsHideAnnotationAndText()
    {
        $annotation = '@hideForAll test test';
        EventDispatcher::getInstance()->addObserver(
            'API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            }
        );
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), true);
    }

    public function testCheckIfMethodCommentContainsHideAnnotationOnly()
    {
        $annotation = '@hideForAll';
        EventDispatcher::getInstance()->addObserver(
            'API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            }
        );
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), true);
    }

    public function testCheckIfMethodCommentDoesNotContainHideAnnotation()
    {
        $annotation = '@not found here';
        EventDispatcher::getInstance()->addObserver(
            'API.DocumentationGenerator.@hello',
            function (&$hide) {
                $hide = true;
            }
        );
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), false);
    }

    public function testProxyMetadataExposesExplicitPermissionsOnly()
    {
        $apiClass = Request::getClassNameAPI('API');
        $usersManagerClass = Request::getClassNameAPI('UsersManager');

        Proxy::getInstance()->registerClass($apiClass);
        Proxy::getInstance()->registerClass($usersManagerClass);

        $this->assertSame(
            ['name' => 'someView', 'parameter' => null],
            Proxy::getInstance()->getMethodPermission($apiClass, 'getPiwikVersion')
        );

        $this->assertSame(
            ['name' => 'superUserOrUser', 'parameter' => 'userLogin'],
            Proxy::getInstance()->getMethodPermission($usersManagerClass, 'setUserPreference')
        );

        $this->assertNull(
            Proxy::getInstance()->getMethodPermission($apiClass, 'getSettings')
        );
    }

    public function testGeneratedDocumentationDisplaysPermissionsForMigratedMethod()
    {
        $documentation = new DocumentationGenerator();

        $result = $documentation->getApiDocumentationAsString(false);

        $this->assertStringContainsString('[Permissions: someView]', $result);
        $this->assertStringContainsString('[Permissions: superUserOrUser(userLogin)]', $result);
    }
}
