; <?php exit; ?> DO NOT REMOVE THIS LINE
; If you want to change some of these default values, the best practise is to override 
; them in your configuration file in config/config.ini.php. If you directly edit this file,
; you risk losing your changes when you upgrade Piwik. 
; For example if you want to override action_title_category_delimiter, 
; edit config/config.ini.php and add the following:
; [General]
; action_title_category_delimiter = "-"

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
adapter			= PDO_MYSQL
; if charset is set to utf8, Piwik will ensure that it is storing its data using UTF8 charset.
; it will add a sql query SET at each page view.
; Piwik should work correctly without this setting.  
;charset		= utf8

[database_tests]
host 			= localhost
username 		= root
password 		= 
dbname			= piwik_tests
tables_prefix	= piwiktests_
port			= 3306
adapter 		= PDO_MYSQL
 
[Debug]
; if set to 1, the archiving process will always be triggered, even if the archive has already been computed
; this is useful when making changes to the archiving code so we can force the archiving process
always_archive_data_period = 0;
always_archive_data_day = 0;

; if set to 1, all the SQL queries will be recorded by the profiler 
; and a profiling summary will be printed at the end of the request
enable_sql_profiler = 0

; if set to 1, a Piwik tag will be included in the Piwik UI footer and will track visits, pages, etc. to idsite = 1
; this is useful for Piwik developers as an easy way to create data in their local Piwik
track_visits_inside_piwik_ui = 0

[General]
; character used to automatically create categories in the Actions > Pages, Outlinks and Downloads reports
; for example a URL like "example.com/blog/development/first-post" will create 
; the page first-post in the subcategory development which belongs to the blog category
action_url_category_delimiter = /

; similar to above, but this delimiter is only used for page titles in the Actions > Page titles report
action_title_category_delimiter = /

; this action name is used when the URL ends with a slash / 
; it is useful to have an actual string to write in the UI
action_default_name = index

; this action name is used when the URL has no page title or page URL defined
action_default_name_when_not_defined = "page title not defined"
action_default_url_when_not_defined = "page url not defined"

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

; This setting is overriden in the UI, under "User Settings". 
; The date and period loaded by Piwik uses the defaults below. Possible values: yesterday, today.
default_day = yesterday
; Possible values: day, week, month, year.
default_period = day

; This setting is overriden in the UI, under "General Settings". This is the default value used if the setting hasn't been overriden via the UI.
; Time in seconds after which an archive will be computed again. This setting is used only for today's statistics.
; Defaults to 10 seconds so that by default, Piwik provides real time reporting.
time_before_today_archive_considered_outdated = 10

; This setting is overriden in the UI, under "General Settings". The default value is to allow browsers
; to trigger the Piwik archiving process.
enable_browser_archiving_triggering = 1

; PHP minimum required version (minimum requirement known to date = ->newInstanceArgs)
minimum_php_version = 5.1.3

; MySQL minimum required version
; note: timezone support added in 4.1.3
minimum_mysql_version = 4.1

; PostgreSQL minimum required version
minimum_pgsql_version = 8.3

; Minimum adviced memory limit in php.ini file (see memory_limit value)
minimum_memory_limit = 128

; login cookie name
login_cookie_name = piwik_auth

; login cookie expiration (30 days)
login_cookie_expire = 2592000

; The path on the server in which the cookie will be available on. 
; Defaults to empty. See spec in http://curl.haxx.se/rfc/cookie_spec.html
login_cookie_path = 

; email address that appears as a Sender in the password recovery email
; if specified, {DOMAIN} will be replaced by the current Piwik domain
login_password_recovery_email_address = "password-recovery@{DOMAIN}"

; name that appears as a Sender in the password recovery email
login_password_recovery_email_name = Piwik

; during archiving, Piwik will limit the number of results recorded, for performance reasons
; maximum number of rows for any of the Referers tables (keywords, search engines, campaigns, etc.)
datatable_archiving_maximum_rows_referers = 1000
; maximum number of rows for any of the Referers subtable (search engines by keyword, keyword by campaign, etc.)
datatable_archiving_maximum_rows_subtable_referers = 50

; maximum number of rows for any of the Actions tables (pages, downloads, outlinks)
datatable_archiving_maximum_rows_actions = 500
; maximum number of rows for pages in categories (sub pages, when clicking on the + for a page category)
; note: should not exceed the display limit in Piwik_Actions_Controller::ACTIONS_REPORT_ROWS_DISPLAY
;       because each subdirectory doesn't have paging at the bottom, so all data should be displayed if possible.
datatable_archiving_maximum_rows_subtable_actions = 100

; maximum number of rows for other tables (Providers, User settings configurations)
datatable_archiving_maximum_rows_standard = 500

; by default, Piwik uses self-hosted AJAX libraries.
; If set to 1, Piwik uses a Content Distribution Network
use_ajax_cdn = 0

; required AJAX library versions
jquery_version = 1.4.2
jqueryui_version = 1.8.2
swfobject_version = 2.2

; If set to 0, Flash widgets require separate HTTP requests
; (i.e., one request to load the JavaScript which instantiates Open Flash Chart; the other request is made by OFC to download the JSON data for the chart)
; If set to 1, Piwik uses a single HTTP request per Flash widget to serve both the widget and data
serve_widget_and_data = 1

; If set to 1, Piwik adds a response header to workaround the IE+Flash+HTTPS bug.
reverse_proxy = 0

[Tracker]
; set to 0 if you want to stop tracking the visitors. Useful if you need to stop all the connections on the DB.
record_statistics			= 1

; length of a visit in seconds. If a visitor comes back on the website visit_standard_length seconds after his last page view, it will be recorded as a new visit  
visit_standard_length       = 1800

; visitors that stay on the website and view only one page will be considered staying 0 second
default_time_one_page_visit = 0

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

; The path on the server in which the cookie will be available on. 
; Defaults to empty. See spec in http://curl.haxx.se/rfc/cookie_spec.html
cookie_path = 

; name of the cookie to ignore visits
ignore_visits_cookie_name = piwik_ignore 

; variable name to track any campaign, for example CPC campaign
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC' then it will be counted as a campaign referer named 'Adwords-CPC'
campaign_var_name			= piwik_campaign

; variable name to track any campaign keyword
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC&piwik_kwd=My killer keyword' then it will be counted as a campaign referer named 'Adwords-CPC' with the keyword 'My killer keyword'
campaign_keyword_var_name	= piwik_kwd

; maximum length of a Page Title or a Page URL recorded in the log_action.name table
page_maximum_length = 1024;

; number of octets in IP address to mask, in order to anonymize a visitor's IP address
; if the AnonymizeIP plugin is deactivated, this value is ignored
; for IPv4 addresses, valid values are 0..4
ip_address_mask_length = 1


[log]
;possible values for log: screen, database, file
; by default, standard logging/debug messages are hidden from screen
;logger_message[]		= screen
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

[Plugins]
Plugins[] 		= CorePluginsAdmin
Plugins[] 		= CoreAdminHome
Plugins[] 		= CoreHome
Plugins[] 		= API
Plugins[] 		= Widgetize
Plugins[] 		= LanguagesManager
Plugins[] 		= Actions
Plugins[] 		= Dashboard
Plugins[] 		= MultiSites
Plugins[] 		= Referers
Plugins[] 		= UserSettings
Plugins[]		= Goals
Plugins[]		= SEO

Plugins[] 		= UserCountry
Plugins[] 		= VisitsSummary
Plugins[] 		= VisitFrequency
Plugins[] 		= VisitTime
Plugins[] 		= VisitorInterest
Plugins[] 		= ExampleAPI
Plugins[] 		= ExamplePlugin
Plugins[]		= ExampleRssWidget
Plugins[] 		= ExampleFeedburner
Plugins[] 		= Provider
Plugins[]		= Feedback

Plugins[] 		= Login
Plugins[] 		= UsersManager
Plugins[] 		= SitesManager
Plugins[] 		= Installation
Plugins[] 		= CoreUpdater

[PluginsInstalled]
PluginsInstalled[] = Login
PluginsInstalled[] = CoreAdminHome
PluginsInstalled[] = UsersManager
PluginsInstalled[] = SitesManager
PluginsInstalled[] = Installation

[Plugins_Tracker]
Plugins_Tracker[] = Provider
