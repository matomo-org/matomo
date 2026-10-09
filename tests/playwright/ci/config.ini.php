; <?php exit; ?> DO NOT REMOVE THIS LINE
; Matomo config for the Playwright UI tests on GitHub Actions. Do not use this in production.

[database]
host = 127.0.0.1
username = root
password = "root"
dbname = matomo_tests
adapter = PDO_MYSQL
schema = Mysql
tables_prefix =

[database_tests]
host = 127.0.0.1
username = root
password = "root"
dbname = matomo_tests
adapter = PDO_MYSQL
schema = Mysql
tables_prefix =

[tests]
http_host = "localhost"
request_uri = "/"
enable_logging = 1

[log]
log_writers[] = file
log_level = info

[General]
