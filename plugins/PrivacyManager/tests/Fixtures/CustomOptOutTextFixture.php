<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Tests\Framework\Fixture;

class CustomOptOutTextFixture extends Fixture
{
    public function setUp()
    {
        parent::setUp();

        $settings = new SystemSettings();
        $settings->defaultOptedOutText->setValue("opted out\nmore\n\ntext");
        $settings->defaultOptedOutText->save();
        $settings->defaultOptedInText->setValue("opted in\n\npar 2\n\n par3\nstillpar3");
        $settings->defaultOptedInText->save();
    }
}
