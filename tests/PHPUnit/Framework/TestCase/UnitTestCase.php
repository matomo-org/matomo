<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\Mock\File;


/**
 * Base class for Unit tests.
 *
 * @since 2.10.0
 */
abstract class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        File::reset();
        EventDispatcher::getInstance()->clearAllObservers();
    }

    public function tearDown()
    {
        parent::tearDown();
        File::reset();
    }
}
