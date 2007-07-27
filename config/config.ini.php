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

; query profiling information (SQL, avg execution time, etc.)
query_profiles[]	= screen
query_profiles[]	= database
query_profiles[]	= file

; all call to the API (method name, parameters, execution time, caller IP, etc.)
api_calls[]			= screen
api_calls[]			= database
api_calls[]			= file

; exception raised
exceptions[]		= screen
exceptions[]		= database
exceptions[]		= file

; error intercepted
errors[]			= screen
errors[]			= database
errors[]			= file

; normal messages
messages[]			= screen
messages[]			= database
messages[]			= file


[path]
log				= logs/


[smarty]
template_dir	= core/views/scripts
compile_dir		= tmp/templates_c
config_dir		= tmp/configs
cache_dir		= tmp/cache