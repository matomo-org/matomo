# = Class: piwik::db
# 
# This class installs several database packages which are required by Piwik.
# It installs a MySQL server, starts the MySQL service and installs some 
# useful tools like Percona Toolkit and MySQLTuner.
# 
# == Parameters: 
# 
# $root_password::  A password for the MySQL root user
# $username::       If defined, a MySQL user with this name will be created 
# $password::       The MySQL user's password
# 
# == Requires: 
# 
# == Sample Usage:
#
#  include piwik::db
#
#  class {'piwik::db':
#    root_password => '123456',
#    username => 'piwik',
#    password => 'piwik',
#  }
#
class piwik::db(
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

  package { "percona-toolkit": ensure => installed }

}