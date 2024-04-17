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
use Piwik\EventDispatcher;

/**
 * @group Core
 */
class DocumentationGeneratorTest extends TestCase
{
    public function test_CheckIfModule_ContainsHideAnnotation()
    {
        $annotation = '@hideExceptForSuperUser test test';
        $mock = $this->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->onlyMethods(array('getDocComment'))
            ->getMock();
        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);
        $documentationGenerator = new DocumentationGenerator();
        $this->assertTrue($documentationGenerator->checkIfClassCommentContainsHideAnnotation($mock));
    }

    public function test_CheckDocumentation()
    {
        $moduleToCheck = 'this is documentation which contains @hideExceptForSuperUser';
        $documentationAfterCheck = 'this is documentation which contains ';
        $documentationGenerator = new DocumentationGenerator();
        $this->assertEquals($documentationGenerator->checkDocumentation($moduleToCheck), $documentationAfterCheck);
    }

    public function test_CheckIfMethodComment_ContainsHideAnnotation_andText()
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

    public function test_CheckIfMethodComment_ContainsHideAnnotation_only()
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

    public function test_CheckIfMethodComment_DoesNotContainHideAnnotation()
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
}
