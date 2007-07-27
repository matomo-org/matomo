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


[smarty]
template_dir	= core/views/scripts
compile_dir		= tmp/templates_c
config_dir		= tmp/configs
cache_dir		= tmp/cache