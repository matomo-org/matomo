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

  exec { "easy_install_pip":
    command => "easy_install pip",
    require => [ Package['python-setuptools'], Package['python-dev'], Package['build-essential'] ],
    unless  => "pip --help";
  }

  exec { "install_simplejson":
    command => "pip install simplejson",
    require => Exec['easy_install_pip'],
    unless  => "pip search simplejson";
  }

}