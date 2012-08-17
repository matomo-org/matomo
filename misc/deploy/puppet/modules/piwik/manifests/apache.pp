define piwik::apache (
  $port     = '80',
  $docroot  = $piwik::params::docroot,
  $priority = '10'
) {  
  
  # user for apache / nginx
  user { "${piwik::params::user}":
    ensure    => present,
    comment   => $name,
    home      => $directory,
    shell     => '/bin/false',
    require   => [ Piwik::Repo['piwik_repo_setup'], Class['piwik::php'] ],
  }

  host { "${name}":
    ip => "127.0.0.1";
  } 

  include apache

  include apache::mod::php
  include apache::mod::auth_basic
  apache::mod {'vhost_alias': }
  apache::mod {'rewrite': }

  apache::vhost { "${name}":
    priority   => $priority,
    vhost_name => '_default_',
    port       => $port,
    docroot    => $docroot,
    require    => [ Piwik::Repo['piwik_repo_setup'], Class['piwik::php'], User["${piwik::params::user}"] ],
    configure_firewall => true,
  }

}