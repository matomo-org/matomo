<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Translate;

abstract class BasePeriodTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        Translate::loadAllTranslations();
    }

    public function tearDown()
    {
        parent::tearDown();

        Translate::reset();
    }
}