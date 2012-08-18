# = Class: piwik::base
# 
# This class installes some base packages like git, subversion, vim
# and more.
# 
# == Parameters: 
# 
# == Requires: 
# 
# == Sample Usage:
#
#  include piwik::base
#
class piwik::base {

  include apt

  notify {"apt-get_update": }

  exec { "base_apt-get_update": command => "apt-get update" }

  package { 'vim': ensure => installed, require => Exec['base_apt-get_update'] }

  package { 'subversion': ensure => installed, require => Exec['base_apt-get_update'] }

  package { 'facter': ensure => latest, require => Exec['base_apt-get_update'] }

  package { 'strace': ensure => latest, require => Exec['base_apt-get_update'] }

  package { 'tcpdump': ensure => latest, require => Exec['base_apt-get_update'] }

  package { 'wget': ensure => latest, require => Exec['base_apt-get_update'] }

  package { 'curl': ensure => latest, require => Exec['base_apt-get_update'] }

  include git
}