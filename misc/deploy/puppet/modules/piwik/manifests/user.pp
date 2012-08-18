# = Class: piwik::user
# 
# Makes sure the user exists which is used by Apache and NGINX.
# 
# == Parameters: 
#
# $directory::  The home directory of the user. The directory must be created 
#               separately. This is typically the directory of the Piwik repo.
# 
# == Requires: 
# 
# == Sample Usage:
#
#  include piwik::user
#
class piwik::user(
  $directory  = $piwik::params::docroot
) {
    
  # user for apache / nginx
  user { "${piwik::params::user}":
    ensure    => present,
    comment   => $piwik::params::user,
    home      => $directory,
    shell     => '/bin/false',
  }

}