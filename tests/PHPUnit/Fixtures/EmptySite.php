<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Container\StaticContainer;
use Piwik\DI;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site with no visits
 */
class EmptySite extends Fixture
{
    public $idSite = 1;
    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
    }
    public function tearDown(): void
    {
        // empty
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => DI::add([
                ['Template.siteWithoutData.additionalCta', DI::value(function (&$content) {
                    // lets a UI test check that a component a plugin adds to the CTA row is started
                    if (StaticContainer::get('test.vars.injectNoDataCtaVueEntry')) {
                        $content .= '<div vue-entry="CoreHome.ContentBlock" content-title="Injected CTA">'
                            . '<p class="injected-cta">Injected CTA</p></div>';
                    }
                })],
            ]),
        ];
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2021-01-01');
        }
    }
}
