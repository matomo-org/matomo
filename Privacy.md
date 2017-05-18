#Privacy 
This is a plain English summary of all of the components within Piwik which may affect your privacy in some way. Please keep in mind that if you use third party Themes or Apps with Piwik, there may be additional things not listed here.
##Privacy for Piwik admins and website owners
As admins is the Piwik admin and any other user acessing Piwik.In this section, we document how you can configure your Piwik service to better protect your privacy.
###Automatic update check
When a new session is started, Piwik check if the current version of Piwik is the latest version of Piwik. If an update is available, a notification appears inside Piwik to let you know.
This service can be disabled at any time. By setting "Auto update" feature enable_auto_update = 0 " in your configuration file in config/config.ini.php.
### Keep your Piwik server URL private
By default, the Piwik Javascript code on all tracked websites contains the Piwik server URL. In some cases you might want to hide this Piwik URL completely while still tracking all websites in your Piwik instance. It is possible to do by modifying your Piwik Javascript code, and point it to a “proxy piwik.php script” instead of your Piwik server URL. 
[Steps] (http://piwik.org/faq/how-to/faq_132/)
### Keep your user details private
As the Piwik administrator, you may decide that giving access to real time & visitor log features are not necessary for your Piwik users.
To keep your user details private, disable the 'Live' plugin in Administration > Plugins.
##Privacy for users being tracked by Piwik
In this section, we document how to protect the privacy of users that are tracked by your Piwik analytics service.
###Cookies
A cookie is a string of information that a website stores on a visitor’s computer, and that the visitor’s browser provides to the website each time the visitor returns.
You can disable them in this [way](http://piwik.org/faq/general/faq_157/)

###Automatically anonymize visitor IPS
By default, Piwik stores the visitor IP address (ipv4 or ipv6 format) in the database for each new visitor. If your user has a static IP address this means his browsing history could be easily tracked across several days and even across websites tracked within the same Piwik server.
[Steps](http://piwik.org/docs/privacy/#step-1-automatically-anonymize-visitor-ips)
###Delete old visitors logs
Deleting old logs also has one other important advantage: it will free significant database space, which will, in turn, slightly increase performance!
[Steps](http://piwik.org/docs/privacy/#step-2-delete-old-visitors-logs)
###Include a Web Analytics Opt-Out Feature on Your Site
On your website, in your existing privacy policy page or in the ‘Legal’ page, you can actually add a way for your visitors to “opt-out” of being tracked by your Piwik server. By default, all of your website visitors are tracked, but if they opt-out by clicking on the iframe link, a cookie ‘piwik_ignore’ will be set. All visitors with a piwik_ignore cookie will not be tracked.
[Steps](http://piwik.org/docs/privacy/#step-3-include-a-web-analytics-opt-out-feature-on-your-site-using-an-iframe)
###Respect DoNotTrack preference
[Steps] (http://piwik.org/docs/privacy/#step-4-respect-donottrack-preference)
