; <?php exit; ?> DO NOT REMOVE THIS LINE
; If you want to change some of these default values, the best practise is to override
; them in your configuration file in config/config.ini.php. If you directly edit this file,
; you will lose your changes when you upgrade Piwik.
; For example if you want to override action_title_category_delimiter,
; edit config/config.ini.php and add the following:
; [General]
; action_title_category_delimiter = "-"

;--------
; WARNING - YOU SHOULD NOT EDIT THIS FILE DIRECTLY - Edit config.ini.php instead.
;--------

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
 
[superuser]
login			= 
password		=

[Debug]
; if set to 1, the archiving process will always be triggered, even if the archive has already been computed
; this is useful when making changes to the archiving code so we can force the archiving process
always_archive_data_period = 0;
always_archive_data_day = 0;
; Force archiving Custom date range (without re-archiving sub-periods used to process this date range)
always_archive_data_range = 0;

; if set to 1, all the SQL queries will be recorded by the profiler
; and a profiling summary will be printed at the end of the request
; NOTE: you must also set  [log] logger_message[] = "screen" to enable the profiler to print on screen
enable_sql_profiler = 0

; if set to 1, a Piwik tracking code will be included in the Piwik UI footer and will track visits, pages, etc. to idsite = 1
; this is useful for Piwik developers as an easy way to create data in their local Piwik
track_visits_inside_piwik_ui = 0

; if set to 1, javascript and css files will be included individually
; this option must be set to 1 when adding, removing or modifying javascript and css files
disable_merged_assets = 0

; If set to 1, all requests to piwik.php will be forced to be 'new visitors'
tracker_always_new_visitor = 0

; Allow automatic upgrades to Beta or RC releases
allow_upgrades_to_beta = 0

[General]
; the following settings control whether Unique Visitors will be processed for different period types.
; year and range periods are disabled by default, to ensure optimal performance for high traffic Piwik instances
; if you set it to 1 and want the Unique Visitors to be re-processed for reports in the past, drop all piwik_archive_* tables
; it is recommended to always enable Unique Visitors processing for 'day' periods
enable_processing_unique_visitors_day = 1
enable_processing_unique_visitors_week = 1
enable_processing_unique_visitors_month = 1
enable_processing_unique_visitors_year = 0
enable_processing_unique_visitors_range = 0

; when set to 1, all requests to Piwik will return a maintenance message without connecting to the DB
; this is useful when upgrading using the shell command, to prevent other users from accessing the UI while Upgrade is in progress
maintenance_mode = 0

; character used to automatically create categories in the Actions > Pages, Outlinks and Downloads reports
; for example a URL like "example.com/blog/development/first-post" will create
; the page first-post in the subcategory development which belongs to the blog category
action_url_category_delimiter = /

; similar to above, but this delimiter is only used for page titles in the Actions > Page titles report
action_title_category_delimiter = /

; the maximum url category depth to track. if this is set to 2, then a url such as
; "example.com/blog/development/first-post" would be treated as "example.com/blog/development".
; this setting is used mainly to limit the amount of data that is stored by Piwik.
action_category_level_limit = 10

; minimum number of websites to run autocompleter
autocomplete_min_sites = 5

; maximum number of websites showed in search results in autocompleter
site_selector_max_sites = 15

; if set to 1, shows sparklines (evolution graph) in 'All Websites' report (MultiSites plugin)
show_multisites_sparklines = 1

; number of websites to display per page in the All Websites dashboard
all_websites_website_per_page = 50

; if set to 0, the anonymous user will not be able to use the 'segments' parameter in the API request
; this is useful to prevent full DB access to the anonymous user, or to limit performance usage
anonymous_user_enable_use_segments_API = 1

; if browser trigger archiving is disabled, API requests with a &segment= parameter will still trigger archiving.
; You can force the browser archiving to be disabled in most cases by setting this setting to 0
; The only time that the browser will still trigger archiving is when requesting a custom date range that is not pre-processed yet
browser_archiving_disabled_enforce = 0

; this action name is used when the URL ends with a slash /
; it is useful to have an actual string to write in the UI
action_default_name = index

; if you want all your users to use Piwik in only one language, disable the LanguagesManager
; plugin, and set this default_language (users won't see the language drop down)
default_language = en

; default number of elements in the datatable
datatable_default_limit = 10

; default number of rows returned in API responses
API_datatable_default_limit = 100

; This setting is overriden in the UI, under "User Settings".
; The date and period loaded by Piwik uses the defaults below. Possible values: yesterday, today.
default_day = yesterday
; Possible values: day, week, month, year.
default_period = day

; Time in seconds after which an archive will be computed again. This setting is used only for today's statistics.
; Defaults to 10 seconds so that by default, Piwik provides real time reporting.
; This setting is overriden in the UI, under "General Settings".
; This is the default value used if the setting hasn't been overriden via the UI.
time_before_today_archive_considered_outdated = 10

; This setting is overriden in the UI, under "General Settings". The default value is to allow browsers
; to trigger the Piwik archiving process.
enable_browser_archiving_triggering = 1

; If set to 1, nested reports will be archived with parent references in the datatables
; At the moment, this is not needed in core but it can be handy for plugins
enable_archive_parents_of_datatable = 0

; MySQL minimum required version
; note: timezone support added in 4.1.3
minimum_mysql_version = 4.1

; PostgreSQL minimum required version
minimum_pgsql_version = 8.3

; Minimum adviced memory limit in php.ini file (see memory_limit value)
minimum_memory_limit = 128

; Minimum memory limit enforced when archived via misc/cron/archive.php
minimum_memory_limit_when_archiving = 768

; Piwik will check that usernames and password have a minimum length, and will check that characters are "allowed"
; This can be disabled, if for example you wish to import an existing User database in Piwik and your rules are less restrictive
disable_checks_usernames_attributes = 0

; Piwik will use the configured hash algorithm where possible.
; For legacy data, fallback or non-security scenarios, we use md5.
hash_algorithm = whirlpool

; by default, Piwik uses PHP's built-in file-based session save handler with lock files.
; For clusters, use dbtable.
session_save_handler = files

; by default, Piwik uses relative URLs, so you can login using http:// or https://
; (the latter assumes you have a valid SSL certificate).
; If set to 1, Piwik redirects the login form to use a secure connection (i.e., https).
force_ssl_login = 0

; If set to 1, Piwik will automatically redirect all http:// requests to https://
; If SSL / https is not correctly configured on the server, this will break Piwik
; If you set this to 1, and your SSL configuration breaks later on, you can always edit this back to 0 
; it is recommended for security reasons to always use Piwik over https
force_ssl = 0

; login cookie name
login_cookie_name = piwik_auth

; login cookie expiration (14 days)
login_cookie_expire = 1209600

; The path on the server in which the cookie will be available on.
; Defaults to empty. See spec in http://curl.haxx.se/rfc/cookie_spec.html
login_cookie_path =

; email address that appears as a Sender in the password recovery email
; if specified, {DOMAIN} will be replaced by the current Piwik domain
login_password_recovery_email_address = "password-recovery@{DOMAIN}"
; name that appears as a Sender in the password recovery email
login_password_recovery_email_name = Piwik

; Set to 1 to disable the framebuster on standard Non-widgets pages (a click-jacking countermeasure).
; Default is 0 (i.e., bust frames on all non Widget pages such as Login, API, Widgets, Email reports, etc.).
enable_framed_pages = 0

; Set to 1 to disable the framebuster on Admin pages (a click-jacking countermeasure).
; Default is 0 (i.e., bust frames on the Settings forms).
enable_framed_settings = 0

; language cookie name for session
language_cookie_name = piwik_lang

; standard email address displayed when sending emails
noreply_email_address = "noreply@{DOMAIN}"

; feedback email address;
; when testing, use your own email address or "nobody"
feedback_email_address = "hello@piwik.org"

; during archiving, Piwik will limit the number of results recorded, for performance reasons
; maximum number of rows for any of the Referers tables (keywords, search engines, campaigns, etc.)
; this limit will also be applied to the Custom Variables names and values reports
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

; by default, the real time Live! widget will update every 5 seconds and refresh with new visits/actions/etc.
; you can change the timeout so the widget refreshes more often, or not as frequently
live_widget_refresh_after_seconds = 5

; In "All Websites" dashboard, when looking at today's reports (or a date range including today),
; the page will automatically refresh every 5 minutes. Set to 0 to disable automatic refresh
multisites_refresh_after_seconds = 300

; by default, Piwik uses self-hosted AJAX libraries.
; If set to 1, Piwik uses a Content Distribution Network
use_ajax_cdn = 0

; required AJAX library versions
jquery_version = 1.7.2
jqueryui_version = 1.8.22
swfobject_version = 2.2

; Set to 1 if you're using https on your Piwik server and Piwik can't detect it,
; e.g., a reverse proxy using https-to-http, or a web server that doesn't
; set the HTTPS environment variable.
assume_secure_protocol = 0

; List of proxy headers for client IP addresses
;
; CloudFlare (CF-Connecting-IP)
;proxy_client_headers[] = HTTP_CF_CONNECTING_IP
;
; ISP proxy (Client-IP)
;proxy_client_headers[] = HTTP_CLIENT_IP
;
; de facto standard (X-Forwarded-For)
;proxy_client_headers[] = HTTP_X_FORWARDED_FOR

; List of proxy headers for host IP addresses
;
; de facto standard (X-Forwarded-Host)
;proxy_host_headers[] = HTTP_X_FORWARDED_HOST

; List of proxy IP addresses (or IP address ranges) to skip (if present in the above headers).
; Generally, only required if there's more than one proxy between the visitor and the backend web server.
;
; Examples:
;proxy_ips[] = 204.93.240.*
;proxy_ips[] = 204.93.177.0/24
;proxy_ips[] = 199.27.128.0/21
;proxy_ips[] = 173.245.48.0/20

; List of trusted hosts (eg domain or subdomain names) when generating absolute URLs.
;
; Examples:
;trusted_hosts[] = example.com
;trusted_hosts[] = stats.example.com

; The release server is an essential part of the Piwik infrastructure/ecosystem
; to provide the latest software version.
latest_version_url = http://piwik.org/latest.zip

; The API server is an essential part of the Piwik infrastructure/ecosystem to
; provide services to Piwik installations, e.g., getLatestVersion and
; subscribeNewsletter.
api_service_url = http://api.piwik.org

; When the ImageGraph plugin is activated, report metadata have an additional entry : 'imageGraphUrl'.
; This entry can be used to request a static graph for the requested report.
; When requesting report metadata with $period=range, Piwik needs to translate it to multiple periods for evolution graphs.
; eg. $period=range&date=previous10 becomes $period=day&date=previous10. Use this setting to override the $period value.
graphs_default_period_to_plot_when_period_range = day

[Tracker]
; Piwik uses first party cookies by default. If set to 1,
; the visit ID cookie will be set on the Piwik server domain as well
; this is useful when you want to do cross websites analysis
use_third_party_id_cookie = 0

; This setting should only be set to 1 in an intranet setting, where most users have the same configuration (browsers, OS)
; and the same IP. If left to 0 in this setting, all visitors will be counted as one single visitor.
trust_visitors_cookies = 0

; name of the cookie used to store the visitor information
; This is used only if use_third_party_id_cookie = 1
cookie_name	= _pk_uid

; by default, the Piwik tracking cookie expires in 2 years
; This is used only if use_third_party_id_cookie = 1
cookie_expire = 63072000

; The path on the server in which the cookie will be available on.
; Defaults to empty. See spec in http://curl.haxx.se/rfc/cookie_spec.html
; This is used for the Ignore cookie, and the third party cookie if use_third_party_id_cookie = 1
cookie_path =

; set to 0 if you want to stop tracking the visitors. Useful if you need to stop all the connections on the DB.
record_statistics			= 1

; length of a visit in seconds. If a visitor comes back on the website visit_standard_length seconds
; after his last page view, it will be recorded as a new visit
visit_standard_length       = 1800

; visitors that stay on the website and view only one page will be considered as time on site of 0 second
default_time_one_page_visit = 0

; if set to 1, Piwik attempts a "best guess" at the visitor's country of
; origin when the preferred language tag omits region information.
; The mapping is defined in core/DataFiles/LanguageToCountry.php,
enable_language_to_country_guess = 1

; When the misc/cron/archive.sh cron hasn't been setup, we still need to regularly run some maintenance tasks.
; Visits to the Tracker will try to trigger Scheduled Tasks (eg. scheduled PDF/HTML reports by email).
; Scheduled tasks will only run if 'Enable Piwik Archiving from Browser' is enabled in the General Settings.
; Tasks run once every hour maximum, they might not run every hour if traffic is low.
; Set to 0 to disable Scheduled tasks completely.
scheduled_tasks_min_interval = 3600

; name of the cookie to ignore visits
ignore_visits_cookie_name = piwik_ignore

; Comma separated list of variable names that will be read to define a Campaign name, for example CPC campaign
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC' then it will be counted as a campaign referer named 'Adwords-CPC'
; Includes by default the GA style campaign parameters
campaign_var_name			= "pk_campaign,piwik_campaign,utm_campaign,utm_source,utm_medium"

; Comma separated list of variable names that will be read to track a Campaign Keyword
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC&piwik_kwd=My killer keyword' ;
; then it will be counted as a campaign referer named 'Adwords-CPC' with the keyword 'My killer keyword'
; Includes by default the GA style campaign keyword parameter utm_term
campaign_keyword_var_name	= "pk_kwd,piwik_kwd,utm_term"

; maximum length of a Page Title or a Page URL recorded in the log_action.name table
page_maximum_length = 1024;

; Anonymize a visitor's IP address after testing for "Ip exclude"
; This value is the number of octets in IP address to mask; if the AnonymizeIP plugin is deactivated, this value is ignored.
; For IPv4 addresses, valid values are 0..4; for IPv6 addresses, valid values are 0..16
ip_address_mask_length = 1

; DO NOT USE THIS SETTING ON PUBLICLY AVAILABLE PIWIK SERVER
; !!! Security risk: if set to 0, it would allow anyone to push data to Piwik with custom dates in the past/future and with fake IPs !!!
; When using the Tracking API, to override either the datetime and/or the visitor IP, 
; token_auth with an "admin" access is required. If you set this setting to 0, the token_auth will not be required anymore.
; DO NOT USE THIS SETTING ON PUBLIC PIWIK SERVERS
tracking_requests_require_authentication = 1

[Segments]
; Reports with segmentation in API requests are processed in real time.
; On high traffic websites it is recommended to pre-process the data
; so that the analytics reports are always fast to load.
; You can define below the list of Segments strings
; for which all reports should be Archived during the cron execution
; All segment values MUST be URL encoded.
;Segments[]="visitorType==new"
;Segments[]="visitorType==returning"

; If you define Custom Variables for your visitor, for example set the visit type
;Segments[]="customVariableName1==VisitType;customVariableValue1==Customer"

[Deletelogs]
; delete_logs_enable - enable (1) or disable (0) delete log feature. Make sure that all archives for the given period have been processed (setup a cronjob!),
; otherwise you may lose tracking data.
; delete_logs_schedule_lowest_interval - lowest possible interval between two table deletes (in days, 1|7|30). Default: 7.
; delete_logs_older_than - delete data older than XX (days). Default: 180
delete_logs_enable = 0
delete_logs_schedule_lowest_interval = 7
delete_logs_older_than = 180
enable_auto_database_size_estimate = 1

[branding]
; custom logo
; if 1, custom logo is being displayed instead of piwik logo
use_custom_logo = 0

[mail]
defaultHostnameIfEmpty = defaultHostnameIfEmpty.example.org  ; default Email @hostname, if current host can't be read from system variables
transport =							; smtp (using the configuration below) or empty (using built-in mail() function)
port =								; optional; defaults to 25 when security is none or tls; 465 for ssl
host =								; SMTP server address
type =								; SMTP Auth type. By default: NONE. For example: LOGIN
username =							; SMTP username
password =							; SMTP password
encryption =						; SMTP transport-layer encryption, either 'ssl', 'tls', or empty (i.e., none).

[proxy]
type = BASIC						; proxy type for outbound/outgoing connections; currently, only BASIC is supported
host = 								; Proxy host: the host name of your proxy server (mandatory)
port = 								; Proxy port: the port that the proxy server listens to. There is no standard default, but 80, 1080, 3128, and 8080 are popular
username = 							; Proxy username: optional; if specified, password is mandatory
password = 							; Proxy password: optional; if specified, username is mandatory

[log]
;possible values for log: screen, database, file
; by default, standard logging/debug messages are hidden from screen
;logger_message[]		= screen
logger_error[]			= screen
logger_exception[]		= screen

; if set to 1, only requests done in CLI mode (eg. the archive.sh cron run) will be logged
; NOTE: log_only_when_debug_parameter will also be checked for
log_only_when_cli = 0

; if set to 1, only requests with "&debug" parameter will be logged
; NOTE: log_only_when_cli will also be checked for
log_only_when_debug_parameter = 0

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
Plugins[] 		= Proxy
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
Plugins[]		= PDFReports
Plugins[] 		= UserCountryMap
Plugins[] 		= Live
Plugins[]		= CustomVariables
Plugins[]		= PrivacyManager
Plugins[]		= ImageGraph
Plugins[]		= DoNotTrack

[PluginsInstalled]
PluginsInstalled[] = Login
PluginsInstalled[] = CoreAdminHome
PluginsInstalled[] = UsersManager
PluginsInstalled[] = SitesManager
PluginsInstalled[] = Installation

[Plugins_Tracker]
Plugins_Tracker[] = Provider
Plugins_Tracker[] = Goals
Plugins_Tracker[] = DoNotTrack

[APISettings]
; Any key/value pair can be added in this section, they will be available via the REST call
; index.php?module=API&method=API.getSettings 
; This can be used to expose values from Piwik, to control for example a Mobile app tracking
SDK_batch_size = 10
SDK_interval_value = 30

; NOTE: do not directly edit this file! See notice at the top
 
