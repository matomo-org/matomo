define piwik::nginx (
  $port    = 8080,
  $docroot = $piwik::params::docroot
) {

  $socket_path = "${docroot}/tmp/fpm.socket"

  host { "${name}":
    ip => "127.0.0.1";
  } 

  php::fpm::pool { "${name}":
    pool_prefix          => $docroot,
    user                 => $piwik::params::user,
    group                => $piwik::params::group,
    listen_type          => 'socket',
    listen               => $socket_path,
    socket_owner         => $piwik::params::user,
    socket_group         => $piwik::params::group,
    socket_mode          => '0660',
    catch_workers_output => 'yes',
  }

  $php_locations = {
    "php-rewrite-${name}" => {
      location  => '~ \.php$',
      vhost     => $name,
      try_files => '$uri =404',
      fastcgi   => "unix:${socket_path}",
    }
  }

  class { 'nginx': }

  nginx::resource::vhost { "${name}":
    ensure      => present,
    www_root    => $docroot,
    listen_port => $port,
    locations   => $php_locations,
  }

}