class piwik::loganalytics {

  # required for Log Analytics (integration tests)

  package {
    'python-setuptools':
        ensure => latest;
    'python-dev':
        ensure => latest;
    'build-essential':
        ensure => latest;
  }

  # TODO add onlyif => ''
  exec { "install simplejson":
    command => "easy_install simplejson",
    require => [ Package['python-setuptools'], Package['python-dev'], Package['build-essential'] ];
  }

}