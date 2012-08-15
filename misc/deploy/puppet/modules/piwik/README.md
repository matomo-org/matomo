# puppet-piwik

## Piwik - Open source web analytics

### License: 
http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

### Link: 
http://piwik.org

## How to use

### Simple Example:
```
	class { 'piwik': }
	piwik::apache { 'apache.piwik': }
```

### Full example:
```
	class { 'piwik':
	  directory     => '/var/www/piwik',
	  repository    => 'svn',
	  version       => 'trunk',
	  db_user       => 'username',
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
```

Do not forget to update your local hosts file when adding servers

### Requirements
* saz-php
* dazz/puppet-composer
* puppet-augeas
* rafaelfc-phpqatools
* puppet-pear
* puppetlabs-stdlib
* puppetlabs-firewall
* openstackci/vcsrepo
* puppetlabs-git
* camptocamp/puppet-common
* camptocamp/puppet-apt
* codec/puppet-users
* puppetlabs-apache
* puppetlabs-mysql
* puppetlabs-nginx