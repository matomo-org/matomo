define piwik::repo(
  $directory  = $piwik::params::docroot,
  $version    = $piwik::params::piwik_version,
  $repository = $piwik::params::repository
) {

  if ! defined(File[$directory]) {
    file { "${directory}": }
  }

  class { 'piwik::user': directory => $directory }

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