
$piwik_domain = 'nginx.piwik'
$piwik_path   = '/var/www/piwik'
$piwik_port   = 80
$socket_path  = "${piwik_path}/tmp/fpm.socket"

host {
    "${piwik_domain}":
        ip      => "127.0.0.1";
} 

user { $piwik_domain:
  ensure  => present,
  comment => $piwik_domain,
  home    => $piwik_path,
  shell   => '/bin/false',
}

php::fpm::pool { $piwik_domain:
  pool_prefix          => $piwik_path,
  user                 => $piwik_domain,
  group                => $piwik_domain,
  listen_type          => 'socket',
  listen               => $socket_path,
  socket_owner         => 'www-data',
  socket_group         => 'www-data',
  socket_mode          => '0660',
  catch_workers_output => 'yes',
}

$php_locations = {
  "php-rewrite-${piwik_domain}" => {
    location  => '~ \.php$',
    vhost     => $piwik_domain,
    try_files => '$uri =404',
    fastcgi   => "unix:${socket_path}",
  }
}

node default {
  class { 'nginx': }
  nginx::resource::vhost { "${piwik_domain}":
    ensure   => present,
    www_root => $piwik_path,
    listen_port => 8080,
    locations => $php_locations
  }
}
