[superuser]
login			= root
password		= nintendo

[database]
host			= localhost
username		= root
password		= nintendo
dbname			= piwiktrunk
adapter			= PDO_MYSQL
tables_prefix	= piwik_
profiler 		= true

[database_tests : database]
dbname			= piwiktests
tables_prefix	= piwiktests_


[log]

; normal messages
;logger_message[]		= screen
;logger_message[]		= database
;logger_message[]		= file

; all calls to the API (method name, parameters, execution time, caller IP, etc.)
;logger_api_call[]		= screen
;logger_api_call[]		= database
;logger_api_call[]		= file

; error intercepted
;logger_error[]			= screen
;logger_error[]			= database
;logger_error[]			= file

; exception raised
;logger_exception[]		= screen
;logger_exception[]		= database
;logger_exception[]		= file

; query profiling information (SQL, avg execution time, etc.)
;logger_query_profile[]	= screen
;logger_query_profile[]	= database
;logger_query_profile[]	= file


[path]
log				= logs/


[smarty]
template_dir	= core/views/scripts
compile_dir		= tmp/templates_c
config_dir		= tmp/configs
cache_dir		= tmp/cache