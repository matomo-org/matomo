
$piwik_apache_domain = 'apache.piwik'
$piwik_apache_path   = '/var/www/piwik'
$piwik_apache_port   = '80'

class {'apache':  }

include apache::mod::php
apache::mod {'vhost_alias': }
apache::mod {'auth_basic': }
apache::mod {'rewrite': }

host {
    "${piwik_apache_domain}":
        ip      => "127.0.0.1";
} 

apache::vhost { 'apache.piwik':
    priority        => '10',
    vhost_name      => '_default_',
    port            => $piwik_apache_port,
    docroot         => $piwik_apache_path,
    configure_firewall => true,
}