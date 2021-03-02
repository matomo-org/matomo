<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Integration;

use Piwik\Archive;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-05-06');
    }

    public function test_pluginSpecificArchiveUsed_EvenIfAllArchiveExists_IfThereAreNoDataInAllArchive()
    {
        $idSite = 1;

        // insert all plugin archive
        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive1metric', 1);
        $archiveWriter->insertRecord('ExamplePlugin_archive2metric', 5);
        $archiveWriter->finalizeArchive();

        sleep(1);

        // insert single plugin archive
        $_GET['pluginOnly'] = 1;
        $_GET['trigger'] = 'archivephp';

        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $params->setRequestedPlugin('ExamplePlugin');
        $params->onlyArchiveRequestedPlugin();
        $params->setIsPartialArchive(true);
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive2metric', 2);
        $archiveWriter->insertRecord('ExamplePlugin_archive3metric', 3);
        $archiveWriter->finalizeArchive();

        sleep(1);

        // insert single plugin archive
        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $params->setRequestedPlugin('ExamplePlugin');
        $params->onlyArchiveRequestedPlugin();
        $params->setIsPartialArchive(true);
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive3metric', 7);
        $archiveWriter->finalizeArchive();

        unset($_GET['trigger']);
        unset($_GET['pluginOnly']);

        $archive = Archive::build($idSite, 'day', '2014-05-07');
        $metrics = $archive->getNumeric(['ExamplePlugin_archive1metric', 'ExamplePlugin_archive2metric', 'ExamplePlugin_archive3metric']);

        $expected = [
            'ExamplePlugin_archive1metric' => 1,
            'ExamplePlugin_archive2metric' => 2,
            'ExamplePlugin_archive3metric' => 7,
        ];

        $this->assertEquals($expected, $metrics);
    }

    public function test_pluginSpecificArchiveUsed_EvenIfAllArchiveExists_IfThereAreNoDataInAllArchive_WithBrowserArchivingDisabled()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'archiving_range_force_on_browser_request', 0);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['archiving_range_force_on_browser_request'] = 0;

        $this->assertTrue(Rules::isArchivingDisabledFor([1], new Segment('', [1]), 'day'));

        $this->test_pluginSpecificArchiveUsed_EvenIfAllArchiveExists_IfThereAreNoDataInAllArchive();
    }
}