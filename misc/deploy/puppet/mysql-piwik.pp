class { 'mysql': }

class { 'mysql::server':
  config_hash => { 'root_password' => 'secure' }
}

# mysql::db { 'piwik':
#  user     => 'root',
#  password => 'secure',
#  host     => 'localhost',
#  grant    => ['all'],
#}

