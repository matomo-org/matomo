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
use ReflectionMethod;

/**
 * @group Core
 */
class DocumentationGeneratorTest extends TestCase
{
    public function tearDown(): void
    {
        unset($_GET['period'], $_GET['date']);

        parent::tearDown();
    }

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

    public function testAddExamplesUsesTheRequestedPeriodAndDate()
    {
        $_GET['period'] = 'week';
        $_GET['date'] = '2020-01-02';

        $examples = $this->addExamples();

        self::assertStringContainsString('&period=week&', $examples);
        self::assertStringContainsString('&date=2020-01-02&', $examples);
    }

    public function testAddExamplesEncodesTheRequestedPeriodAndDate()
    {
        $_GET['period'] = 'range';
        $_GET['date'] = '2020-01-02,2020-01-31';

        $examples = $this->addExamples();

        self::assertStringContainsString('&period=range&', $examples);
        self::assertStringContainsString('&date=2020-01-02%2C2020-01-31&', $examples);
    }

    /**
     * @dataProvider getInvalidPeriodsAndDates
     */
    public function testAddExamplesFallsBackToDefaultsForInvalidPeriodsAndDates($period, $date)
    {
        $_GET['period'] = $period;
        $_GET['date'] = $date;

        $examples = $this->addExamples();

        self::assertStringNotContainsString('unexpected', $examples);
        self::assertStringContainsString('&period=day&', $examples);
        self::assertStringContainsString('&date=today&', $examples);
    }

    public function getInvalidPeriodsAndDates(): iterable
    {
        yield 'unknown period and date' => ['notaperiod', 'notadate'];
        yield 'period and date containing a separator' => ['day&unexpected=1', 'today&unexpected=1'];
        yield 'empty period and date' => ['', ''];
    }

    private function addExamples(): string
    {
        $documentationGenerator = new DocumentationGenerator();

        $class = Request::getClassNameAPI('API');
        Proxy::getInstance()->registerClass($class);

        $addExamples = new ReflectionMethod($documentationGenerator, 'addExamples');
        $addExamples->setAccessible(true);

        return $addExamples->invoke($documentationGenerator, $class, 'get', '');
    }
}
