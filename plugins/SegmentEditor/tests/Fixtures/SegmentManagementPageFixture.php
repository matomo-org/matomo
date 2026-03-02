<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\SegmentEditor\tests\Fixtures;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Option;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;

class SegmentManagementPageFixture extends OneVisitorTwoVisits
{
    public const GLOBAL_SEGMENT_NAME = 'UI Test Global Segment';
    public const GLOBAL_SEGMENT_DEFINITION = 'countryCode==fr';

    public const SITE_SEGMENT_NAME = 'UI Test Site Segment';
    public const SITE_SEGMENT_DEFINITION = 'visitCount>=1';

    public const XSS_SEGMENT_NAME = '<script>alert("testsegment");</script>';
    public const XSS_SEGMENT_DEFINITION = 'browserCode==FF';

    public const REALTIME_SEGMENT_NAME = 'UI Test Realtime Segment';
    public const REALTIME_SEGMENT_DEFINITION = 'browserCode==FF';

    public const COMPLEX_DASHBOARD_SEGMENT_NAME = 'UI Test Complex Dashboard Segment';
    public const COMPLEX_DASHBOARD_SEGMENT_DEFINITION = 'browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3,browserName==s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3;browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3';

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpSegments();
    }

    private function setUpSegments(): void
    {
        $originalEnableBrowserArchivingTriggering = Config::getInstance()->General['enable_browser_archiving_triggering'];
        $originalBrowserArchivingDisabledEnforce = Config::getInstance()->General['browser_archiving_disabled_enforce'];
        $originalBrowserTriggerOption = Option::get(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);

        try {
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;
            Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
            Rules::setBrowserTriggerArchiving(false);

            $api = SegmentEditorAPI::getInstance();
            $api->add(self::GLOBAL_SEGMENT_NAME, self::GLOBAL_SEGMENT_DEFINITION, null, true, true);
            $api->add(self::SITE_SEGMENT_NAME, self::SITE_SEGMENT_DEFINITION, $this->idSite, true, true);
            $api->add(self::XSS_SEGMENT_NAME, self::XSS_SEGMENT_DEFINITION, $this->idSite, true, true);
            $api->add(self::REALTIME_SEGMENT_NAME, self::REALTIME_SEGMENT_DEFINITION, $this->idSite, false, true);
            $api->add(self::COMPLEX_DASHBOARD_SEGMENT_NAME, self::COMPLEX_DASHBOARD_SEGMENT_DEFINITION, $this->idSite, true, true);
        } finally {
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = $originalBrowserArchivingDisabledEnforce;
            Config::getInstance()->General['enable_browser_archiving_triggering'] = $originalEnableBrowserArchivingTriggering;

            if ($originalBrowserTriggerOption === false) {
                Option::delete(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);
            } else {
                Rules::setBrowserTriggerArchiving((bool) $originalBrowserTriggerOption);
            }
        }
    }
}
