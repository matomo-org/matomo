; <?php exit; ?> DO NOT REMOVE THIS LINE
; This configuration is used for automatic integration
; tests on Travis-CI. Do not use this in production.

[database]
host = localhost
username = root
password =
dbname = piwik_tests
adapter = PDO\MYSQL
; no table prefix for tests on travis
tables_prefix = 
;charset = utf8

[database_tests]
host = localhost
username = root
password =
dbname = piwik_tests
adapter = PDO\MYSQL
; no table prefix for tests on travis
tables_prefix = 

[log]
log_writers[] = file
log_level = debug

; leave this empty here
[General]
