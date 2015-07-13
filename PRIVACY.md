# Privacy 
This is a plain English summary of all of the components within Piwik which may affect your privacy in some way. Please keep in mind that if you use third party Themes or Apps with Piwik, there may be additional things not listed here.

## Privacy for users being tracked by Piwik
In this section, we document how to protect the privacy of users who are tracked by your Piwik analytics service.

### Anonymise visitor IP addresses
By default, Piwik stores the visitor IP address (ipv4 or ipv6 format) in the database for each new visitor. 
If a visitor has a static IP address this means her browsing history could be easily tracked across several days and even across websites tracked within the same Piwik server.
[How to anonymise IP addresses.](http://piwik.org/docs/privacy/#step-1-automatically-anonymize-visitor-ips)

### Delete old visitors logs
By default, Piwik keeps all the user data forever. To better respect the privacy of your users, it is recommended to purge old user tracking data. 
Piwik can be configured to automatically delete log data older than a specified number of months. 
[How to delete old visitors log data.](http://piwik.org/docs/privacy/#step-2-delete-old-visitors-logs)

### Include a tracking Opt-Out feature on your site
In your website, it is recommended to provide your visitors an easy way to “opt-out” of being tracked by your Piwik analytics server. 
By default all of your website visitors are tracked but if they click this opt-out link, a cookie `piwik_ignore` is set in their browser and future actions by this visitor will be ignored by Piwik. 
[How to include a tracking opt-out iframe.](http://piwik.org/docs/privacy/#step-3-include-a-web-analytics-opt-out-feature-on-your-site-using-an-iframe)

### Respect DoNotTrack preference
Do Not Track is a technology and policy proposal that enables users to opt out of tracking by websites they do not visit, including analytics services, advertising networks, and social platforms. 
By default, Piwik respects users preference and will not track visitors which have specified “I do not want to be tracked” in their web browsers. 
[How to check your Piwik respects DoNotTrack.] (http://piwik.org/docs/privacy/#step-4-respect-donottrack-preference)

### Disable tracking cookies
A cookie is a string of information that a website stores on a visitor’s computer, and that the visitor’s browser provides to the website each time the visitor returns.
[How to disable tracking cookies.](http://piwik.org/faq/general/faq_157/)

### Keep your visitors details private
By default, any user who has at least `view` permission to Piwik can view detailed information for all users tracked in Piwik (such as IP address, visitor ID, other personally identifiable information, 
details of all past visits and actions, etc.). As the Piwik administrator, you may decide that your users do not need access to these detailed features. 
To disable the Visitor Log and Visitor Profile and Real time visitors feature, deactivate the `Live` plugin in Administration > Plugins.

## Privacy for Piwik admins and website owners
In this section we document how an administrator of a Piwik analytics service can better protect their privacy.

### Keep your Piwik server URL private
By default, the Piwik Javascript code on all tracked websites contains the Piwik server URL. In some cases you might want to hide this Piwik URL completely while still tracking all websites in your Piwik instance. 
To hide your Piwik server URL, you can modify the Javascript Tracking code and point it to a proxy piwik.php script instead of your Piwik server URL. 
[How to keep Piwik server URL private.](http://piwik.org/faq/how-to/faq_132/)

### Automatic update check
From time to time, Piwik calls `api.piwik.org` if the current version of Piwik is the latest version of Piwik. If an update is available, a notification is displayed and lets you upgrade Piwik.
To disable the update check, deactivate the "Automatic update" feature by setting `enable_auto_update = 0` in your configuration file `config/config.ini.php`.

