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

/**
 * @group Core
 */
class DocumentationGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testCheckIfModuleContainsHideAnnotation()
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

    public function testCheckDocumentation()
    {
        $moduleToCheck = 'this is documentation which contains @hideExceptForSuperUser';
        $documentationAfterCheck = 'this is documentation which contains ';

        $documentationGenerator = new DocumentationGenerator();
        $this->assertEquals($documentationGenerator->checkDocumentation($moduleToCheck), $documentationAfterCheck);
    }

    public function testCheckIfMethodCommentContainsHideAnnotation()
    {
        $annotation = '@hideExceptForSuperUser test test';

        $mock = $this->getMockBuilder('ReflectionMethod')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocComment'))
            ->getMock();

        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);

        $this->assertEquals(Proxy::getInstance()->checkIfMethodContainsHideAnnotation($mock), $annotation);
    }

    public function testPrepareModuleToDisplay()
    {
        $moduleName = 'VisitTime';
        $moduleToDisplay = "<a href='#VisitTime'>VisitTime</a><br/>";
        $documentationGenerator = new DocumentationGenerator();

        $this->assertEquals($documentationGenerator->prepareModuleToDisplay($moduleName), $moduleToDisplay);
    }

    /**
     * @dataProvider providerPrepareModulesAndMethods
     */
    public function testPrepareModulesAndMethods($toDisplay, $actualModulesAndMethods)
    {
        $this->assertEquals($toDisplay, $actualModulesAndMethods);
    }

    public function providerPrepareModulesAndMethods()
    {
        $toDisplay = array(
            'VisitTime'=>
                array(
                    'getVisitInformationPerLocalTime',
                    'getVisitInformationPerServerTime',
                    'getByDayOfWeek'
                )
        );

        $info = array(
            'getVisitInformationPerLocalTime' => array(
                'idSite',
                'period',
                'date'
            ),
            'getVisitInformationPerServerTime' => array(
                'idSite',
                'period',
                'date'
            ),
            'getByDayOfWeek' => array(
                'idSite',
                'period',
                'date'
            ),
            '__documentation' =>
                'VisitTime API lets you access reports by Hour (Server time), and by Hour Local Time of your visitors.',
        );

        $moduleName = 'VisitTime';

        $documentationGenerator = New DocumentationGenerator();
        $actualModulesAndMethods = $documentationGenerator->prepareModulesAndMethods($info, $moduleName);

        return array(
            array($toDisplay, $actualModulesAndMethods)
        );
    }

    /**
     * @dataProvider providerPrepareMethodToDisplay
     */
    public function testPrepareMethodToDisplay($elementShouldContainsInMethods, $methods)
    {
        $this->assertContains($elementShouldContainsInMethods, $methods);
    }

    public function providerPrepareMethodToDisplay()
    {
        $info = array(
            'sendFeedbackForFeature' => array(
                'featureName',
                'like',
            ),
            '__documentation' =>  'API for plugin Feedback',
        );

        $moduleName = 'Feedback';

        $methods = array(
            'sendFeedbackForFeature'
        );

        $class = '\Piwik\Plugins\Feedback\API';
        $outputExampleUrls = true;
        $prefixUrls = '';

        $firstElementToAssert = "<a  name='Feedback' id='Feedback'></a><h2>Module Feedback</h2>"
            ."<div class='apiDescription'> API for plugin Feedback </div>";
        $secondElementToAssert = "<div class='apiMethod'>- <b>Feedback.sendFeedbackForFeature </b>"
            ."(featureName, like, message = '')"
            ."<small><span class=\"example\"> [ No example available ]</span></small></div>";

        $documentationGenerator = new DocumentationGenerator();

        $preparedMethods = $documentationGenerator->prepareMethodToDisplay(
            $moduleName,
            $info,
            $methods,
            $class,
            $outputExampleUrls,
            $prefixUrls
        );

        return array(
            array($firstElementToAssert, $preparedMethods),
            array($secondElementToAssert, $preparedMethods)
        );
    }

    /**
     * @dataProvider providerAddExamples
     */
    public function testAddExamples($example, $examples)
    {
        $this->assertContains($example, $examples);
    }

    public function providerAddExamples()
    {
        $class = '\Piwik\Plugins\VisitTime\API';
        $methodName = 'getVisitInformationPerLocalTime';
        $prefixUrls = '';

        $documentationGenerator = new DocumentationGenerator();

        $xmlExample = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime"
            ."&idSite=1&period=day&date=today&format=xml&token_auth='>XML</a>";
        $jsonExample = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime"
            ."&idSite=1&period=day&date=today&format=JSON&token_auth='>Json</a>";
        $excelElement = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime"
            ."&idSite=1&period=day&date=today&format=Tsv&token_auth=&translateColumnNames=1'>Tsv (Excel)</a>";
        $rss = "RSS of the last <a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime"
            ."&idSite=1&period=day&date=last10&format=rss&token_auth=&translateColumnNames=1'>10 days</a>";

        $examples = $documentationGenerator->addExamples($class, $methodName, $prefixUrls);

        return array(
            array($xmlExample, $examples),
            array($jsonExample, $examples),
            array($excelElement, $examples),
            array($rss, $examples)
        );
    }

    public function testGetExampleUrl()
    {
        $class = '\Piwik\Plugins\VisitTime\API';
        $methodName = 'getVisitInformationPerLocalTime';
        $parametersToSet = array(
            'idSite' => 1,
            'period' => 'day',
            'date' => 'yesterday'
        );

        $expectedExampleUrl =
            '?module=API&method=VisitTime.getVisitInformationPerLocalTime&idSite=1&period=day&date=yesterday';

        $documentationGenerator = new DocumentationGenerator();

        $this->assertEquals(
            $expectedExampleUrl,
            $documentationGenerator->getExampleUrl($class, $methodName, $parametersToSet));
    }

    public function testGetParametersString()
    {
        $class = '\Piwik\Plugins\VisitTime\API';
        $name = 'getVisitInformationPerLocalTime';

        $parameters = "(idSite, period, date, segment = '')";

        $documentationGenerator = new DocumentationGenerator();

        $this->assertEquals($parameters, $documentationGenerator->getParametersString($class, $name));
    }
}
