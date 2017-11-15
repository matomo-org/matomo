# Troubleshooting Piwik Tests

If you have problems with running Piwik tests see below.

If you cannot solve your issues please [ask in the forums](https://forum.piwik.org/list.php?9)


## Important note for Linux users: fix for slow tests

If the tests are running incredibly slow on your machine, maybe you are running mysql DB on an ext4 partition?
Here is the tip that will save you hours of research: if you use Mysql on ext4 partition,
make sure you add "nobarrier" option to /etc/fstab to disable some super slow IO feature.

Change from:
    `UUID=83237e54-445f-8b83-180f06459d46       /       ext4    errors=remount-ro     0       1`
to this:
    `UUID=83237e54-445f-8b83-180f06459d46       /       ext4    errors=remount-ro,nobarrier     0       1`


## Using latest GIT version
On ubuntu to use the latest GIT:

```
sudo add-apt-repository ppa:git-core/ppa
sudo apt-get update
sudo apt-get upgrade
```

## Troubleshooting failing tests

If you get any of these errors:
 * `RuntimeException: Unable to create the cache directory ( piwik/tmp/templates_c/66/77).`
 * or `fopen( piwik/tmp/latest/testgz.txt): failed to open stream: No such file or directory`
 * or `Exception: Error while creating the file: piwik/tmp/latest/LATEST`
 * or `PHP Warning:  file_put_contents( piwik/tmp/logs/piwik.test.log): failed to open stream: Permission denied in [..]`

On your dev server, give your user permissions to write to the directory:

    $ sudo chmod 777 -R piwik/tmp/

**If you get the MySQL error number `2002`**, try changing the `[database_tests] host` config option to `"127.0.0.1"`.
