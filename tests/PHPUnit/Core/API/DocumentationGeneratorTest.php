<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */
use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\EventDispatcher;
use Piwik\Plugin\Manager as PluginManager;

/**
 * @group Core
 */
class DocumentationGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function test_CheckIfModule_ContainsHideAnnotation()
    {
        $annotation = '@hideExceptForSuperUser test test';
        $mock = $this->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocComment'))
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
        EventDispatcher::getInstance()->addObserver('API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), true);
    }

    public function test_CheckIfMethodComment_ContainsHideAnnotation_only()
    {
        $annotation = '@hideForAll';
        EventDispatcher::getInstance()->addObserver('API.DocumentationGenerator.@hideForAll',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), true);
    }

    public function test_CheckIfMethodComment_DoesNotContainHideAnnotation()
    {
        $annotation = '@not found here';
        EventDispatcher::getInstance()->addObserver('API.DocumentationGenerator.@hello',
            function (&$hide) {
                $hide = true;
            });
        $this->assertEquals(Proxy::getInstance()->shouldHideAPIMethod($annotation), false);
    }
}