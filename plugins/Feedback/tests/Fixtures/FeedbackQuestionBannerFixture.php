<?php

namespace Piwik\Plugins\Feedback\tests\Fixtures;

use Piwik\Date;
use Piwik\Option;
use Piwik\Tests\Fixtures\UITestFixture;

class FeedbackQuestionBannerFixture extends UITestFixture
{
    public function setUp(): void
    {
        parent::setUp();
        $yesterday = Date::yesterday();
        Option::set('Feedback.nextFeedbackReminder.superUserLogin', $yesterday->toString('Y-m-d'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Option::delete('Feedback.nextFeedbackReminder.superUserLogin');
    }
}
