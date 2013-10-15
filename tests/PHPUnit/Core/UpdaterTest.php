<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Updater;

class UpdaterTest extends DatabaseTestCase
{
    /**
     * @group Core
     */
    public function testUpdaterChecksCoreVersionAndDetectsUpdateFile()
    {
        $updater = new Updater();
        $updater->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/core/';
        $updater->recordComponentSuccessfullyUpdated('core', '0.1');
        $updater->addComponentToCheck('core', '0.3');
        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
        $this->assertEquals(1, count($componentsWithUpdateFile));
    }

    /**
     * @group Core
     */
    public function testUpdaterChecksGivenPluginVersionAndDetectsMultipleUpdateFileInOrder()
    {
        $updater = new Updater();
        $updater->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/%s/';
        $updater->recordComponentSuccessfullyUpdated('testpluginUpdates', '0.1beta');
        $updater->addComponentToCheck('testpluginUpdates', '0.1');
        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();

        $this->assertEquals(1, count($componentsWithUpdateFile));
        $updateFiles = $componentsWithUpdateFile['testpluginUpdates'];
        $this->assertEquals(2, count($updateFiles));

        $path = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/testpluginUpdates/';
        $expectedInOrder = array(
            $path . '0.1beta2.php' => '0.1beta2',
            $path . '0.1.php'      => '0.1'
        );
        $this->assertEquals($expectedInOrder, array_map("basename", $updateFiles));
    }

    /**
     * @group Core
     */
    public function testUpdaterChecksCoreAndPluginCheckThatCoreIsRanFirst()
    {
        $updater = new Updater();
        $updater->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/%s/';
        $updater->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/core/';

        $updater->recordComponentSuccessfullyUpdated('testpluginUpdates', '0.1beta');
        $updater->addComponentToCheck('testpluginUpdates', '0.1');

        $updater->recordComponentSuccessfullyUpdated('core', '0.1');
        $updater->addComponentToCheck('core', '0.3');

        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
        $this->assertEquals(2, count($componentsWithUpdateFile));
        reset($componentsWithUpdateFile);
        $this->assertEquals('core', key($componentsWithUpdateFile));
    }
}
