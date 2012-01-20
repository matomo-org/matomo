; <?php exit; ?> DO NOT REMOVE THIS LINE
; this file for documentation purposes; DO NOT COPY this file to config.ini.php;
; the config.ini.php is normally created during the installation process
; (see http://piwik.org/docs/installation)
; when this file is absent it triggers the Installation process to create
; config.ini.php; that file will contain the superuser and database access info

[superuser]
login			= yourSuperUserLogin
password		= yourSuperUserPasswordHash
email			= hello@example.org

[database]
host			= localhost
username		= databaseLogin
password		= datatabasePassword
dbname			= databaseName
adapter			= PDO_MYSQL ; PDO_MYSQL, MYSQLI, or PDO_PGSQL
tables_prefix	= piwik_
;charset		= utf8
