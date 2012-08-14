include php

php::module { ['snmp', 'xdebug', 'mysql', 'gd', 'sqlite', 'memcache', 'mcrypt', 'imagick', 'geoip', 'uuid', 'recode', 'cgi']:
  notify => Class['php::fpm::service'],
}

file { "/etc/php5/conf.d/pdo.ini":
  content => "extension=pdo.so",
  notify => Class['php::fpm::service'],
}

file { "/etc/php5/conf.d/pdo_mysql.ini":
  content => "extension=pdo_mysql.so",
  notify => Class['php::fpm::service'],
}

file { "/etc/php5/conf.d/mysqli.ini":
  content => "extension=mysqli.so",
  notify => Class['php::fpm::service'],
}

# TODO replace above files with php::conf
# php::conf { [ 'pdo' ]:
#   content => 'extension=pdo.so',
#   notify => Class['php::fpm::service'],
# }
# php::conf { [ 'pdo_mysql' ]:
#   content => 'extension=pdo_mysql.so',
#   notify => Class['php::fpm::service'],
# }
# php::conf { [ 'mysqli' ]:
#   content => 'extension=mysqli.so',
#   notify => Class['php::fpm::service'],
# }