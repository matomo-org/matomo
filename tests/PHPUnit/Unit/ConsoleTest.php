<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Console;
use Piwik\Version;

/**
 * @group Console
 */
class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testIsApplicationNameAndVersionCorrect()
    {
        $console = new Console();

        $this->assertEquals('Matomo', $console->getName());
        $this->assertEquals(Version::VERSION, $console->getVersion());
    }
}
