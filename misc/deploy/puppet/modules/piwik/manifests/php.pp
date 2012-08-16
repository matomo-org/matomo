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

  include pear
  include phpqatools

  pear::package { "PHPUnit_MockObject":
    repository => "pear.phpunit.de",
    require    => [ Pear::Package["PEAR"], Class['pear'] ],
  }

  pear::package { "PHP_CodeCoverage":
    repository => "pear.phpunit.de",
    require    => [ Pear::Package["PEAR"], Class['pear'] ],
  }

  pear::package { "PHPUnit_Selenium":
    repository => "pear.phpunit.de",
    require    => [ Pear::Package["PEAR"], Class['pear'] ],
  }

  package { "percona-toolkit": ensure => installed }
  
  exec { 'install_composer':
    command => 'wget http://getcomposer.org/composer.phar -O composer.phar | php -- --install-dir=bin',
    require => [ Package['wget'], Class["php::install", "php::config"] ],
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