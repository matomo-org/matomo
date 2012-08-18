# = Class: piwik::params
# 
# This class manages Piwik parameters
# 
# == Parameters: 
# 
# == Requires: 
# 
# == Sample Usage:
#
# This class file is not called directly
#
class piwik::params {
  $user    = 'www-data'
  $group   = 'www-data'
  $docroot = '/var/www/piwik'

  $repository     = 'svn'
  $svn_repository = 'http://dev.piwik.org/svn/'
  $git_repository = 'https://github.com/piwik/piwik'
  $piwik_version  = 'trunk'

  $db_user     = 'piwik@localhost'
  $db_password = 'secure'
}