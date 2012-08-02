# VisualPHPUnit

VisualPHPUnit is a visual front-end for PHPUnit.  It offers the following features:

* A stunning front-end which organizes test and suite results
* The ability to view unit testing progress via graphs
* An option to maintain a history of unit test results through the use of snapshots
* Enumeration of PHPUnit statistics and messages
* Convenient display of any debug messages written within unit tests
* Sandboxing of PHP errors
* The ability to generate test results from both a browser and the command line

## Screenshots

![Screenshot of VisualPHPUnit, displaying a breakdown of test results.](http://nsinopoli.github.com/VisualPHPUnit/vpu2_main.png "VisualPHPUnit Test Results")
![Screenshot of VisualPHPUnit, displaying a graph of test results.](http://nsinopoli.github.com/VisualPHPUnit/vpu2_graphs.png "VisualPHPUnit Statistics Graph")

## Requirements

VisualPHPUnit requires PHP 5.3+ and PHPUnit v3.5+.

## Upgrading From v1.x

VPU underwent a complete rewrite in v2.0.  Users who are looking to upgrade from v1.x are encouraged to follow the installation instructions outlined below.

### What About My Data?

Because the UI has been changed, snapshots from v1.x will not render correctly in v2.x.

Test statistics generated in v1.x, however, can still be used.  When installing, ignore the [migration](#graph-generation) and run the following commands against your old VPU database instead:

```sql
alter table SuiteResult change success succeeded int(11) not null;
alter table TestResult change success succeeded int(11) not null;
```

### I Miss v1.x!

While no longer actively supported, v1.x can be found on its [own branch](https://github.com/NSinopoli/VisualPHPUnit/tree/1.x).

## Installation

1. Download and extract (or git clone) the project to a web-accessible directory.
2. Change the permissions of `app/resource/cache` to `777`.
3. Open `app/config/bootstrap.php` with your favorite editor.
    1. Within the `$config` array, change `pear_path` so that it points to the directory where PEAR is located.
    2. Within the `$config` array, change `test_directory` so that it points to the root directory where your unit tests are stored.

## Web Server Configuration

### nginx

Place this code block within the `http {}` block in your `nginx.conf` file:

```nginx

    server {
	    server_name     vpu;
	    root            /srv/http/vpu/app/public;
	    index           index.php;

	    access_log      /var/log/nginx/vpu_access.log;
	    error_log       /var/log/nginx/vpu_error.log;

	    location / {
            try_files $uri /index.php;
	    }

	    location ~ \.php$ {
            fastcgi_pass    unix:/var/run/php-fpm/php-fpm.sock;
            fastcgi_index   index.php;
            fastcgi_param   SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include         fastcgi_params;
	    }
    }
```

Note that you will have to change the `server_name` to the name you use in your hosts file. You will also have to adjust the directories according to where you installed the code. In this configuration, /srv/http/vpu/ is the project root. The public-facing part of VisualPHPUnit, however, is located in app/public within the project root (so in this example, it's /srv/http/vpu/app/public).

When that's done, restart your web server, and then point your browser at the server name you chose above!

### Apache

VPU comes with .htaccess files, so you won't have to worry about configuring anything.  Simply point your browser at the location where you installed the code!

## Project Configuration (optional)

VPU comes with many of its features disabled by default.  In order to take advantage of them, you'll have to modify a few more lines in `app/config/bootstrap.php`.

### <a name='graph-generation'></a>Graph Generation

If you'd like to enable graph generation, you will have to do the following:

1. Within the `$config` array, change `store_statistics` to `true`.  If you'd like, you can keep this set as `false`, though you will have to change the 'Store Statistics' option to 'Yes' on the UI if you want the test statistics to be used in graph generation.
2. Run the migration `app/resource/migration/01_CreateSchema.sql` against a MySQL database.
    - Note that this will automatically create a database named `vpu` with the tables needed to save your test statistics.
3. Within the `$config` array, change the settings within the `db` array to reflect your database settings.
    - Note that if you're using the migration described above, `database` should remain set to `vpu`.
    - The `plugin` directive should not be changed.

### <a name='snapshots'></a>Snapshots

If you'd like to enable snapshots, you will have to do the following:

1. Within the `$config` array, change `create_snapshots` to `true`.  If you'd like, you can keep this set as `false`, though you will have to change the 'Create Snapshots' option to 'Yes' on the UI if you want the test results to be saved.
2. Within the `$config` array, change `snapshot_directory` to a directory where you would like the snapshots to be saved.
    - Note that this directory must have the appropriate permissions in order to allow PHP to write to it.
    - Note that the dropdown list on the 'Archives' page will only display the files found within `snapshot_directory`.

### <a name='sandboxing'></a>Error Sandboxing

If you'd like to enable error sandboxing, you will have to do the following:

1. Within the `$config` array, change `sandbox_errors` to `true`.  If you'd like, you can keep this set as `false`, though you will have to change the 'Sandbox Errors' option to 'Yes' on the UI if you want the errors encountered during the test run to be sandboxed.
2. Within the `$config` array, change `error_reporting` to reflect which errors you'd like to have sandboxed.  See PHP's manual entry on [error_reporting](http://php.net/manual/en/function.error-reporting.php) for more information.

### <a name='xml-configuration'></a>PHPUnit XML Configuration File

If you'd like to use a [PHPUnit XML configuration file](http://www.phpunit.de/manual/current/en/appendixes.configuration.html) to define which tests to run, you will have to do the following:

1. Within the `$config` array, change `xml_configuration_file` to the path where the configuration file can be found.
    - Note that if you leave this set to `false`, but select 'Yes' for the 'Use XML Config' option on the UI, VPU will complain and run with the tests chosen in the file selector instead.
2. Modify your PHPUnit XML configuration file to include this block:

```xml
       <!-- This is required for VPU to work correctly -->
       <listeners>
         <listener class="PHPUnit_Util_Log_JSON"></listener>
       </listeners>
```

### Bootstraps

If you'd like to load any bootstraps, you will have to do the following:

1. Within the `$config` array, list the paths to each of the bootstraps within the `bootstraps` array.

## Running VPU at the Command Line

VPU can be run at the command line, making it possible to automate the generation of test results via cron.

### Configuration

The CLI script requires that the `xml_configuration_file` setting within the `$config` array of `app/config/bootstrap.php` be properly set.  VPU will run the tests specified in the XML configuration file.  Please be sure that the [configuration file](#xml-configuration) contains the required JSON listener.

In order to [save](#snapshots) the test results, the CLI script also requires that the `snapshot_directory` setting within the `$config` array of `app/config/bootstrap.php` be properly set.  Note that the value of `create_snapshots` within the `$config` array has no effect on the CLI script.

Errors will be [sandboxed](#sandboxing) if `sandbox_errors` is set to `true` within the `$config` array of `app/config/bootstrap.php`.

Test statistics will be stored if `store_statistics` is set to `true` within the `$config` array of `app/config/bootstrap.php`.  Make sure that the [database](#graph-generation) is configured correctly.

### Executing

VPU can be executed from the command line using the following command:

```bash
# from the project root
bin/vpu
```

## Version Information

Current stable release is v2.0, last updated on June 16, 2012.

## Feedback

Feel free to send any feedback you may have regarding this project to NSinopoli@gmail.com.

## Credits

Special thanks to Matt Mueller (http://mattmueller.me/blog/), who came up with the initial concept, wrote the original code (https://github.com/MatthewMueller/PHPUnit-Test-Report), and was kind enough to share it.

Thanks to Mike Zhou, Hang Dao, Thomas Ingham, and Fredrik Wollsén for their suggestions!
