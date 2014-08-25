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
class hideModulesAndMethodsTest extends PHPUnit_Framework_TestCase
{
    public function testCheckIfModuleContainsHideAnnotation()
    {
        $annotation = '@hideExceptForSuperUser test test';
        $mock = $this->getMockBuilder('ReflectionClass')->disableOriginalConstructor()->setMethods(
            array('getDocComment')
        )->getMock();
        $mock->expects($this->once())->method('getDocComment')->willReturn($annotation);

        $documentationGenerator = new DocumentationGenerator();
        $this->assertEquals($documentationGenerator->checkIfClassCommentContainsHideAnnotation($mock), $annotation);
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

        $mock = $this->getMockBuilder('ReflectionMethod')->disableOriginalConstructor()->setMethods(
            array('getDocComment')
        )->getMock();
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

    public function testPrepareModulesAndMethods()
    {
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

        $toDisplay = array(
            'VisitTime'=>
                array(
                    'getVisitInformationPerLocalTime',
                    'getVisitInformationPerServerTime',
                    'getByDayOfWeek'
                )
        );

        $documentationGenerator = New DocumentationGenerator();

        $this->assertEquals($toDisplay, $documentationGenerator->prepareModulesAndMethods($info, $moduleName));
    }

    public function testPrepareMethodToDisplay()
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

        $firstElementToAssert = "<a  name='Feedback' id='Feedback'></a><h2>Module Feedback</h2><div class='apiDescription'> API for plugin Feedback </div>";
        $secondElementToAssert = "<div class='apiMethod'>- <b>Feedback.sendFeedbackForFeature </b>(featureName, like, message = '')<small><span class=\"example\"> [ No example available ]</span></small></div>";

        $documentationGenerator = new DocumentationGenerator();

        $this->assertContains(
            $firstElementToAssert,
            $documentationGenerator->prepareMethodToDisplay(
                $moduleName, $info, $methods, $class, $outputExampleUrls, $prefixUrls
            )
        );
        $this->assertContains(
            $secondElementToAssert,
            $documentationGenerator->prepareMethodToDisplay(
                $moduleName, $info, $methods, $class, $outputExampleUrls, $prefixUrls
            )
        );
    }

    public function testAddExamples()
    {
        $class = '\Piwik\Plugins\VisitTime\API';
        $methodName = 'getVisitInformationPerLocalTime';
        $prefixUrls = '';

        $documentationGenerator = new DocumentationGenerator();

        $xmlExample = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime&idSite=1&period=day&date=today&format=xml&token_auth='>XML</a>";
        $jsonExample = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime&idSite=1&period=day&date=today&format=JSON&token_auth='>Json</a>";
        $excelElement = "<a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime&idSite=1&period=day&date=today&format=Tsv&token_auth=&translateColumnNames=1'>Tsv (Excel)</a>";
        $rss = "RSS of the last <a target=_blank href='?module=API&method=VisitTime.getVisitInformationPerLocalTime&idSite=1&period=day&date=last10&format=rss&token_auth=&translateColumnNames=1'>10 days</a>";

        $this->assertContains($xmlExample, $documentationGenerator->addExamples($class, $methodName, $prefixUrls));
        $this->assertContains($jsonExample, $documentationGenerator->addExamples($class, $methodName, $prefixUrls));
        $this->assertContains($excelElement, $documentationGenerator->addExamples($class, $methodName, $prefixUrls));
        $this->assertContains($rss, $documentationGenerator->addExamples($class, $methodName, $prefixUrls));
    }
}
