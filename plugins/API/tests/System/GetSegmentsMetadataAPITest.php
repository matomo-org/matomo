<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\System;

use Piwik\Cache;
use Piwik\API\Request;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Plugins\CoreHome\Columns\VisitorId;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class GetSegmentsMetadataAPITest extends SystemTestCase
{
    public function test_it_contains_visitid_by_default()
    {
        $request = new Request(
            'method=API.getSegmentsMetadata'
            . '&filter_limit=-1'
            . '&_hideImplementationData=0'
            . '&format=json'
            . '&module=API'
        );

        $response = json_decode($request->process(), true);

        $contains = false;

        foreach ($response as $segment) {
            if ($segment['segment'] === (new VisitorId())->getSegmentName()) {
                $contains = true;
                break;
            }
        }

        $this->assertTrue($contains);
    }

    public function test_it_does_not_contain_visitid_if_profile_disabled()
    {
        Cache::flushAll();

        $systemSettings = new SystemSettings();
        $systemSettings->disableVisitorProfile->setValue(1);
        $systemSettings->save();

        $request = new Request(
            'method=API.getSegmentsMetadata'
            . '&filter_limit=-1'
            . '&_hideImplementationData=0'
            . '&format=json'
            . '&module=API'
        );

        $response = json_decode($request->process(), true);

        $contains = false;

        foreach ($response as $segment) {
            if ($segment['segment'] === (new VisitorId())->getSegmentName()) {
                $contains = true;
                break;
            }
        }

        $this->assertFalse($contains);
    }
}
