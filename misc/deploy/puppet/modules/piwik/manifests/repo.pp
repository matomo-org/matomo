define piwik::repo(
  $directory  = $piwik::params::docroot,
  $version    = $piwik::params::piwik_version,
  $repository = $piwik::params::repository
) {

  if $repository == 'svn' {
    vcsrepo { "${directory}":
      ensure   => present,
      provider => svn,
      source   => "${piwik::params::svn_repository}/${version}",
      owner    => $piwik::params::user,
      group    => $piwik::params::group,
    }
  }

  if $repository == 'git' {
    vcsrepo { "${directory}":
      ensure   => present,
      provider => git,
      source   => "${piwik::params::svn_repository}/${version}",
      owner    => $piwik::params::user,
      group    => $piwik::params::group,
    }
  }

  file { "${directory}/config":
    ensure => directory,
    mode   => '0777',
  }

  file { "${directory}/tmp":
    ensure => directory,
    mode   => '0777',
  }

}