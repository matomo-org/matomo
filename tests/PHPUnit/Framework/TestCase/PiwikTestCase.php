<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestAspect;

/**
 * TODO
 *
 * @testWithPiwikEnvironment
 */
class PiwikTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestAspect[][]
     */
    private static $testCaseAspects = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $thisClass = get_called_class();
        $testAspects = self::getTestAspects($thisClass);

        foreach ($testAspects as $aspect) {
            $aspect->setUpBeforeClass($thisClass);
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $thisClass = get_class($this);

        /** @var TestAspect[] $testAspects */
        $testAspects = array_merge(
            self::getTestAspects($thisClass),
            self::getTestAspects($thisClass, $this->getName(false))
        );

        foreach ($testAspects as $aspect) {
            $aspect->setUp($this);
        }
    }

    protected function tearDown()
    {
        $thisClass = get_class($this);

        /** @var TestAspect[] $testAspects */
        $testAspects = array_merge(
            self::getTestAspects($thisClass),
            self::getTestAspects($thisClass, $this->getName(false))
        );

        foreach ($testAspects as $aspect) {
            $aspect->tearDown($this);
        }

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        $thisClass = get_called_class();
        $testAspects = self::getTestAspects($thisClass);

        foreach ($testAspects as $aspect) {
            $aspect->tearDownAfterClass($thisClass);
        }

        parent::tearDownAfterClass();
    }

    /**
     * @param $thisClass
     * @param string $methodName
     * @return \Piwik\Tests\Framework\TestAspect[]
     */
    private static function getTestAspects($thisClass, $methodName = '')
    {
        $key = empty($methodName) ? $thisClass : ($thisClass . '.' . $methodName);
        if (empty(self::$testCaseAspects[$key])) {
            $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations($thisClass, $methodName);

            if (empty(self::$testCaseAspects[$thisClass])) {
                $classAspects = self::getTestAspectsFromAnnotations($annotations['class'], $typeToGet = 'class');
                self::$testCaseAspects[$thisClass] = $classAspects;
            }

            if (!empty($methodName)) {
                $methodAspects = self::getTestAspectsFromAnnotations($annotations['method'], $typeToGet = 'method');
                self::$testCaseAspects[$thisClass . '.' . $methodName] = $methodAspects;
            }
        }

        $result = self::$testCaseAspects[$key];

        $baseClass = get_parent_class($thisClass);
        if (!empty($baseClass)
            && $baseClass != 'PHPUnit_Framework_TestCase'
        ) {
            $result = array_merge(self::getTestAspects($baseClass, $methodName), $result);
        }

        return $result;
    }

    private static function getTestAspectsFromAnnotations($annotations, $typeToGet)
    {
        $testAspects = array();

        foreach ($annotations as $annotation => $values) {
            $testAspectClass = 'Piwik\Tests\Framework\TestAspect\\' . ucfirst($annotation);
            if (!class_exists($testAspectClass)) {
                continue;
            }

            $useAspect = $typeToGet == 'class' ? $testAspectClass::isClassAspect() : $testAspectClass::isMethodAspect();
            if (!$useAspect) {
                continue;
            }

            $values = array_filter($values);

            $reflectionClass = new \ReflectionClass($testAspectClass);
            $testAspects[] = $reflectionClass->newInstanceArgs($values);
        }

        return $testAspects;
    }

    public static function getTestCaseFixture($testCaseClass)
    {
        if (!isset($testCaseClass::$fixture)) {
            return new Fixture();
        } else {
            return $testCaseClass::$fixture;
        }
    }
}
