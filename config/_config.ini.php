; <?php exit; ?> DO NOT REMOVE THIS LINE
; this file is just here for documentation purpose
; the config.ini.php is normally created during the installation process
; when this file is absent it triggers the Installation process
; the config.ini.php file contains information about the super user and the database access

[superuser]
login			= yourSuLogin
password		= yourSuPassword
email			= hello@piwik.org

[database]
host			= localhost
username		= databaseLogin
password		= datatabasePassword
dbname			= databaseName
adapter			= PDO_MYSQL ; PDO_MYSQL or MYSQLI
tables_prefix	= piwik_