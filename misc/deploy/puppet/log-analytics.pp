
# packages required for Log Analytics (integration tests)
package {
  'python-setuptools':
      ensure => latest;
  'python-dev':
      ensure => latest;
  'build-essential':
      ensure => latest;
}

exec { "install simplejson":
  command => "easy_install simplejson"
}