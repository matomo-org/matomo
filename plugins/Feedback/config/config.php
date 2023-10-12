<?php

use Piwik\Url;

return [
    'popularHelpTopics' => [
        ['title' => 'Feedback_NotTrackingVisits', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/troubleshooting/faq_58/')],
        ['title' => 'Feedback_TrackMultipleSites', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/troubleshooting/faq_104/')],
        ['title' => 'Feedback_HowToMigrateFromGA', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/faq_102/')],
        ['title' => 'Feedback_HowToDefineAndTrackGoals', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/tracking-goals-web-analytics/')],
        ['title' => 'Feedback_HowToGetStartedWithMtm', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/guide/tag-manager/getting-started-with-tag-manager/')],
        ['title' => 'Feedback_HowToMigrateFromGtm', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/tag-manager/migrating-from-google-tag-manager/')],
        ['title' => 'Feedback_HowToTrackEcommerce', 'url' => Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/ecommerce-analytics/')],
    ]
];
