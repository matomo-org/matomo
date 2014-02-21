; <?php exit; ?> DO NOT REMOVE THIS LINE
; This configuration is used for automatic integration
; tests on Travis-CI. Do not use this in production.

[database]
host = localhost
username = root
password =
dbname = piwik_tests
adapter = PDO_MYSQL
tables_prefix = piwiktests_
;charset = utf8

[database_tests]
host = localhost
username = root
password =
dbname = piwik_tests
adapter = PDO_MYSQL
tables_prefix = piwiktests_

[log]
log_writers[] = file
log_level = debug

[Tests]
persist_fixture_data = 1
