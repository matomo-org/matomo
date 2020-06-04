<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\EventDispatcher;
use Piwik\Tracker\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\VisitorRecognizer;

/**
 * @group Core
 */
class VisitorRecognizerTest extends IntegrationTestCase
{
    /**
     * @var VisitorRecognizer
     */
    private $recognizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->recognizer = new VisitorRecognizer(true, 1800, 24000,
            new Model(), EventDispatcher::getInstance());
    }

    private function getVisitProperties()
    {
        $visit = new VisitProperties();
        $visit->setProperty('idvisit', '321');
        $visit->setProperty('idvisitor', Common::hex2bin('1234567890234567'));

        return $visit;
    }

    public function test_removeUnchangedValues_newVisit_shouldNotChangeAnything()
    {
        $visit = array(
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $result = $this->recognizer->removeUnchangedValues($visit);

        $this->assertEquals($visit, $result);
    }

    public function test_removeUnchangedValues_existingVisitWithDifferentValues_shouldNotChangeAnything()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234567'),
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $originalProperties = new VisitProperties(array(
            'visit_last_action_time' => '2020-05-05 04:05:05',
            'visit_total_time' => '40',
        ));
        $result = $this->recognizer->removeUnchangedValues($visit, $originalProperties);

        $this->assertEquals($visit, $result);
    }

    public function test_removeUnchangedValues_existingVisitWithSomeSameValues_shouldRemoveUnchangedValues()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234569'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
            'foo' => 'bar',
        );
        $originalVisit = new VisitProperties(array(
            'idvisitor' => Common::hex2bin('1234567890234567'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 04:05:05',
            'visit_total_time' => '50',
        ));
        $result = $this->recognizer->removeUnchangedValues($visit, $originalVisit);

        $this->assertEquals(array(
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'foo' => 'bar',
            'idvisitor' => Common::hex2bin('1234567890234569'),
        ), $result);
    }

    public function test_removeUnchangedValues_existingVisitWithAllSameValues_shouldRemoveEmptyArray()
    {
        $visit = array(
            'idvisitor' => Common::hex2bin('1234567890234569'),
            'user_id' => 'hello',
            'visit_last_action_time' => '2020-05-05 05:05:05',
            'visit_total_time' => '50',
        );
        $originalVisit = new VisitProperties($visit);
        $result = $this->recognizer->removeUnchangedValues($visit, $originalVisit);

        $this->assertEquals(array(), $result);
    }

}
