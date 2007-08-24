[superuser]
login			= root
password		= nintendo

[database]
host			= localhost
username		= root
password		= nintendo
dbname			= piwiktrunk
adapter			= PDO_MYSQL ; PDO_MYSQL or MYSQLI
tables_prefix	= piwik_
profiler 		= true

[database_tests : database]
dbname			= piwiktests
tables_prefix	= piwiktests_

[Language]
current			= en
default			= en

[Plugins]
enabled[] 		= UserSettings
enabled[] 		= Actions
enabled[] 		= Provider
enabled[] 		= UserCountry
enabled[] 		= Referers
enabled[] 		= VisitFrequency
enabled[] 		= VisitTime
enabled[] 		= VisitorInterest

[Plugins_LogStats]
enabled[] 		= Provider


[General]
; Time in seconds after which an archive will be computed again. 
; This setting is used only for today's statistics.
time_before_archive_considered_outdated = 3

action_category_delimiter = /


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
logger_api_call[]		= database
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
;logger_query_profile[]	= screen
;logger_query_profile[]	= database
;logger_query_profile[]	= file

[log_tests]
logger_message[]		= screen
logger_api_call[]		= screen
logger_error[]			= screen
logger_exception[]		= screen
logger_query_profile[]	= screen


[path]
log				= logs/


[smarty]
template_dir	= core/views/scripts
compile_dir		= tmp/templates_c
config_dir		= tmp/configs
cache_dir		= tmp/cache