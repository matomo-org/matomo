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