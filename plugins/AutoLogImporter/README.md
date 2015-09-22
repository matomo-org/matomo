# Piwik AutoLogImporter Plugin

[![Build Status](https://magnum.travis-ci.com/PiwikPRO/plugin-AutoLogImporter.svg?token=YG4RfcyzVryveJy9zhmw&branch=master)](https://magnum.travis-ci.com/PiwikPRO/plugin-AutoLogImporter)

## Description

Automatically imports all log files within a specified directory. It remembers which files of a directory have been
imported and only imports new files. Important: If a file was changed after an import the file is considered as a
new file and will be imported again.

Features:
* Automatically imports files from a specified directory
* Makes sure to not import the same file twice
* Gives you detailed output the log import result
* Shows which files will be imported next
* Shows which files are currently imported
* Shows which files have been imported in the past
* Custom log importer options can be configured to make it work with different log formats etc
* Imported files won't be deleted after importing but marked as imported in the database

## FAQ

__What are the requirements?__

* You need to have Python installed under `/usr/bin/python` see https://github.com/piwik/piwik-log-analytics.
* It currently works only out of the box if the log format can be detected automatically (eg Apache). A different log format can be configured in `config.php` see further below.
* We currently assume `--replay-tracking`, meaning the log file should contain requests to `piwik.php`.
* The PHP method `exec()` must be enabled.
* This plugin has not been tested with very large log files.
* Beside each log file there has to be a `.log.hash` file containing the md5 sum of the log file, otherwise a log file will be skipped. This is to make sure the log file was copied correctly via the `FileSynchronizer` plugin.

__How do I setup this plugin?__

* Install this plugin
* Configure it via "Administration => Plugin Settings".

**Warning**:
Make sure to not specify for example the path to the Apache log files directly as we would possibly import the same file
multiple times. Imagine there's an `access.log` and Apache writes each request into it as they are coming. We would
import this file every hour (as the task to import log files runs every hour). We cannot detect that we imported this
file already has the MD5 hash changes every time the file is imported. Unfortunately, the
[Log importer](https://github.com/piwik/piwik-log-analytics) cannot detect which lines of a file were already imported.

The easiest way to setup this plugin is to install the plugin `FileSynchronizer` as well. Specify the Apache log files
directory as source directory and `*-*-*.log` as file pattern. This makes sure to not copy `access.log` but log files
of the previous day, eg `2015-02-10.log`. Specify any target directory in the `FileSynchronizer`, just make sure to also
configure this directory for this plugin.

This will make sure only (finished) log files of previous days will be copied and imported, and it makes sure to create
a `.hash` file for each log file to make sure the log file will be actually imported.

__Why is a ".hash" file needed for each log file?__

Short version: To prevent "race conditions". Long version: Imagine the `FileSynchronizer` copies log files to the
target directory, possibly from a remote server. At the same time this plugin is starting to import log files. We would
import a log file that is currently still being copied and possibly even import twice as the MD5 hash would change once
the file is fully copied. The `.hash` file is created after the file was copied so we can verify whether the file was
copied successfully.

__How can I access details about imported files?__

Either by accessing the diagnostic status page under "Administration => Auto Log Import" or by having a look at the
database table `auto_log_importer`.

__Can I configure the log parameters passed to the log importer?__

Yes! Create the file `/config/config.php` in case it does not exist and dfine additional parameters like this:

```
<?php

return array(
    'AutoLogImporter.logImportOptions' => array('--token-auth=FOOBAR', '--enable-static')
);
```

__How can I trigger a log import manually?__

You can trigger a file import manually by executing the following command:

`./console core:run-scheduled-tasks "Piwik\Plugins\AutoLogImporter\Tasks.importLogFiles"`

__Can I force a certain log file to be written into a specific file?__

If the filename contains the pattern `_idsite_$idSite` (eg "2015-01-01_idsite_1.log"), the log importer will
automatically set the option `--idsite=$idSite`.

__I want to see more than the latest 200 imported files, is it possible?__

Yes, there is a `limit` URL parameter that you can change to any number.


## Changelog

* 0.1.0 Initial version

## Contact
To get a license for one of the Enterprise plugins, or to access the latest updates, contact us.
If you have any suggestion, code review, or feedback please email contact@piwik.pro

