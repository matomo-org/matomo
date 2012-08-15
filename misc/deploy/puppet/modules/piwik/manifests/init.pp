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

  include piwik::php

  if $log_analytics == true {
    include piwik::loganalytics
  }

  # mysql / db
  piwik::db { 'piwik_db_setup':
    username      => $db_user,
    password      => $db_password,
    root_password => $db_root_password,
  }

  # repo checkout
  piwik::repo { 'piwik_repo_setup':
    directory  => $directory,
    version    => $version,
    repository => $repository,
  }

  # user for apache / nginx
  user { "${piwik::params::user}":
    ensure  => present,
    comment => $name,
    home    => $directory,
    shell   => '/bin/false',
  }

}