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
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Period\Factory;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveTest extends IntegrationTestCase
{
    public function test_pluginSpecificArchiveUsed_EvenIfAllArchiveExists_IfThereAreNoDataInAllArchive()
    {
        $idSite = Fixture::createWebsite('2014-05-06');

        // insert all plugin archive
        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive1metric', 1);
        $archiveWriter->insertRecord('ExamplePlugin_archive2metric', 5);
        $archiveWriter->finalizeArchive();

        // insert single plugin archive
        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $params->setRequestedPlugin('ExamplePlugin');
        $params->onlyArchiveRequestedPlugin();
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive2metric', 2);
        $archiveWriter->insertRecord('ExamplePlugin_archive3metric', 3);
        $archiveWriter->finalizeArchive();

        // insert single plugin archive
        $params = new Parameters(new Site($idSite), Factory::build('day', '2014-05-07'), new Segment('', [$idSite]));
        $params->setRequestedPlugin('ExamplePlugin');
        $params->onlyArchiveRequestedPlugin();
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('ExamplePlugin_archive3metric', 7);
        $archiveWriter->finalizeArchive();

        $archive = Archive::build($idSite, 'day', '2014-05-07');
        $metrics = $archive->getNumeric(['ExamplePlugin_archive1metric', 'ExamplePlugin_archive2metric', 'ExamplePlugin_archive3metric']);

        $expected = [
            'ExamplePlugin_archive1metric' => 1,
            'ExamplePlugin_archive2metric' => 2,
            'ExamplePlugin_archive3metric' => 7,
        ];
        $this->assertEquals($expected, $metrics);
    }


}