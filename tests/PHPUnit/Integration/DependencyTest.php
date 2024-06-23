<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugin\Dependency;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group Core
 */
class DependencyTest extends IntegrationTestCase
{
    /**
     * @var Dependency
     */
    private $dependency;

    public static function setUpBeforeClass(): void
    {
        // skip to set up fixture, as it's not needed for tests
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->dependency = new Dependency();
    }

    public function testGetMissingDependenciesShouldReturnEmptyArrayIfNoInputGiven()
    {
        $this->assertMissingDependency(null, array());
        $this->assertMissingDependency(array(), array());
    }

    public function testGetMissingDependenciesEmptyVersionShouldBeIgnored()
    {
        $this->assertMissingDependency(array('php' => ''), array());
    }

    public function testGetMissingDependenciesMultipleConditions()
    {
        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '<2.0'), array(
            $this->missingPhp('<5.2'),
            $this->missingPiwik('>=2.0.0-b1,<2.0', '<2.0')
        ));

        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '>=4.0.0-b1,<9.0'), array(
            $this->missingPhp('<5.2')
        ));

        $this->assertMissingDependency(array('php' => '<9.2', 'piwik' => '<2.0'), array(
            $this->missingPiwik('>=2.0.0-b1,<2.0', '<2.0')
        ));

        $this->assertMissingDependency(array('php' => '<9.2', 'piwik' => '>=2.0,<9.0'), array());
    }

    public function testGetMissingDependenciesMultipleConditionsDifferentConditions()
    {
        $this->assertMissingDependency(array('php' => '<5.2', 'piwik' => '>2.0,<9.0.0'), array(
            $this->missingPhp('<5.2')
        ));

        $this->assertMissingDependency(array('php' => '>=5.3', 'piwik' => '>1.0,<2.0'), array(
            $this->missingPiwik('>1.0,<2.0', '<2.0')
        ));

        $this->assertMissingDependency(array('php' => '!=' . $this->formatPhpVersion(), 'piwik' => '<>' . Version::VERSION), array(
            $this->missingPhp('!=' . $this->formatPhpVersion()),
            $this->missingPiwik('<>' . Version::VERSION . ',<' . (Version::MAJOR_VERSION + 1) . '.0.0-b1', '<>' . Version::VERSION),
        ));
    }

    public function testGetMissingVersionANDCondition()
    {
        $this->assertMissingDependency(array('php' => '<2.0,>=9.0', 'piwik' => '>=3.0.0-b1,<4.0.0-b1'), array(
            $this->missingPhp('<2.0,>=9.0', '<2.0, >=9.0'),
            $this->missingPiwik('>=3.0.0-b1,<4.0.0-b1', '<4.0.0-b1')
        ));
    }

    public function testGetMissingDependenciesDetectsPHPVersion()
    {
        $phpVersion = $this->formatPhpVersion();
        $this->assertMissingDependency(array('php' => '>=2.1'), array());
        $this->assertMissingDependency(array('php' => '>=' . $phpVersion), array());
        $this->assertMissingDependency(array('php' => '>' . $phpVersion), array(
            $this->missingPhp('>' . $phpVersion),
        ));
        $this->assertMissingDependency(array('php' => '>=9.2'), array(
            $this->missingPhp('>=9.2'),
        ));
    }

    public function testGetMissingDependenciesDetectsPiwikVersion()
    {
        $this->assertMissingDependency(array('piwik' => '>=2.1,<9.0.0'), array());
        $this->assertMissingDependency(array('piwik' => '>=' . Version::VERSION), array());
        $this->assertMissingDependency(array('piwik' => '>' . Version::VERSION), array(
            $this->missingPiwik('>' . Version::VERSION . ',<' . (Version::MAJOR_VERSION + 1) . '.0.0-b1', '>' . Version::VERSION)
        ));
        $this->assertMissingDependency(array('piwik' => '>=9.2'), array(
            $this->missingPiwik('>=9.2,<10.0.0-b1', '>=9.2')
        ));
    }

    public function testGetMissingDependenciesDetectUnknownDependencyName()
    {
        $this->assertMissingDependency(array('unkNowN' => '>99.99'), array(
            $this->buildMissingDependecy('unkNowN', '', '>99.99')
        ));
        $this->assertMissingDependency(array('unkNowN' => '>=0.01'), array(
            $this->buildMissingDependecy('unkNowN', '', '>=0.01')
        ));
    }

    public function testGetMissingDependenciesDetectsPluginVersion()
    {
        PluginManager::getInstance()->loadAllPluginsAndGetTheirInfo();

        $this->assertMissingDependency(array('Annotations' => '>=2.1'), array());
        $this->assertMissingDependency(array('Annotations' => '>=' . Version::VERSION), array());
        $this->assertMissingDependency(array('Annotations' => '>' . Version::VERSION), array(
            $this->buildMissingDependecy('Annotations', Version::VERSION, '>' . Version::VERSION),
        ));
        $this->assertMissingDependency(array('Annotations' => '>=9.2'), array(
            $this->buildMissingDependecy('Annotations', Version::VERSION, '>=9.2'),
        ));
    }

    public function testGetMissingDependenciesSetPiwikVersion()
    {
        $this->assertMissingDependency(array('piwik' => '>=9.2'), array($this->missingPiwik('>=9.2,<10.0.0-b1', '>=9.2')));

        $this->dependency->setPiwikVersion('9.2');

        $this->assertMissingDependency(array('piwik' => '>=9.2'), array());
    }

    public function testGetMissingVersionEmptyCurrentAndRequiredVersionShouldBeIgnored()
    {
        $this->assertMissingVersion(null, null, array());
        $this->assertMissingVersion('', '', array());
    }

    public function testGetMissingVersionEmptyCurrentVersionShouldBeDeclaredAsMissing()
    {
        $this->assertMissingVersion('', '>=5.5', array('>=5.5'));
    }

    public function testGetMissingVersionEmptyRequiredVersionShouldBeIgnored()
    {
        $this->assertMissingVersion('5.5', '', array());
    }

    public function testGetMissingVersionShouldIgnoreAnyWhitespace()
    {
        $this->assertMissingVersion('5.5 ', '5.5', array());
        $this->assertMissingVersion(' 5.5 ', '5.5', array());
        $this->assertMissingVersion('5.5', ' 5.5', array());
        $this->assertMissingVersion('5.5', ' 5.5 ', array());
    }

    public function testGetMissingVersionNoComparisonDefinedShouldUseGreatherThanOrEqualByDefault()
    {
        $this->assertMissingVersion('5.4', '5.2', array());
        $this->assertMissingVersion('5.4', '5.4', array());
        $this->assertMissingVersion('5.4', '9.2', array('>=9.2'));
    }

    public function testGetMissingVersionGreatherThanOrEqual()
    {
        $this->assertMissingVersion('5.4', '>=5.2', array());
        $this->assertMissingVersion('5.4', '>=5.4', array());
        $this->assertMissingVersion('5.4', '>=9.2', array('>=9.2'));
    }

    public function testGetMissingVersionGreatherThan()
    {
        $this->assertMissingVersion('5.4', '>5.2', array());
        $this->assertMissingVersion('5.4', '>5.4', array('>5.4'));
        $this->assertMissingVersion('5.4', '>9.2', array('>9.2'));
    }

    public function testGetMissingVersionLowerThanOrEqual()
    {
        $this->assertMissingVersion('5.4', '<=5.2', array('<=5.2'));
        $this->assertMissingVersion('5.4', '<=5.4', array());
        $this->assertMissingVersion('5.4', '<=9.2', array());
    }

    public function testGetMissingVersionLowerThan()
    {
        $this->assertMissingVersion('5.4', '<5.2', array('<5.2'));
        $this->assertMissingVersion('5.4', '<5.4', array('<5.4'));
        $this->assertMissingVersion('5.4', '<9.2', array());
    }

    public function testGetMissingVersionNotEqual()
    {
        $this->assertMissingVersion('5.4', '<>5.2', array());
        $this->assertMissingVersion('5.4', '<>5.4', array('<>5.4'));
        $this->assertMissingVersion('5.4', '<>9.2', array());
    }

    public function testGetMissingVersionNotEqualUsingBang()
    {
        $this->assertMissingVersion('5.4', '!=5.2', array());
        $this->assertMissingVersion('5.4', '!=5.4', array('!=5.4'));
        $this->assertMissingVersion('5.4', '!=9.2', array());
    }

    public function testGetMissingVersionExact()
    {
        $this->assertMissingVersion('5.4', '==5.2', array('==5.2'));
        $this->assertMissingVersion('5.4', '==5.4', array());
        $this->assertMissingVersion('5.4', '==9.2', array('==9.2'));
    }

    public function testGetMissingVersionANDConditionReturnsOnlyNonMatchingVersions()
    {
        $this->assertMissingVersion('5.4', '<5.2,>9.0', array('<5.2', '>9.0'));
        $this->assertMissingVersion('5.4', '>5.2,<9.0', array());
        $this->assertMissingVersion('5.4', '>5.2,<9.0,<2.0', array('<2.0'));
        $this->assertMissingVersion('5.4', '>5.2,<9.0,<2.0,>=9.0', array('<2.0', '>=9.0'));
        $this->assertMissingVersion('5.4', '<2.0,>=9.0', array('<2.0', '>=9.0'));
    }

    public function testGetMissingVersionANDConditionShouldIgnoreAnyWhitespace()
    {
        $this->assertMissingVersion('5.2', '5.5 , 5.4,   5.3', array('>=5.5', '>=5.4', '>=5.3'));
        $this->assertMissingVersion('5.5', '5.5 , 5.4,   5.3', array());
        $this->assertMissingVersion(' 5.2 ', '5.5 , 5.4,   5.3', array('>=5.5', '>=5.4', '>=5.3'));
        $this->assertMissingVersion(' 5.2 ', '>5.5 , <5.4,   ==5.3', array('>5.5', '==5.3'));
        $this->assertMissingVersion(' 5.2 ', '>5.5 , !=5.4,   ==5.3', array('>5.5', '==5.3'));
    }

    public function testGetMissingVersion()
    {
        $this->assertMissingVersion('5.2', '<5.2,>9.0', array('<5.2', '>9.0'));
        $this->assertMissingVersion('5.2', '<=5.2,>9.0', array('>9.0'));
        $this->assertMissingVersion('5.1', '<5.2,>9.0', array('>9.0'));
        $this->assertMissingVersion('9.1', '<5.2,>9.0', array('<5.2'));
        $this->assertMissingVersion('5.2', '>=5.2,<=9.0', array());
        $this->assertMissingVersion('9.0', '>=5.2,<=9.0', array());
        $this->assertMissingVersion('6.4', '>=5.2,<=9.0', array());
    }

    /**
     * @dataProvider getHasDepenedencyToDisabledPluginProvider
     */
    public function testHasDependencyToDisabledPlugin($expectedHasDependency, $requires)
    {
        $this->assertSame($expectedHasDependency, $this->dependency->hasDependencyToDisabledPlugin($requires));
    }

    public function getHasDepenedencyToDisabledPluginProvider()
    {
        return array(
            array($expected = false, $requires = null),
            array($expected = false, $requires = array()),
            array($expected = false, $requires = array('php' => '<5.2', 'piwik' => '<2.0')),
            array($expected = false, $requires = array('php' => '<5.2', 'piwik' => '<2.0', 'CoreHome' => '2.15.0')),
            array($expected = false, $requires = array('CoreHome' => '<2.0', 'Actions' => '>=2.15.0')),
            array($expected = true, $requires = array('php' => '<5.2', 'piwik' => '<2.0', 'FooBar' => '2.15.0')),
        );
    }

    private function missingPiwik($requiredVersion, $causedBy = null)
    {
        return $this->buildMissingDependecy('piwik', Version::VERSION, $requiredVersion, $causedBy);
    }

    private function missingPhp($requiredVersion, $causedBy = null)
    {
        return $this->buildMissingDependecy('php', $this->formatPhpVersion(), $requiredVersion, $causedBy);
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

    /*
     * remove all the ubuntu and system text
     */
    private function formatPhpVersion()
    {
        preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $phpversion);

        return $phpversion[0];
    }

    private function assertMissingDependency($requires, $expectedMissing)
    {
        $missing = $this->dependency->getMissingDependencies($requires);

        $this->assertEquals($expectedMissing, $missing);
    }

    private function assertMissingVersion($currentVersion, $requiredVersion, $expectedMissing)
    {
        $missing = $this->dependency->getMissingVersions($currentVersion, $requiredVersion);

        $this->assertEquals($expectedMissing, $missing);
    }
}
