Exec {
  path => "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
}

class { 'piwik':
  directory     => '/var/www/piwik',
  repository    => 'svn',
  version       => 'trunk',
  db_user       => 'piwik@%',
  db_password   => 'secure',
  log_analytics => true,
}

piwik::apache { 'apache.piwik':
  port     => 80,
  docroot  => '/var/www/piwik',
  priority => '10',
}

piwik::nginx { 'nginx.piwik':
  port    => 8080,
  docroot => '/var/www/piwik',
}
