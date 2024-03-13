/*!
 * Matomo - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0; // ignore ssl errors

const puppeteer = require('puppeteer');
const baseUrl = process.argv[2] || 'http://localhost/tests/javascript/';

const pluginArg = process.argv.find((arg) => /--plugin=(.*?)/.test(arg));
const plugin = pluginArg ?pluginArg.split('=', 2)[1] : null;

main();

async function main() {

    const browser = await puppeteer.launch({args: ['--no-sandbox', '--ignore-certificate-errors']});
    const page = await browser.newPage();

    page.on('console', async (consoleMessage) => {
        console.log("[" + consoleMessage.type()  + "] " + consoleMessage.text());
    });

    let url = baseUrl;
    if (plugin) {
      url += `?module=${encodeURIComponent(plugin)}`;
    }

    page.on('domcontentloaded', async () => {
      await page.evaluate(() => {
        window.testsDone = false;
        window.testsSuccessfull = false;

        QUnit.done(function (obj) {
          console.info("Tests passed: " + obj.passed);
          console.info("Tests failed: " + obj.failed);
          console.info("Total tests:  " + obj.total);
          console.info("Runtime (ms): " + obj.runtime);
          window.testsDone = true;
          window.testsSuccessfull = (obj.failed == 0);
        });

        QUnit.log(function (obj) {
          if (!obj.result) {
            var errorMessage = "Test failed in module " + obj.module + ": '" + obj.name + "' \nError: " + obj.message;

            if (obj.actual) {
              errorMessage += " \nActual: " + obj.actual;
            }

            if (obj.expected) {
              errorMessage += " \nExpected: " + obj.expected;
            }

            errorMessage += " \nSource: " + obj.source + "\n\n";

            console.info(errorMessage);
          }
        });
      });
    });

    await page.goto(url);
    await page.waitForFunction(() => !!window.testsDone, {timeout: 600000});

    var success = await page.evaluate(function() {
        return window.testsSuccessfull;
    });

    process.exit(success ? 0 : 1);
}
