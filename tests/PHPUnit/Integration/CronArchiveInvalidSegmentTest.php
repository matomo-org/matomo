<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentAPI;

/**
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveInvalidSegmentTest extends IntegrationTestCase
{
    /** @var FakeLogger  */
    private $logger;

    public function setUp(): void
    {
        \Piwik\Tests\Framework\Mock\FakeCliMulti::$specifiedResults = array(
          '/method=API.get/' => json_encode(array(array('nb_visits' => 1)))
        );

        Fixture::createWebsite('2014-12-12 00:01:02');

        Manager::getInstance()->activatePlugin('UserLanguage');

        Rules::setBrowserTriggerArchiving(false);
        SegmentAPI::getInstance()->add('languageSegment', 'languageCode==fr', 1, true, true);

        $tracker = Fixture::getTracker(1, '2019-12-12 02:03:00');
        $tracker->setUrl('http://someurl.com');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        $tracker->setForceVisitDateTime('2019-12-11 03:04:05');
        $tracker->setUrl('http://someurl.com/2');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg2'));

        $tracker->setForceVisitDateTime('2019-12-10 03:04:05');
        $tracker->setUrl('http://someurl.com/3');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg3'));

        $tracker->setForceVisitDateTime('2019-12-02 03:04:05');
        $tracker->setUrl('http://someurl.com/4');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg4'));

        $this->logger = new FakeLogger();
    }

    public function test_output_invalidSegment()
    {
        $archiver = new CronArchive($this->logger);

        $archiver->init();
        $archiver->run();

        $this->assertStringNotContainsStringIgnoringCase('Got invalid response from API request', $this->logger->output);
        $this->assertStringContainsString("Skipped Archiving website id 1", $this->logger->output);
        $this->assertStringContainsString('no error', $this->logger->output);
    }

    public function test_output_invalidSegment_whenPluginIsNotActive()
    {
        Manager::getInstance()->deactivatePlugin('UserLanguage');

        $archiver = new CronArchive($this->logger);

        $archiver->init();
        $archiver->run();

        $this->assertStringNotContainsStringIgnoringCase('Got invalid response from API request', $this->logger->output);
        $this->assertStringContainsString("Segment 'languageCode==fr' is not a supported segment", $this->logger->output);
        $this->assertStringContainsString('no error', $this->logger->output);
    }

    public function provideContainerConfig()
    {
        Date::$now = strtotime('2020-02-03 04:05:06');

        return array(
            'observers.global' => \Piwik\DI::add([
                ['API.CoreAdminHome.archiveReports', \Piwik\DI::value(function (&$result) {
                    Manager::getInstance()->deactivatePlugin('UserLanguage');
                })]
            ]),
        );
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
