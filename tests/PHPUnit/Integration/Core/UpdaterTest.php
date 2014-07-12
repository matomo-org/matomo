<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Core;

use DatabaseTestCase;
use Piwik\Updater;
use Piwik\Tests\Fixture;

/**
 * Class Core_UpdaterTest
 *
 * @group Core
 * @group Core_UpdaterTest
 */
class UpdaterTest extends DatabaseTestCase
{
    public function testUpdaterChecksCoreVersionAndDetectsUpdateFile()
    {
        $updater = new Updater();
        $updater->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/core/';
        $updater->recordComponentSuccessfullyUpdated('core', '0.1');
        $updater->addComponentToCheck('core', '0.3');
        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
        $this->assertEquals(1, count($componentsWithUpdateFile));
    }

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

    public function testUpdateWorksAfterPiwikIsAlreadyUpToDate()
    {
        $result = Fixture::updateDatabase($force = true);
        if ($result === false) {
            throw new \Exception("Failed to force update (nothing to update).");
        }
    }
}