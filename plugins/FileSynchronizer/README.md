# Piwik FileSynchronizer Plugin

[![Build Status](https://magnum.travis-ci.com/PiwikPRO/plugin-FileSynchronizer.svg?token=YG4RfcyzVryveJy9zhmw)](https://magnum.travis-ci.com/PiwikPRO/plugin-FileSynchronizer)

## Description

Synchronizes any files of a directory with another directory. Useful for example to copy log files to another directory.

Features:

* Syncs any files from one directory to another
* Can copy files to a remote computer by changing the copy template (eg `scp $source user@host:$target`)
* Specify a shell wildcard to sync only files matching this pattern
* Modify the filename while syncing
* Shows which files will be synced next
* Shows which files are currently syncing
* Shows which files were synced in the past
* Gives detailed error output if a sync failed and will retry automatically
* Syncs files once per hour

## FAQ

__Can I sync files to another server?__

Yes, you can specify a copy template like `scp $source user@host:$target`. By default files are simply copied from
`$source` to `$target` via the linux command `cp`.

__How often are files synchronized?__

Once per hour.

__Is Windows supported?__

No.

__How can I verify the file was correctly copied?__

We create a hash file for each copied file containing the MD5 hash of the content. If the file name is `access.log`,
there will be a `access.log.hash` in the configured target directory once the file was copied successfully.

__What happens when a file having the same name already exists in the target directory?__

The file in the target directory will be overwritten.

__How can I trigger a file sync manually?__

You can trigger a file sync manually by executing the following command:

`./console core:run-scheduled-tasks "Piwik\Plugins\FileSynchronizer\Tasks.syncFiles"`

__How can I access details about previously synced files?__

Either by accessing the diagnostic status page under "Administration => File Synchronizer" or by having a look at the
database table `file_synchronizer`.

__I want to see more than the latest 200 synced files, is it possible?__

Yes, there is a `limit` URL parameter that you can change to any number.

## Changelog

* 0.1.0 Initial version

## Contact
To get a license for one of the Enterprise plugins, or to access the latest updates, contact us.
If you have any suggestion, code review, or feedback please email contact@piwik.pro
