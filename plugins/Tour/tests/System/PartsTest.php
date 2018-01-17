<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\tests\System;

use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\Tour\Engagement\Part1;
use Piwik\Plugins\Tour\Engagement\Parts;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group Part1Test
 * @group Plugins
 */
class PartsTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var Part1
     */
    private $part1;

    /**
     * @var Parts
     */
    private $parts;

    public function setUp()
    {
        parent::setUp();

        $this->part1 = new Part1(new DataFinder());
        $this->parts = new Parts($this->part1);
    }

    public function test_getCurrentStep_returnsFirstStep_whenNotCompletedYet()
    {
        $this->assertSame($this->part1, $this->parts->getCurrentPart());
    }

    public function test_getCurrentStep_returnsNullOnceAllPartsAreCompleted()
    {
        foreach ($this->part1->getSteps() as $step) {
            Part1::skipStep($step['key']);
        }
        $this->part1->clearCache();
        $this->assertNull($this->parts->getCurrentPart());
    }

    public function test_getAllParts()
    {
        $this->assertSame(array($this->part1), $this->parts->getAllParts());
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

Part1Test::$fixture = new SimpleFixtureTrackFewVisits();