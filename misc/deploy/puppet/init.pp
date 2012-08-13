
Exec {
  path => "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
}

# does an apt-get update
include apt

package {
    'vim':
        ensure => installed;
    'subversion':
        ensure => installed;
    'facter':
        ensure => latest;
    'strace':
        ensure => latest;
    'tcpdump':
        ensure => latest;
    'wget':
        ensure => latest;
    'python-software-properties':
        ensure => installed;
}

include git

import 'php-piwik'

import 'piwikrepo'

import 'mysql-piwik'

import 'nodes'