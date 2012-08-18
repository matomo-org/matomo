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