class piwik::php {

  include php

  php::module { ['snmp', 'xdebug', 'mysql', 'gd', 'sqlite', 'memcache', 'mcrypt', 'imagick', 'geoip', 'uuid', 'recode', 'cgi']: 
    require => Class["php::install", "php::config"],
  }

  php::conf { [ 'pdo' ]:
    source  => 'puppet:///modules/piwik/etc/php5/conf.d/',
    require => Class["php::install", "php::config"],
  }
  php::conf { [ 'pdo_mysql' ]:
    source  => 'puppet:///modules/piwik/etc/php5/conf.d/',
    require => Class["php::install", "php::config"],
  }
  php::conf { [ 'mysqli' ]:
    source  => 'puppet:///modules/piwik/etc/php5/conf.d/',
    require => Class["php::install", "php::config"],
  }

  class { 'pear': require => Class['php::install'] }
  class { 'phpqatools': require => Class['pear'] }

  pear::package { "PHPUnit_MockObject":
    repository => "pear.phpunit.de",
    require    => Pear::Package["PEAR"],
  }

  pear::package { "PHP_CodeCoverage":
    repository => "pear.phpunit.de",
    require    => Pear::Package["PEAR"],
  }

  pear::package { "PHPUnit_Selenium":
    repository => "pear.phpunit.de",
    require    => Pear::Package["PEAR"],
  }

  exec { 'install_composer':
    command => 'curl -s https://getcomposer.org/installer | php -- --install-dir="/bin"',
    require => [ Package['curl'], Class["php::install", "php::config"] ],
    unless  => 'which composer.phar',
  }

  # TODO add channels... we should fork pear module and send pull requests
  # pear module should allow to add channels, do upgrade and install a
  # package only if not already installed
  # pear upgrade pear
  # pear channel-discover pear.phpunit.de
  # pear channel-discover pear.symfony-project.com
  # pear channel-discover components.ez.no
  # pear update-channels
  # pear upgrade-all
  # pear install --alldeps phpunit/PHPUnit

}