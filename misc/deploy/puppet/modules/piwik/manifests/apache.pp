# = Definition: piwik::apache
#
# This definition installs Apache2 including some modules like
# mod_rewrite and creates a virtual host.
#
# == Parameters: 
#
# $name::      The name of the host
# $port::      The port to configure the host
# $priority::  The priority of the site
# $docroot::   The location of the files for this host
#
# == Actions:
#
# == Requires: 
#
# The piwik class
#
# == Sample Usage:
#
#  piwik::apache { 'apache.piwik': }
#
#  piwik::apache { 'apache.piwik':
#    port     => 80,
#    priority => '10',
#    docroot  => '/var/www/piwik',
#  }
#
define piwik::apache (
  $port     = '80',
  $docroot  = $piwik::params::docroot,
  $priority = '10'
) {  

  host { "${name}":
    ip => "127.0.0.1";
  } 

  include apache

  include apache::mod::php
  include apache::mod::auth_basic
  # TODO move this to a class and include it. This allows us to define multiple apache hosts
  apache::mod {'vhost_alias': }
  apache::mod {'rewrite': }

  apache::vhost { "${name}":
    priority   => $priority,
    vhost_name => '_default_',
    port       => $port,
    docroot    => $docroot,
    require    => [ Host[$name], Piwik::Repo['piwik_repo_setup'], Class['piwik::php'] ],
    configure_firewall => true,
  }

}