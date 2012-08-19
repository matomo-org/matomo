# = Definition: piwik::repo
#
# This definition clones a specific version from a Piwik
# repository into the specified directory.
#
# == Parameters: 
#
# $directory::   The piwik repository will be checked out/cloned into this 
#                directory.
# $version::     The Piwik version. Defaults to 'trunk'. 
#                Valid values: For example 'tags/1.8.3' or 'branch/whatever'.
# $repository::  Whether to checkout the SVN or Git reporitory. Defaults to svn. 
#                Valid values: 'svn' and 'git'.  
#
# == Actions:
#
# == Requires: 
#
# == Sample Usage:
#
#  piwik::repo { 'piwik_repo_simple': }
#
#  piwik::repo { 'piwik_repo_full':
#    directory  => '/var/www/piwik',
#    version    => 'trunk',
#    repository => 'svn',
#  }
#
define piwik::repo(
  $directory  = $piwik::params::docroot,
  $version    = $piwik::params::piwik_version,
  $repository = $piwik::params::repository
) {

  if ! defined(File[$directory]) {
    file { "${directory}": }
  }

  if $repository == 'svn' {
    vcsrepo { "${directory}":
      ensure   => present,
      provider => svn,
      source   => "${piwik::params::svn_repository}/${version}",
      owner    => $piwik::params::user,
      group    => $piwik::params::group,
      require  => User["${piwik::params::user}"],
    }
  }

  if $repository == 'git' {
    vcsrepo { "${directory}":
      ensure   => present,
      provider => git,
      source   => "${piwik::params::svn_repository}/${version}",
      owner    => $piwik::params::user,
      group    => $piwik::params::group,
      require  => User["${piwik::params::user}"],
    }
  }

  file { "${directory}/config":
    ensure    => directory,
    mode      => '0777',
    subscribe => Vcsrepo["${directory}"],
  }

  file { "${directory}/tmp":
    ensure    => directory,
    mode      => '0777',
    subscribe => Vcsrepo["${directory}"],
  }

}