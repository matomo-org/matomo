; <?php exit; ?> DO NOT REMOVE THIS LINE
; This configuration is used for automatic integration
; tests on Travis-CI. Do not use this in production.

[superuser]
login            = admin
password        = 098f6bcd4621d373cade4e832627b4f6
email            = hello@example.org

[database]
host            = localhost
username        = root
password        =
dbname            = piwik_tests
adapter            = PDO_MYSQL ; PDO_MYSQL, MYSQLI, or PDO_PGSQL
tables_prefix        = piwiktests_
;charset        = utf8

[log]
log_writers[] = file
log_level = debug