# Screenshots UI tests

Piwik contains UI tests that compare captured screenshots of URLs and UI controls with expected screenshots.
If a captured screenshot does not match its expected screenshot, the build will fail. 

For a plugin developer documentation have a look here: https://developer.piwik.org/guides/tests-ui
The remaining part of this document is rather Piwik Core Developer related.

## Fixing a broken build 

Changes made to Piwik that affect the UI (such as changes to CSS, JavaScript, Twig templates or even PHP code) may
break the UI tests build. This is an opportunity to review your code and as a Piwik developer you should ensure that
any side effects created by your changes are correct.

If they are not correct, determine the cause of the change and fix it in a new commit. If the changes are correct,
then you should update the expected screenshots accordingly.

**Steps to fix a broken build**

To fix a broken build, follow these steps:

 * Go to the Tests travis build: [https://travis-ci.org/piwik/piwik](https://travis-ci.org/piwik/piwik) and select the build containing `TEST_SUITE=UITests`
 * Find the build you are interested in. The UI tests build will be run for each commit in each branch, so if you're
   looking to resolve a specific failure, you'll have to find the build for the commit you've made.
 * In the build output, at the beginning of the test output, there will be a link to a image diff viewer. It will look something
   like this:

       View UI failures (if any) here http://builds-artifacts.piwik.org/ui-tests.master/1837.1/screenshot-diffs/diffviewer.html

   Click on the link in the message.
 * The diff viewer will list links to the generated screenshots for failed tests as well as the expected screenshots and image diffs.
 * For each failure, check if the change is desired. Sometimes we introduce regression without realising, and screenshot tests can help us spot such regressions.
     * If a change is not wanted, revert or fix your commit.
     * If a change is correct, then you can set the new screenshot as the expected screenshot.
       To do so, in the diffviewer.html page click on the "Processed" link for this screenshot.
       Then "Save this file as" and save it in the piwik/tests/UI/expected-ui-screenshots/ directory.
       (If the screenshot test is for a plugin and not Piwik Core, the expected screenshot should be added to the
       plugin's expected screenshot directory. For example: piwik/plugins/DBStats/tests/UI/expected-ui-screenshots.)

     _Note: When determining whether a screenshot is correct, the data displayed is not important. Report data correctness is verified through System and other PHP tests. The UI tests should only test UI behavior._
 * Push the changes (to your code and/or to the expected-ui-screenshots directory.
 * Wait for next Test build [on travis](https://travis-ci.org/piwik/piwik). Hopefully, the build should be green!

_Note: the `tests:sync-ui-screenshots` console command can be used to speed up the process. Run `./console tests:sync-ui-screenshots -h` to learn more._

## <a name="run-tests"></a>Running Tests

You can test the UI by running the following command in the root piwik directory:

    $ ./console tests:run-ui

The following options may be useful if you plan on running the UI tests locally often:

 * **--persist-fixture-data**: This will save the test data in a separate database so the setup only has to be run once.
                               This can save 5 mins per screenshot test run.
 * **--drop**: If you've used --persist-fixture-data and need to re-setup the separate data, use this option with --persist-fixture-data.
 * **--keep-symlinks**: If you want to visit the URLs of captured pages in a browser to diagnose failures use this option.
                        This will keep the recursive symlinks in tests/PHPUnit/proxy.
