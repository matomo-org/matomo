# = Class: piwik::loganalytics
# 
# This class installes all required packages in order to use
# Log Analytics. Those packages are also required to run the
# Log Analytics integration tests.
# 
# == Parameters: 
# 
# == Requires: 
# 
# == Sample Usage:
#
#  include piwik::loganalytics
#
class piwik::loganalytics {

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
    unless  => "which pip";
  }

  exec { "install_simplejson":
    command => "pip install simplejson",
    require => Exec['easy_install_pip'],
    unless  => 'pip freeze | grep "simplejson" > /dev/null';
  }

}