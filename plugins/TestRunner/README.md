# Piwik TestRunner Plugin

## FAQ

__Can I easily change the config for tests that run on AWS?__

Yes, just edit `Aws/config.ini.php`

__I want to run the tests with different parameters on AWS, is it possible?__

Yes, at the time of writing this you have to edit the file `Runner/Remote.php`

__Why am I getting an error "AWS was not able to validate the provided access credentials"?__

It could be caused by an invalid set date. Execute `date` on the command line and make sure it is correct.

__How can I change the base image (AMI) that is used for AWS tests?__

* Log in to AWS
* Select `EC2 => AMI`
* Launch a new instance of the current AMI by selecting it and pressing `Launch`
* Select a `c3.large` instance type
* Press `Review and Launch` and on next page `Launch` (there you have to select your keypair otherwise you can't log in)
* Log in to the newly created instance. To get login information 
  * Go to `EC2 => Instances`
  * Select the created instance
  * Press `Connect`
  * SSH connect example is listed there
  * Make sure to use user `ubuntu` and not `root`
* Make changes on the instance
* When you are done
  * Go into the home directory `cd`
  * Clear the history: `cat /dev/null > ~/.bash_history && history -c`
  * Execute `cd www/piwik`, then `exit`. Why? Whenever a new instance is created, those two commands will be in the history 
    and provides a better usability for the developer who accesses it as those two commands are most likely needed.
  * Reflect the changes you did in Puppet https://github.com/piwik/piwik-dev-environment/tree/master/puppet/modules/piwik/manifests 
    or if you don't know Puppet at least add it in this shell script https://github.com/piwik/piwik-dev-environment/blob/master/puppet/files/setup.sh
    For instance if you installed a new package you can simply add a new entry here https://github.com/piwik/piwik-dev-environment/blob/master/puppet/modules/piwik/manifests/base.pp
* In `EC2 => Instances` menu select the instance you are currently using.
* Select `Actions => Image => Create Image`
* Define the name `Piwik Testing vX.X` and a description like `Used to run Piwik tests via Piwik console`. Make sure to increase the box version in X.X (have a look in `EC2 => AMI` for current version)
* Press `Create Image`
* Go to `EC2 => AMIs` menu and while waiting for the image creation to complete add the following tags
  * `Name` => `PiwikTesting`
  * `Ubuntu` => Ubuntu version eg `14.04`
  * `BoxVersion` => Version of the box eg `3.3`
  * `PHP` => PHP Version eg `5.5`
  * `MySQL` => MySQL Version eg `5.5`
* Copy the assigned AMI ID and replace the config value `[tests]aws_ami = ...`  in `global.ini.php`
* Once the AMI is available trigger an `integration`, `system`, and `ui` test run using the `tests:run-aws` command to make sure everything still works
* Commit / push the new AMI-ID
* Once everything works remove the outdated AMI by selecting it and clicking `Actions => Deregister`. 

In the future once everything is completely automated we would simple create a new instance out of ((Vagrant || Docker) && Puppet) whenever we need a change but it takes a lot of time to do this and is not worth it right now.

__How do I create a new EC2 key/pair for a developer?__

1. Go to: https://console.aws.amazon.com/ec2/v2/home?region=us-east-1
2. Click `Create Key Pair`
3. Send PGP email

```
Here are info for running tests on Ec2
 * Access Key ID: 
 * Secret Access Key: 
 * aws_keyname = "piwik-xyz"
 * PEM file content is:
```
 
