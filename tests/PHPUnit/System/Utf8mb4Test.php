<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Fixtures\Utf8mb4;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Tests for tracking parameters containing 4 byte UTF8 chars.
 *
 * @group Core
 * @group Utf8mb4Test
 */
class Utf8mb4Test extends SystemTestCase
{
    /** @var Utf8mb4 */
    public static $fixture = null; // initialized below class definition

    public function testApi()
    {
        $this->runApiTests(['Live.getLastVisitsDetails'], [
            'idSite'            => self::$fixture->idSite,
            'date'              => '2010-01-04',
            'period'            => 'year'
        ]);
    }


    public static function getOutputPrefix()
    {
        return 'Utf8mb4';
    }
}

Utf8mb4Test::$fixture = new Utf8mb4();