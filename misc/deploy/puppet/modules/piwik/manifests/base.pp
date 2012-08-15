class piwik::base {

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
  }

  include git
}