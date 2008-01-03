; <?php exit; ?> DO NOT REMOVE THIS LINE
[superuser]
login			= root
password		= nintendo

[database]
host			= 
username		= 
password		= 
dbname			= 
tables_prefix	= 
adapter			= PDO_MYSQL ; PDO_MYSQL or MYSQLI
;is it used? if yes add it in the session array in the installation
;profiler 		= false

[database_tests]
host 			= localhost
username 		= root
password 		= nintendo
dbname			= piwiktests
tables_prefix	= piwiktests_
adapter 		= PDO_MYSQL

[Language]
current			= en
default			= en

[Plugins]
enabled[] 		= Login
enabled[] 		= UsersManager
enabled[] 		= SitesManager

enabled[] 		= UserSettings
enabled[] 		= VisitsSummary
enabled[] 		= Actions
enabled[] 		= Provider
enabled[] 		= UserCountry
enabled[] 		= Referers
enabled[] 		= VisitFrequency
enabled[] 		= VisitTime
enabled[] 		= VisitorInterest
enabled[] 		= ExamplePlugin

;enabled[] 		= Openads
enabled[] 		= Installation

[Plugins_LogStats]
enabled[] 		= Provider

[Debug]
; if set to true, the archiving process will always be triggered, even if the archive has already been computed
; this is useful when making changes to the archiving code so we can 
always_archive_data = false


[General]
; Time in seconds after which an archive will be computed again. 
; This setting is used only for today's statistics.
time_before_archive_considered_outdated = 30

; character used to automatically create categories in the "Action" "Downloads" reports
; for example a URL like "example.com/blog/development/first-post" will create 
; the page first-post in the subcategory development which belongs to the blog category
action_category_delimiter = /

; default sorting order used by all datatables (desc or asc)
dataTable_default_sort_order = desc

; default number of elements in the datatable
dataTable_default_limit = 10

minimumPhpVersion = 5.1
minimumMemoryLimit = 128

[LogStats]
; set to 0 if you want to stop tracking the visitors. Useful if you need to stop all the connections on the DB.
record_statistics			= 1

; this action name is used when the javascript variable piwik_action_name is not specified in the piwik javascript code, and when the URL has no path.
default_action_name 		= index

; visitors that stay on the website and view only one page will be considered staying 10 seconds
default_time_one_page_visit = 10

; variable name used to specify a download link
; Example: '/piwik.php?idsite=1&download=http://piwik.org/piwik.zip' will redirect to 'http://piwik.org/piwik.zip'
download_url_var_name 		= download

; variable name used to specify a link to an external website
; Example: '/piwik.php?idsite=1&link=http://piwik.org/' will redirect to 'http://piwik.org/'
outlink_url_var_name		= link

; variable that contains the name of the download or the outlink to redirect to
; Example: '/piwik.php?idsite=1&download=http://piwik.org/piwik.zip&name=Piwik last version'
download_outlink_name_var   = name

; variable name to track a newsletter campaign. 
; Example: If a visitor first visits 'index.php?piwik_nl=Great offer' then it will be counted as a newsletter referer for the newsletter 'Great offer'  
newsletter_var_name			= piwik_nl

; variable name to track a referer coming from a partner website. 
; Example: If a visitor first visits 'index.php?piwik_partner=Amazon' then it will be counted as a partner referer with the name 'Amazon'  
partner_var_name			= piwik_partner

; variable name to track any campaign, for example CPC campaign
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC' then it will be counted as a campaign referer named 'Adwords-CPC'
campaign_var_name			= piwik_campaign

; variable name to track any campaign keyword
; Example: If a visitor first visits 'index.php?piwik_campaign=Adwords-CPC&piwik_kwd=My killer keyword' then it will be counted as a campaign referer named 'Adwords-CPC' with the keyword 'My killer keyword'
campaign_keyword_var_name	= piwik_kwd

; name of the cookie used to store the visitor information
cookie_name	= piwik_visitor

[log]

; normal messages
logger_message[]		= screen
;logger_message[]		= database
;logger_message[]		= file

; all calls to the API (method name, parameters, execution time, caller IP, etc.)
;logger_api_call[]		= screen
;logger_api_call[]		= database
;logger_api_call[]		= file

; error intercepted
logger_error[]			= screen
;logger_error[]			= database
;logger_error[]			= file

; exception raised
logger_exception[]		= screen
;logger_exception[]		= database
;logger_exception[]		= file

; query profiling information (SQL, avg execution time, etc.)
logger_query_profile[]	= screen
;logger_query_profile[]	= database
;logger_query_profile[]	= file

[log_tests]
logger_message[]		= screen
logger_api_call[]		= screen
logger_error[]			= screen
logger_exception[]		= screen
logger_query_profile[]	= screen


[path]
log				= tmp/logs/


[smarty]
; the list of directories in which to look for templates
template_dir[]	= plugins
template_dir[]	= themes/default
template_dir[]	= themes

; smarty provided plugins
plugins_dir[] 	= libs/Smarty/plugins
; smarty plugins provided by piwik 
plugins_dir[]	= modules/SmartyPlugins

; where to store the compiled smarty templates
compile_dir		= tmp/templates_c

cache_dir		= tmp/cache

; error reporting inside Smarty
error_reporting = E_ALL|E_NOTICE
; should be set to false in a piwik release
debugging		= true