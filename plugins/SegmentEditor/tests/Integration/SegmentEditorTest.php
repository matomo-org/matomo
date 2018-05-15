<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;

/**
 * Class Plugins_SegmentEditorTest
 *
 * @group Plugins
 */
class SegmentEditorTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        \Piwik\Plugin\Manager::getInstance()->loadPlugin('SegmentEditor');
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        // setup the access layer
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superusertest';

        APISitesManager::getInstance()->addSite('test', 'http://example.org');
    }

    /**
     * @group Plugins
     */
    public function testAddInvalidSegment_ShouldThrow()
    {
        try {
            API::getInstance()->add('name', 'test==test2');
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
        }
        try {
            API::getInstance()->add('name', 'test');
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
        }
    }

    /**
     * @group Plugins
     */
    public function test_AddAndGet_SimpleSegment()
    {
        $name = 'name';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $idSegment = API::getInstance()->add($name, $definition);
        $this->assertEquals($idSegment, 1);
        $segment = API::getInstance()->get($idSegment);
        unset($segment['ts_created']);
        $expected = array(
            'idsegment' => 1,
            'name' => $name,
            'definition' => $definition,
            'login' => 'superUserLogin',
            'enable_all_users' => '0',
            'enable_only_idsite' => '0',
            'auto_archive' => '0',
            'ts_last_edit' => null,
            'deleted' => '0',
        );

        $this->assertEquals($segment, $expected);
    }

    /**
     * @group Plugins
     */
    public function test_AddAndGet_AnotherSegment()
    {
        $name = 'name';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $idSegment = API::getInstance()->add($name, $definition, $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $this->assertEquals($idSegment, 1);

        // Testing get()
        $segment = API::getInstance()->get($idSegment);
        $expected = array(
            'idsegment' => '1',
            'name' => $name,
            'definition' => $definition,
            'login' => 'superUserLogin',
            'enable_all_users' => '1',
            'enable_only_idsite' => '1',
            'auto_archive' => '1',
            'ts_last_edit' => null,
            'deleted' => '0',
        );
        unset($segment['ts_created']);
        $this->assertEquals($segment, $expected);

        // There is a segment to process for this particular site
        $model = new Model();
        $segments = $model->getSegmentsToAutoArchive($idSite);
        unset($segments[0]['ts_created']);
        $this->assertEquals($segments, array($expected));

        // There is no segment to process for a non existing site
        try {
            $model->getSegmentsToAutoArchive(33);
            $this->fail();
        } catch(Exception $e) {
            // expected
        }

        // There is no segment to process across all sites
        $segments = $model->getSegmentsToAutoArchive($idSite = false);
        $this->assertEquals($segments, array());
    }

    /**
     * @group Plugins
     */
    public function test_UpdateSegment()
    {
        $name = 'name"';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $nameSegment1 = 'hello';
        $idSegment1 = API::getInstance()->add($nameSegment1, 'searches==0', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $idSegment2 = API::getInstance()->add($name, $definition, $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);

        $updatedSegment = array(
            'idsegment' => $idSegment2,
            'name' =>   'NEW name',
            'definition' =>  'searches==0',
            'enable_only_idsite' => '0',
            'enable_all_users' => '0',
            'auto_archive' => '0',
            'ts_last_edit' => Date::now()->getDatetime(),
            'ts_created' => Date::now()->getDatetime(),
            'login' => Piwik::getCurrentUserLogin(),
            'deleted' => '0',
        );
        API::getInstance()->update($idSegment2,
            $updatedSegment['name'],
            $updatedSegment['definition'],
            $updatedSegment['enable_only_idsite'],
            $updatedSegment['auto_archive'],
            $updatedSegment['enable_all_users']
        );

        $newSegment = API::getInstance()->get($idSegment2);

        // avoid test failures for when ts_created/ts_last_edit are different by between 1/2 secs
        $this->removeSecondsFromSegmentInfo($updatedSegment);
        $this->removeSecondsFromSegmentInfo($newSegment);

        $this->assertEquals($newSegment, $updatedSegment);

        // Check the other segmenet was not updated
        $newSegment = API::getInstance()->get($idSegment1);
        $this->assertEquals($newSegment['name'], $nameSegment1);
    }

    /**
     * @group Plugins
     */
    public function test_deleteSegment()
    {
        $idSegment1 = API::getInstance()->add('name 1', 'searches==0', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $idSegment2 = API::getInstance()->add('name 2', 'searches>1,visitIp!=127.0.0.1', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);

        $deleted = API::getInstance()->delete($idSegment2);
        $this->assertTrue($deleted);
        try {
            API::getInstance()->get($idSegment2);
            $this->fail("getting deleted segment should have failed");
        } catch(Exception $e) {
            // expected
        }

        // and this should work
        API::getInstance()->get($idSegment1);
    }

    private function removeSecondsFromSegmentInfo(&$segmentInfo)
    {
        $timestampProperties = array('ts_last_edit', 'ts_created');
        foreach ($timestampProperties as $propertyName) {
            if (isset($segmentInfo[$propertyName])) {
                $segmentInfo[$propertyName] = substr($segmentInfo[$propertyName], 0, strlen($segmentInfo[$propertyName]) - 2);
            }
        }
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
