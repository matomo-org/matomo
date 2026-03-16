<?php

namespace Piwik\Plugins\MobileMessaging\tests\Fixtures;

use Piwik\Settings\Storage\Factory;
use Piwik\Tests\Fixtures\EmptySite;

class InvalidProviderFixture extends EmptySite
{
    public function setUp(): void
    {
        parent::setUp();

        (new Factory())->getPluginStorage('MobileMessaging', '')->getBackend()->save([
            'Provider' => 'InValid',
            'APIKey' => [],
        ]);
    }
}
