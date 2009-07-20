; <?php exit; ?> DO NOT REMOVE THIS LINE
; If you want to change some of these default values, the best practise is to override 
; them in your configuration file in config/config.ini.php. If you directly edit this file,
; you risk losing your changes when you upgrade Piwik. 
; For example if you want to override enable_browser_archiving_triggering, 
; edit config/config.ini.php and add the following:
; [General]
; enable_browser_archiving_triggering = 0

[superuser]
login			= root
password		= 

[database]
host			= 
username		= 
password		= 
dbname			= 
tables_prefix	= 
port			= 3306
adapter			= PDO_MYSQL ; PDO_MYSQL or MYSQLI
;unix_socket		=

[database_tests]
host 			= localhost
username 		= root
password 		= 
dbname			= piwik_tests
tables_prefix	= piwiktests_
port			= 3306
adapter 		= PDO_MYSQL
;unix_socket		=
 
[Debug]
; if set to 1, the archiving process will always be triggered, even if the archive has already been computed
; this is useful when making changes to the archiving code so we can force the archiving process
always_archive_data_period = 0;
always_archive_data_day = 0;

; if set to 1, all the SQL queries will be recorded by the profiler 
; and a profiling summary will be printed at the end of the request
enable_sql_profiler = 0

[General]
; Time in seconds after which an archive will be computed again. 
; This setting is used only for today's statistics.
; Defaults to 10 seconds so that by default, Piwik provides real time reporting.
time_before_today_archive_considered_outdated = 10

; When loading piwik interface, we redirect the user to 'yesterday' statistics by default
; Possible values: yesterday, today, or any YYYY-MM-DD
default_day = yesterday
; Possible values: day, week, month, year
default_period = day

; When loading piwik interface, Piwik will load by default the CoreHome module
; You can override the setting to force the user to login. 
; This is useful when you have some websites view "anonymous" access but you want to 
; force users to login instead of viewing the first anonymous website available 
default_module_login = 0

; When loading the piwik interface in the browser (as opposed to from the PHP-CLI client)
; should we launch the archiving process if the archives have not yet been processed?
; You want to set it to 0 when triggering the archiving is done through a crontab, 
; so that your users do not trigger archiving in their browser when this is not expected
enable_browser_archiving_triggering = 1

; character used to automatically create categories in the "Action" "Downloads" reports
; for example a URL like "example.com/blog/development/first-post" will create 
; the page first-post in the subcategory development which belongs to the blog category
action_category_delimiter = /

; currency used by default when reporting money in Piwik
; the trailing space is required for php 5.2.x vs 5.3 compatibility
default_currency = "$ "

; if you want all your users to use Piwik in only one language, disable the LanguagesManager
; plugin, and set this default_language (users won't see the language drop down) 
default_language = en

; default number of elements in the datatable
datatable_default_limit = 10

; default number of rows returned in API responses
API_datatable_default_limit = 50

; if set to 1, the website selector will be displayed in the Piwik UI
; if your Piwik installation has thousands of websites, you may disable the website selector
; as it slows down the loading of the Piwik UI by setting this value to 0
show_website_selector_in_user_interface = 1

; PHP minimum required version (minimum requirement known to date = ->newInstanceArgs)
minimum_php_version = 5.1.3

; MySQL minimum required version
minimum_mysql_version = 4.1

; Minimum adviced memory limit in php.ini file (see memory_limit value)
minimum_memory_limit = 128

; login cookie name
login_cookie_name = piwik_auth

; login cookie expiration (30 days)
login_cookie_expire = 2592000

; email address that appears as a Sender in the password recovery email
; if specified, {DOMAIN} will be replaced by the current Piwik domain
login_password_recovery_email_address = "password-recovery@{DOMAIN}"

; name that appears as a Sender in the password recovery email
login_password_recovery_email_name = Piwik

; during archiving, Piwik will limit the number of results recorded, for performance reasons
; maximum number of rows for any of the Referers tables (keywords, search engines, campaigns, etc.)
datatable_archiving_maximum_rows_referers = 500
; maximum number of rows for any of the Referers subtable (search engines by keyword, keyword by campaign, etc.)
datatable_archiving_maximum_rows_subtable_referers = 50

; maximum number of rows for any of the Actions tables (pages, downloads, outlinks)
datatable_archiving_maximum_rows_actions = 500
; maximum number of rows for pages in categories (sub pages, when clicking on the + for a page category)
datatable_archiving_maximum_rows_subtable_actions = 100

[Tracker]
; set to 0 if you want to stop tracking the visitors. Useful if you need to stop all the connections on the DB.
record_statistics			= 1

; this action name is used when the javascript variable piwik_action_name is not specified in the piwik javascript code, and when the URL has no path.
default_action_name 		= index

; length of a visit in seconds. If a visitor comes back on the website visit_standard_length seconds after his last page view, it will be recorded as a new visit  
visit_standard_length       = 1800

; visitors that stay on the website and view only one page will be considered staying 10 seconds
default_time_one_page_visit = 10

; if set to 0, any goal conversion will be credited to the last more recent non empty referer. 
; when set to 1, the first ever referer used to reach the website will be used
use_first_referer_to_determine_goal_referer = 0

; if set to 1, Piwik will try to match visitors without cookie to a previous visitor that has the same
; configuration: OS, browser, resolution, IP, etc. This heuristic adds an extra SQL query for each page view without cookie. 
; it is advised to set it to 1 for more accurate detection of unique visitors.
; However when most users have the same IP, and the same configuration, it is advised to set it to 0
enable_detect_unique_visitor_using_settings = 1

; if set to 1, Piwik attempts a "best guess" at the visitor's country of
; origin when the preferred language tag omits region information.
; The mapping is defined in core/DataFiles/LanguageToCountry.php,
enable_language_to_country_guess = 1

; name of the cookie used to store the visitor information
cookie_name	= piwik_visitor

; by default, the Piwik tracking cookie expires in 2 years
cookie_expire = 63072000

; variable name to track any campaign, for example CPC campaign
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC' then it will be counted as a campaign referer named 'Adwords-CPC'
campaign_var_name			= piwik_campaign

; variable name to track any campaign keyword
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC&piwik_kwd=My killer keyword' then it will be counted as a campaign referer named 'Adwords-CPC' with the keyword 'My killer keyword'
campaign_keyword_var_name	= piwik_kwd

; variable name used to specify a download link
; Example: '/piwik.php?idsite=1&download=http://piwik.org/piwik.zip' will redirect to 'http://piwik.org/piwik.zip'
download_url_var_name 		= download

; variable name used to specify a link to an external website
; Example: '/piwik.php?idsite=1&link=http://piwik.org/' will redirect to 'http://piwik.org/'
outlink_url_var_name		= link

; variable name used to specify that the user should be redirected to the clicked linked, or the downloaded file
; eg. http://yourwebsite.org/piwik/piwik.php?idsite=1&link=http://example.org&redirect=1
;     will count the outlink in piwik and redirect the user to http://example.org
; eg. http://yourwebsite.org/piwik/piwik.php?idsite=1&download=http://yourwebsite.org/download.pdf&redirect=1
;     will count the download in piwik and redirect the user to http://yourwebsite.org/download.pdf
; NOTE: it is recommended to rely on the automatic outlink and download tracking (more information on http://piwik.org/docs/javascript-tracking/). 
;       rather than adding a depending on Piwik for your website to function properly. 
;       However this feature is useful to some users as it gives a simple and reliable way of counting clicks, that you can then query using the Piwik API.
outlink_redirect_var_name	= redirect
download_redirect_var_name	= redirect

[log]
;possible values for log: screen, database, file
; normal messages
logger_message[]		= screen
logger_error[]			= screen
logger_exception[]		= screen

; if configured to log in files, log files will be created in this path
; eg. if the value is tmp/logs files will be created in /path/to/piwik/tmp/logs/
logger_file_path		= tmp/logs

; all calls to the API (method name, parameters, execution time, caller IP, etc.)
; disabled by default as it can cause serious overhead and should only be used wisely
;logger_api_call[]		= file

[smarty]
; the list of directories in which to look for templates
template_dir[]	= plugins
template_dir[]	= themes/default
template_dir[]	= themes

plugins_dir[]	= core/SmartyPlugins
plugins_dir[] 	= libs/Smarty/plugins

compile_dir		= tmp/templates_c
cache_dir		= tmp/cache

; error reporting inside Smarty
error_reporting = E_ALL|E_NOTICE

; allow smarty debugging using {debug}
debugging		= 1

[Plugins]
Plugins[] 		= CorePluginsAdmin
Plugins[] 		= CoreAdminHome
Plugins[] 		= CoreHome
Plugins[] 		= API
Plugins[] 		= Widgetize
Plugins[] 		= LanguagesManager
Plugins[] 		= Actions
Plugins[] 		= Dashboard
Plugins[] 		= Referers
Plugins[] 		= UserSettings

Plugins[] 		= UserCountry
Plugins[] 		= VisitsSummary
Plugins[] 		= VisitFrequency
Plugins[] 		= VisitTime
Plugins[] 		= VisitorInterest
Plugins[] 		= ExampleAPI
Plugins[] 		= ExamplePlugin
Plugins[]		= ExampleRssWidget
Plugins[] 		= ExampleFeedburner
Plugins[] 		= ExampleRssWidget
Plugins[] 		= Provider
Plugins[]		= Feedback

Plugins[] 		= Login
Plugins[] 		= UsersManager
Plugins[] 		= SitesManager
Plugins[] 		= Installation

[PluginsInstalled]
PluginsInstalled[] = Login
PluginsInstalled[] = CoreAdminHome
PluginsInstalled[] = UsersManager
PluginsInstalled[] = SitesManager
PluginsInstalled[] = Installation

[Plugins_Tracker]
Plugins_Tracker[] = Provider
