<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class SegmentEditorTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        Piwik_PluginsManager::getInstance()->loadPlugin('SegmentEditor');
        Piwik_PluginsManager::getInstance()->installLoadedPlugins();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a super user by default
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superusertest';
        Zend_Registry::set('access', $pseudoMockAccess);

        Piwik_SitesManager_API::getInstance()->addSite('test', 'http://example.org');
    }

    public function testAddInvalidSegment_ShouldThrow()
    {
        try {
            Piwik_SegmentEditor_API::getInstance()->add('name', 'test==test2');
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
        }
        try {
            Piwik_SegmentEditor_API::getInstance()->add('name', 'test');
            $this->fail("Exception not raised.");
        } catch (Exception $expected) {
        }
    }

    public function test_AddAndGet_SimpleSegment()
    {
        $name = 'name';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $idSegment = Piwik_SegmentEditor_API::getInstance()->add($name, $definition);
        $this->assertEquals($idSegment, 1);
        $segment = Piwik_SegmentEditor_API::getInstance()->get($idSegment);
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

    public function test_AddAndGet_AnotherSegment()
    {
        $name = 'name';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $idSegment = Piwik_SegmentEditor_API::getInstance()->add($name, $definition, $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $this->assertEquals($idSegment, 1);

        // Testing get()
        $segment = Piwik_SegmentEditor_API::getInstance()->get($idSegment);
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
        $segments = Piwik_SegmentEditor_API::getInstance()->getAll($idSite, $autoArchived = true);
        unset($segments[0]['ts_created']);
        $this->assertEquals($segments, array($expected));

        // There is no segment to process for a non existing site
        try {
            $segments = Piwik_SegmentEditor_API::getInstance()->getAll(33, $autoArchived = true);
            $this->fail();
        } catch(Exception $e) {
            // expected
        }

        // There is no segment to process across all sites
        $segments = Piwik_SegmentEditor_API::getInstance()->getAll($idSite = false, $autoArchived = true);
        $this->assertEquals($segments, array());
    }

    public function test_UpdateSegment()
    {
        $name = 'name"';
        $definition = 'searches>1,visitIp!=127.0.0.1';
        $nameSegment1 = 'hello';
        $idSegment1 = Piwik_SegmentEditor_API::getInstance()->add($nameSegment1, 'searches==0', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $idSegment2 = Piwik_SegmentEditor_API::getInstance()->add($name, $definition, $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);

        $updatedSegment = array(
            'idsegment' => $idSegment2,
            'name' =>   'NEW name',
            'definition' =>  'searches==0',
            'enable_only_idsite' => '0',
            'enable_all_users' => '0',
            'auto_archive' => '0',
            'ts_last_edit' => Piwik_Date::now()->getDatetime(),
            'ts_created' => Piwik_Date::now()->getDatetime(),
            'login' => Piwik::getCurrentUserLogin(),
            'deleted' => '0',
        );
        Piwik_SegmentEditor_API::getInstance()->update($idSegment2,
            $updatedSegment['name'],
            $updatedSegment['definition'],
            $updatedSegment['enable_only_idsite'],
            $updatedSegment['auto_archive'],
            $updatedSegment['enable_all_users']
        );

        $newSegment = Piwik_SegmentEditor_API::getInstance()->get($idSegment2);
        $this->assertEquals($newSegment, $updatedSegment);

        // Check the other segmenet was not updated
        $newSegment = Piwik_SegmentEditor_API::getInstance()->get($idSegment1);
        $this->assertEquals($newSegment['name'], $nameSegment1);
    }

    public function test_deleteSegment()
    {
        $idSegment1 = Piwik_SegmentEditor_API::getInstance()->add('name 1', 'searches==0', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);
        $idSegment2 = Piwik_SegmentEditor_API::getInstance()->add('name 2', 'searches>1,visitIp!=127.0.0.1', $idSite = 1, $autoArchive = 1, $enabledAllUsers = 1);

        $deleted = Piwik_SegmentEditor_API::getInstance()->delete($idSegment2);
        $this->assertTrue($deleted);
        try {
            Piwik_SegmentEditor_API::getInstance()->get($idSegment2);
            $this->fail("getting deleted segment should have failed");
        } catch(Exception $e) {
            // expected
        }

        // and this should work
        Piwik_SegmentEditor_API::getInstance()->get($idSegment1);
    }
}
