<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\System;

use Piwik\Plugins\CustomPiwikJs\TrackerUpdater;
use Piwik\Plugins\CustomPiwikJs\TrackingCode\PiwikJsManipulator;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CustomPiwikJs
 * @group PiwikJsContentTest
 * @group PiwikJsContent
 * @group Plugins
 */
class PiwikJsContentTest extends SystemTestCase
{
    public function test_piwikJsAndPiwikMinJsMustHaveSameContent()
    {
        $piwikMin = PIWIK_DOCUMENT_ROOT . TrackerUpdater::ORIGINAL_PIWIK_JS;
        $piwikJs = PIWIK_DOCUMENT_ROOT . TrackerUpdater::TARGET_PIWIK_JS;

        $this->assertSame(file_get_contents($piwikMin), file_get_contents($piwikJs));
    }

    public function test_piwikJsContainsHook()
    {
        $piwikMin = PIWIK_DOCUMENT_ROOT . '/js/piwik.min.js';
        $content  = file_get_contents($piwikMin);

        $this->assertContains(PiwikJsManipulator::HOOK, $content);
    }

}