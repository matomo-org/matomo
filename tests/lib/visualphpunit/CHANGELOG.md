# Changelog

## v2.0

* Overhaul the entire code base
* Give the UI a facelift
* Add ability to run tests using a phpunit.xml configuration file (GH-31)
* Add ability to generate test results from the command line (GH-32)

## v1.5.6

* Replace line breaks with <br>s instead of empty strings (GH-42)
* Fix jqueryFileTree folder selection for Macs (GH-41)
* Fix display of debugging output (GH-39)
* Add ability to set MySQL port (GH-37)

## v1.5.5

* Change require -> require_once to avoid errors (GH-34)
* Don't require files to share the same name as the test classes (GH-33)
* Fix output buffering (GH-23)

## v1.5.4

* Fix include_path issues (GH-26)

## v1.5.3

* Fix SANDBOX_IGNORE settings (GH-21)
* Update history file (GH-20)

## v1.5.2

* Add tooltips to compensate for colorblind usage problem (GH-17)
* Add ability to filter suite results (GH-14)

## v1.5.1

* Update color scheme
* Update snapshot list each time a test is run (GH-10)
* Fix snapshot filenames to be compatible with Windows systems (GH-11)
* Allow debug display of JSON within tests (GH-9)
* Fix POST locations to use relative URIs

## v1.5

* Add ability to generate graphs of test results

## v1.4.1

* Fix Windows path issues
* Add a progress bar to indicate that tests are being processed

## v1.4

* Overhaul the UI
* Fix issues with namespaced tests
* Implement a better check for archived files


## v1.3.2

* Add support for bootstraps
* Clean up the user interface
* Add the ability to view snapshots from the homepage
* Change the snapshot filename format to Y-m-d

## v1.3.1

* Allow for relative paths in TEST_DIRECTORY
* Use a better test loading mechanism

## v1.3

* Add a start page to allow for specific choosing of tests and options
* Add the ability to sort suite results by status and time
* Clean up some configuration settings
* Remove ability to save JSON snapshots

## v1.2

* Add statistic bars to display the suite results visually

## v1.1.1

* Fix to allow for loading a single test directly
* Adjust code to allow for proper execution with 'short_open_tag' off
* Fix to match test files with the word 'Test' at the end of the filename
* Fix to eliminate duplicate tests

## v1.1

* Add suite statistics

## v1.0

* Initial release
