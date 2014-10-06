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
    public function testCheckIfModuleContainsHideAnnotation()
    {
        $annotation = '@hide ExceptForSuperUser test test';
        $mock = $this->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocComment'))
            ->getMock();
        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);
        $documentationGenerator = new DocumentationGenerator();
        $this->assertTrue($documentationGenerator->checkIfClassCommentContainsHideAnnotation($mock));
    }
    public function testCheckDocumentation()
    {
        $moduleToCheck = 'this is documentation which contains @hide ExceptForSuperUser';
        $documentationAfterCheck = 'this is documentation which contains ';
        $documentationGenerator = new DocumentationGenerator();
        $this->assertEquals($documentationGenerator->checkDocumentation($moduleToCheck), $documentationAfterCheck);
    }
    public function testCheckIfMethodCommentContainsHideAnnotation()
    {
        $annotation = '@hide ForAll test test';
        $mock = $this->getMockBuilder('ReflectionMethod')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocComment'))
            ->getMock();
        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);
        EventDispatcher::getInstance()->addObserver('API.DocumentationGenerator.hideForAll',
            function (&$response) {
                $response = true;
            });
        $this->assertEquals(Proxy::getInstance()->checkIfMethodContainsHideAnnotation($mock), true);
    }
}