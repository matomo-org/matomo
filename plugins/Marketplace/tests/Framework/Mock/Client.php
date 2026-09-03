<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Framework\Mock;

use Matomo\Cache\Backend\NullCache;
use Matomo\Cache\Lazy;
use Piwik\Log\NullLogger;

class Client
{
    /**
     * @param Lazy|null $cache Defaults to a cache that retains nothing, so a test only sees the
     *                         requests it makes. Pass one backed by an ArrayCache to test behaviour
     *                         that depends on a response actually being cached.
     */
    public static function build($service, ?Lazy $cache = null)
    {
        $environment = new Environment();
        return new \Piwik\Plugins\Marketplace\Api\Client(
            $service,
            $cache ?: new Lazy(new NullCache()),
            new NullLogger(),
            $environment
        );
    }
}
