<?php

namespace Piwik\Plugins\Feedback\tests\Fixtures;

use Piwik\Date;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\Tests\Fixtures\UITestFixture;

class FeedbackQuestionBannerFixture extends UITestFixture
{
    public function setUp(): void
    {
        parent::setUp();
        $yesterday = Date::yesterday();
        (new UserScopedSettingsAccessManager())->set('Feedback', 'superUserLogin', 'nextFeedbackReminder', $yesterday->toString('Y-m-d'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        PluginSettingsTable::removeAllUserSettingsForUser('superUserLogin');
    }
}
