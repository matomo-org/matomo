<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Unit;

use Piwik\Plugins\CoreUpdater\UpdaterException;

/**
 * @group CoreUpdater
 * @group UpdaterExceptionTest
 * @group Unit
 * @group Plugins
 */
class UpdaterExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testWrapsAPhpError()
    {
        $error = new \Error('Call to undefined method Foo::bar()');

        $exception = new UpdaterException($error, ['Installing the latest version']);

        self::assertSame('Call to undefined method Foo::bar()', $exception->getMessage());
        self::assertSame($error, $exception->getPrevious());
        self::assertSame(['Installing the latest version'], $exception->getUpdateLogMessages());
    }
}
