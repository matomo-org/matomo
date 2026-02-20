<?php

namespace Piwik\Plugins\Feedback\tests\Fixtures;

use Piwik\Date;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;
use Piwik\Settings\Storage\UserScopedSettingsStore;
use Piwik\Tests\Fixtures\UITestFixture;

class FeedbackQuestionBannerFixture extends UITestFixture
{
    public function setUp(): void
    {
        parent::setUp();
        $yesterday = Date::yesterday();
        (new UserScopedSettingsStore())->set('Feedback', 'superUserLogin', 'nextFeedbackReminder', $yesterday->toString('Y-m-d'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        PluginSettingsTable::removeAllUserSettingsForUser('superUserLogin');
    }
}
