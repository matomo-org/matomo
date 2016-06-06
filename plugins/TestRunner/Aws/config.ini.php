; <?php exit; ?> DO NOT REMOVE THIS LINE
; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.
[database]
host = "127.0.0.1"
username = "root"
password = "secure"
dbname = "piwik"
tables_prefix = "piwik_"
charset = "utf8"

[tests]
request_uri = "/"

[database_tests]
username = "root"
password = "secure"
tables_prefix = ""

[General]
session_save_handler = "dbtable"
salt = "ad40b992685bd402cdddaa46bdff537e"
enable_update_communication = 0
trusted_hosts[] = "amazonaws.com"
trusted_hosts[] = "www.example.com"
trusted_hosts[] = "apache.piwik"
trusted_hosts[] = "nginx.piwik"
trusted_hosts[] = "amazonAwsUrl"
