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

include pear
include phpqatools

pear::package { "PHPUnit_MockObject":
  repository => "pear.phpunit.de",
  require => Pear::Package["PEAR"],
}

pear::package { "PHP_CodeCoverage":
  repository => "pear.phpunit.de",
  require => Pear::Package["PEAR"],
}

pear::package { "PHPUnit_Selenium":
  repository => "pear.phpunit.de",
  require => Pear::Package["PEAR"],
}

# todo add channels... we should fork pear module and send pull requests
# pear module should allow to add channels, do upgrade and install a
# package only if not already installed
# pear upgrade pear
# pear channel-discover pear.phpunit.de
# pear channel-discover pear.symfony-project.com
# pear channel-discover components.ez.no
# pear update-channels
# pear upgrade-all
# pear install --alldeps phpunit/PHPUnit