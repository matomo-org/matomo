# Matomo Tracking Spam Prevention Plugin

[![Build Status](https://github.com/matomo-org/plugin-TrackingSpamPrevention/actions/workflows/matomo-tests.yml/badge.svg?branch=4.x-dev)](https://github.com/matomo-org/plugin-TrackingSpamPrevention/actions/workflows/matomo-tests.yml)

## Description

Ever noticed tracking requests that look unnatural or originated from locations you wouldn't expect to get visits from?

These tracking requests may be caused by spammers or bots and make your data less accurate. The only thing that is worse than having no data is inaccurate data.

This plugin offers various options to fight spam and bots, so you can rely on your data:

### 1. Block requests from cloud providers

When enabled, this plugin will automatically detect IP addresses used by popular cloud providers like AWS, Azure, Digital Ocean, Google Cloud and Oracle Cloud.

When a tracking request matches such an IP address, then the tracking request will be blocked. Additionally, some Cloud providers like Alibaba Cloud may be detected using the geolocation database (requires eg DB-IP City DB).

If you are only tracking using the JavaScript tracker then this should be a safe feature to enable as tracking requests from humans would not originate from these clouds.

If you are sending tracking requests from a cloud server, then you can also configure IP addresses that are always allowed, so you can still use this feature.

### 2. Block requests from headless browsers

When enabled, this plugin detects the most popular headless browsers and block tracking requests that originate from a headless browser.

Headless browsers are browsers without a user interface and are mostly used for automation. Regular visitors would not use such a browser. It can block additional bots and spam requests that otherwise would not be detected.

It cannot detect a headless browser when the user agent is customised. Often, we can detect them though.

### 3. Restrict number of actions per visit

When enabled, you can configure how many actions a visit should max have. 

Most sites never have more than around 100 to 300 actions within one visit under normal circumstances. In many cases it might therefore be safe to assume that if someone has caused more actions than the configured amount of actions, it might be actually tracking spam or a bot or something else like non-human activity is causing these actions. 

Matomo will in this case stop recording further actions for that visit to have less inaccurate data and to reduce server load. The IP address of this visit will then be blocked for up to 24 hours.

You can also be notified by email when an IP address is banned because a visit had too many actions. 

### 4. Exclude countries

This feature lets you configure to only accept tracking requests for visitors from specific countries. For example, if you have a German website, then it might be unexpected to have any legit visitors from a country outside of Europe meaning a visitor is likely a spammer or a bot in this case. By only tracking visitors from certain countries you can easily avoid a lot of potential spam and bots plus you might also avoid needing to be compliant with certain privacy laws.

### 5. Referrer spam

This feature is not part of this plugin but part of Matomo core.
Matomo maintains a [list of spam referrers](https://matomo.org/blog/2015/05/stopping-referrer-spam/) and Matomo will block tracking requests when such a referrer is detected.

### Recommended other features

You might also want to [configure to track only URLs that belong to your website](https://matomo.org/faq/how-to/faq_21077/). This way any URL that does not belong to your website will not be tracked. This feature is part of Matomo core.

### Privacy

The plugin does not send any information of your visitors to another server. If you enable the "block requests from cloud providers" feature, then your server will download up to date IP ranges from cloud providers directly and store the information in your database.
