<?php
use Piwik\Plugin\Dependency;
use Piwik\Version;
use Piwik\Plugin\Manager as PluginManager;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @group Core
 */
class DependencyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    public function setUp()
    {
        parent::setUp();
        $this->dependency = new Dependency();
    }

    public function test_getMissingDependencies_shouldReturnEmptyArray_IfNoInputGiven()
    {
        $this->assertMissingDependency(null, array());
        $this->assertMissingDependency(array(), array());
    }

    public function test_getMissingDependencies_EmptyVersion_ShouldBeIgnored()
    {
        $this->assertMissingDependency(array('php' => ''), array());
    }

    public function test_getMissingDependencies_NoComparisonDefined_ShouldUseGreatherThanOrEqualByDefault()
    {
        $this->assertMissingDependency(array('php' => '5.2'), array());
        $this->assertMissingDependency(array('php' => PHP_VERSION), array());
        $this->assertMissingDependency(array('php' => '9.2'), array(
            $this->missingPhp('9.2', '>=9.2')
        ));
    }

    public function test_getMissingDependencies_GreatherThanOrEqual()
    {
        $this->assertMissingDependency(array('php' => '>=5.2'), array());
        $this->assertMissingDependency(array('php' => '>=' . PHP_VERSION), array());
        $this->assertMissingDependency(array('php' => '>=9.2'), array(
            $this->missingPhp('>=9.2')
        ));
    }

    public function test_getMissingDependencies_GreatherThan()
    {
        $this->assertMissingDependency(array('php' => '>5.2'), array());
        $this->assertMissingDependency(array('php' => '>' . PHP_VERSION), array(
            $this->missingPhp('>' . PHP_VERSION)
        ));
        $this->assertMissingDependency(array('php' => '>9.2'), array(
            $this->missingPhp('>9.2')
        ));
    }

    public function test_getMissingDependencies_LowerThanOrEqual()
    {
        $this->assertMissingDependency(array('php' => '<=5.2'), array(
            $this->missingPhp('<=5.2')
        ));
        $this->assertMissingDependency(array('php' => '<=' . PHP_VERSION), array());
        $this->assertMissingDependency(array('php' => '<=9.2'), array());
    }

    public function test_getMissingDependencies_lowerThan()
    {
        $this->assertMissingDependency(array('php' => '<5.2'), array(
            $this->missingPhp('<5.2')
        ));
        $this->assertMissingDependency(array('php' => '<' . PHP_VERSION), array(
            $this->missingPhp('<' . PHP_VERSION)
        ));
        $this->assertMissingDependency(array('php' => '<9.2'), array());
    }

    public function test_getMissingDependencies_notEqual()
    {
        $this->assertMissingDependency(array('php' => '<>5.2'), array());
        $this->assertMissingDependency(array('php' => '<>' . PHP_VERSION), array(
            $this->missingPhp('<>' . PHP_VERSION)
        ));
        $this->assertMissingDependency(array('php' => '<>9.2'), array());
    }

    public function test_getMissingDependencies_notEqualUsingBang()
    {
        $this->assertMissingDependency(array('php' => '!=5.2'), array());
        $this->assertMissingDependency(array('php' => '!=' . PHP_VERSION), array(
            $this->missingPhp('!=' . PHP_VERSION)
        ));
        $this->assertMissingDependency(array('php' => '!=9.2'), array());
    }

    public function test_getMissingDependencies_exact()
    {
        $this->assertMissingDependency(array('php' => '==5.2'), array(
            $this->missingPhp('==5.2')
        ));
        $this->assertMissingDependency(array('php' => '==' . PHP_VERSION), array());
        $this->assertMissingDependency(array('php' => '==9.2'), array(
            $this->missingPhp('==9.2')
        ));
    }

    public function test_getMissingDependencies_multipleConditions()
    {
        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '<2.0'), array(
            $this->missingPhp('<5.2'),
            $this->missingPiwik('<2.0')
        ));

        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '<9.0'), array(
            $this->missingPhp('<5.2')
        ));

        $this->assertMissingDependency(array('php' => '<9.2', 'piwik' => '<2.0'), array(
            $this->missingPiwik('<2.0')
        ));

        $this->assertMissingDependency(array('php' => '<9.2', 'piwik' => '<9.0'), array());
    }

    public function test_getMissingDependencies_multipleConditions_differentConditions()
    {
        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '>2.0'), array(
            $this->missingPhp('<5.2')
        ));

        $this->assertMissingDependency(array('php' => '>=5.3', 'piwik' => '<2.0'), array(
            $this->missingPiwik('<2.0')
        ));

        $this->assertMissingDependency(array('php' => '!=' . PHP_VERSION, 'piwik' => '<>' . Version::VERSION), array(
            $this->missingPhp('!=' . PHP_VERSION),
            $this->missingPiwik('<>' . Version::VERSION)
        ));
    }

    public function test_getMissingDependencies_AND_Condition()
    {
        $this->assertMissingDependency(array('php' => '<5.2,>9.0'), array(
            $this->missingPhp('<5.2,>9.0', '<5.2, >9.0')
        ));

        $this->assertMissingDependency(array('php' => '>5.2,<9.0'), array());
        $this->assertMissingDependency(array('php' => '>5.2,<9.0,<2.0'), array(
            $this->missingPhp('>5.2,<9.0,<2.0', '<2.0')
        ));
        $this->assertMissingDependency(array('php' => '>5.2,<9.0,<2.0,>=9.0'), array(
            $this->missingPhp('>5.2,<9.0,<2.0,>=9.0', '<2.0, >=9.0')
        ));
        $this->assertMissingDependency(array('php' => '<2.0,>=9.0'), array(
            $this->missingPhp('<2.0,>=9.0', '<2.0, >=9.0')
        ));

        $this->assertMissingDependency(array('php' => '<2.0,>=9.0', 'piwik' => '>2.0'), array(
            $this->missingPhp('<2.0,>=9.0', '<2.0, >=9.0')
        ));

        $this->assertMissingDependency(array('php' => '<2.0,>=9.0', 'piwik' => '<2.0'), array(
            $this->missingPhp('<2.0,>=9.0', '<2.0, >=9.0'),
            $this->missingPiwik('<2.0')
        ));
    }

    public function test_getMissingDependencies_detectsPHPVersion()
    {
        $this->assertMissingDependency(array('php' => '>=2.1'), array());
        $this->assertMissingDependency(array('php' => '>=' . PHP_VERSION), array());
        $this->assertMissingDependency(array('php' => '>' . PHP_VERSION), array(
            $this->missingPhp('>' . PHP_VERSION)
        ));
        $this->assertMissingDependency(array('php' => '>=9.2'), array(
            $this->missingPhp('>=9.2')
        ));
    }

    public function test_getMissingDependencies_detectsPiwikVersion()
    {
        $this->assertMissingDependency(array('piwik' => '>=2.1'), array());
        $this->assertMissingDependency(array('piwik' => '>=' . Version::VERSION), array());
        $this->assertMissingDependency(array('piwik' => '>' . Version::VERSION), array(
            $this->missingPiwik('>' . Version::VERSION)
        ));
        $this->assertMissingDependency(array('piwik' => '>=9.2'), array(
            $this->missingPiwik('>=9.2')
        ));
    }

    public function test_getMissingDependencies_detectUnknownDependencyName()
    {
        $this->assertMissingDependency(array('unkNowN' => '>99.99'), array(
            $this->buildMissingDependecy('unkNowN', '', '>99.99')
        ));
        $this->assertMissingDependency(array('unkNowN' => '>=0.01'), array(
            $this->buildMissingDependecy('unkNowN', '', '>=0.01')
        ));
    }

    public function test_getMissingDependencies_detectsPluginVersion()
    {
        PluginManager::getInstance()->returnLoadedPluginsInfo();

        $this->assertMissingDependency(array('Annotations' => '>=2.1'), array());
        $this->assertMissingDependency(array('Annotations' => '>=' . Version::VERSION), array());
        $this->assertMissingDependency(array('Annotations' => '>' . Version::VERSION), array(
            $this->buildMissingDependecy('Annotations', Version::VERSION, '>' . Version::VERSION)
        ));
        $this->assertMissingDependency(array('Annotations' => '>=9.2'), array(
            $this->buildMissingDependecy('Annotations', Version::VERSION, '>=9.2')
        ));
    }

    public function test_getMissingDependencies_setPiwikVersion()
    {
        $missing = array($this->missingPiwik('>=9.2'));
        $this->assertEquals($missing, $this->dependency->getMissingDependencies(array('piwik' => '>=9.2')));

        $this->dependency->setPiwikVersion('9.2');
        $this->assertEquals(array(), $this->dependency->getMissingDependencies(array('piwik' => '>=9.2')));
    }

    public function test_getMissingVersion()
    {
        $this->assertEquals(array('<5.2', '>9.0'), $this->dependency->getMissingVersions('5.2', '<5.2,>9.0'));
        $this->assertEquals(array('>9.0'), $this->dependency->getMissingVersions('5.2', '<=5.2,>9.0'));
        $this->assertEquals(array('>9.0'), $this->dependency->getMissingVersions('5.1', '<5.2,>9.0'));
        $this->assertEquals(array('<5.2'), $this->dependency->getMissingVersions('9.1', '<5.2,>9.0'));

        $this->assertEquals(array(), $this->dependency->getMissingVersions('5.2', '>=5.2,<=9.0'));
        $this->assertEquals(array(), $this->dependency->getMissingVersions('9.0', '>=5.2,<=9.0'));
        $this->assertEquals(array(), $this->dependency->getMissingVersions('6.4', '>=5.2,<=9.0'));
    }

    private function missingPiwik($requiredVersion, $causedBy = null)
    {
        return $this->buildMissingDependecy('piwik', Version::VERSION, $requiredVersion, $causedBy);
    }

    private function missingPhp($requiredVersion, $causedBy = null)
    {
        return $this->buildMissingDependecy('php', PHP_VERSION, $requiredVersion, $causedBy);
    }

    private function buildMissingDependecy($name, $currentVersion, $requiredVersion, $causedBy = null)
    {
        if (is_null($causedBy)) {
            $causedBy = $requiredVersion;
        }

        return array(
            'requirement'     => $name,
            'actualVersion'   => $currentVersion,
            'requiredVersion' => $requiredVersion,
            'causedBy'        => $causedBy
        );
    }

    private function assertMissingDependency($requires, $expectedMissing)
    {
        $missing = $this->dependency->getMissingDependencies($requires);

        $this->assertEquals($expectedMissing, $missing);
    }

}

