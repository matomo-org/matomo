define piwik::db(
  $username      = $piwik::params::db_user,
  $password      = $piwik::params::db_password,
  $root_password = $piwik::params::db_password
) {

  class { 'mysql': }

  class { 'mysql::server':
    config_hash => { 'root_password' => $root_password }
  }

  if $username != '' {

# there seems to be a bug in database_user 
#    database_user { "${username}":
#      ensure        => present,
#      password_hash => mysql_password($password),
#      provider      => 'mysql',
#      require       => Class['mysql::server'],
#    }

#    database_grant { "${username}":
#      privileges => ['all'],
#      provider   => 'mysql',
#      require    => Database_user["${username}"],
#    }

  }

  include mysql::server::mysqltuner

}