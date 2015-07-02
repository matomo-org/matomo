<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\Container\StaticContainer;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\TestCase\UnitTestCase;

/**
 * @group Core
 */
class DocumentationGeneratorTest extends UnitTestCase
{
    public function test_CheckIfModule_ContainsHideAnnotation()
    {
        $annotation = '@hideExceptForSuperUser test test';
        $mock = $this->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocComment'))
            ->getMock();
        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);
        /** @var DocumentationGenerator $documentationGenerator */
        $documentationGenerator = $this->environment->getContainer()->get('Piwik\API\DocumentationGenerator');
        $this->assertTrue($documentationGenerator->checkIfClassCommentContainsHideAnnotation($mock));
    }

    public function test_CheckDocumentation()
    {
        $moduleToCheck = 'this is documentation which contains @hideExceptForSuperUser';
        $documentationAfterCheck = 'this is documentation which contains ';
        /** @var DocumentationGenerator $documentationGenerator */
        $documentationGenerator = $this->environment->getContainer()->get('Piwik\API\DocumentationGenerator');
        $this->assertEquals($documentationGenerator->checkDocumentation($moduleToCheck), $documentationAfterCheck);
    }

    public function test_CheckIfMethodComment_ContainsHideAnnotation_andText()
    {
        $annotation = '@hideForAll test test';
        $this->getEventDispatcher()->addObserver('API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals($this->getApiProxy()->shouldHideAPIMethod($annotation), true);
    }

    public function test_CheckIfMethodComment_ContainsHideAnnotation_only()
    {
        $annotation = '@hideForAll';
        $this->getEventDispatcher()->addObserver('API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals($this->getApiProxy()->shouldHideAPIMethod($annotation), true);
    }

    public function test_CheckIfMethodComment_DoesNotContainHideAnnotation()
    {
        $annotation = '@not found here';
        $this->getEventDispatcher()->addObserver('API.DocumentationGenerator.@hello',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals($this->getApiProxy()->shouldHideAPIMethod($annotation), false);
    }

    /**
     * @return Proxy
     */
    private function getApiProxy()
    {
        return $this->environment->getContainer()->get('Piwik\API\Proxy');
    }

    private function getEventDispatcher()
    {
        return StaticContainer::get('Piwik\EventDispatcher');
    }
}