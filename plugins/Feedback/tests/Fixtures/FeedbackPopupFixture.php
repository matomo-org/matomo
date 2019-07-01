<?php


namespace Piwik\Plugins\Feedback\tests\Fixtures;

use Piwik\Date;
use Piwik\Option;
use Piwik\Tests\Fixtures\UITestFixture;

class FeedbackPopupFixture extends UITestFixture
{
    public function setUp()
    {
        parent::setUp();
        $yesterday = Date::yesterday();
        Option::set('Feedback.nextFeedbackReminder.superUserLogin', $yesterday->toString('Y-m-d'));
    }

    public function tearDown()
    {
        parent::tearDown();
        Option::delete('Feedback.nextFeedbackReminder.superUserLogin');
    }

}