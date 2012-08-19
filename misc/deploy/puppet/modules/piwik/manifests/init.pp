# = Class: piwik
# 
# This class installs all required packages and services in order to run Piwik. 
# It'll do a checkout of the Piwik repository as well. You only have to setup
# Apache and/or NGINX afterwards.
# 
# == Parameters: 
#
# $directory::         The piwik repository will be checked out into this directory.
# $repository::        Whether to checkout the SVN or Git reporitory. Defaults to svn. 
#                      Valid values: 'svn' and 'git'. 
# $version::           The Piwik version. Defaults to 'trunk'. 
#                      Valid values: For example 'tags/1.8.3' or 'branch/whatever'. 
# $db_user::           If defined, it creates a MySQL user with this username.
# $db_password::       The MySQL user's password.
# $db_root_password::  A password for the MySQL root user.
# $log_analytics::     Whether log analytics will be used. Defaults to true. 
#                      Valid values: true or false
# 
# == Requires: 
# 
# See README
# 
# == Sample Usage:
#
#  class {'piwik': }
#
#  class {'piwik':
#    db_root_password => '123456',
#    repository => 'git',
#  }
#
class piwik(
  $directory   = $piwik::params::docroot,
  $repository  = $piwik::params::repository,
  $version     = $piwik::params::piwik_version,
  $db_user     = $piwik::params::db_user,
  $db_password = $piwik::params::db_password,
  $db_root_password = $piwik::params::db_password,
  $log_analytics    = true
) inherits piwik::params {

  include piwik::base

  # mysql / db
  class { 'piwik::db':
    username      => $db_user,
    password      => $db_password,
    root_password => $db_root_password,
    require       => Class['piwik::base'],
  }

  class { 'piwik::php':
     require => Class['piwik::db'],
  }

  if $log_analytics == true {
    include piwik::loganalytics
  }

  class { 'piwik::user': directory => $directory }

  # repo checkout
  piwik::repo { 'piwik_repo_setup':
    directory  => $directory,
    version    => $version,
    repository => $repository,
    require    => Class['piwik::base'],
  }

}