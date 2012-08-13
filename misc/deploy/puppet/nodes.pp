host {
    "piwik.local":
        ip      => "127.0.0.1";
} # host

node default {
  class { 'nginx': }
  nginx::resource::vhost { 'piwik.local':
    ensure   => present,
    www_root => '/var/www/piwik',
    listen_port => 8001,
  }
}
