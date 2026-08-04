/*!
 * Matomo - free/libre analytics platform
 *
 * Refreshed login layout: the logo, sign in form and footer links sit in a single centred
 * column that fills the page.
 *
 * DOM/geometry invariants only, no baselines: these hold whatever the installation has
 * recorded, so they don't churn. The page's visual baselines live with the login screenshots.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("LoginLayout", function () {
    const loginUrl = "?module=Login&action=login";

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    after(function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it("should render the sign in form without card chrome and flush with the logo", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('.loginLayout__primary');

        const form = await page.evaluate(function () {
            const styles = getComputedStyle(document.querySelector('.loginLayout__content .card'));
            const leftOf = function (selector) {
                return document.querySelector(selector).getBoundingClientRect().left;
            };

            return {
                borderTopWidth: styles.borderTopWidth,
                borderLeftWidth: styles.borderLeftWidth,
                // Logo, heading, labels and every field share one left edge — no Materialize gutter.
                offsets: [
                    '.loginLayout__content .card-title',
                    'label[for="login_form_login"]',
                    '#login_form_login',
                    'label[for="login_form_password"]',
                    '#login_form_password',
                    '#login_form_submit',
                ].map(function (selector) {
                    return Math.abs(leftOf(selector) - leftOf('.loginLayout__logo'));
                }),
            };
        });

        expect(form.borderTopWidth).to.equal('0px');
        expect(form.borderLeftWidth).to.equal('0px');
        form.offsets.forEach(function (offset) {
            expect(offset).to.be.below(1);
        });
    });

    it("should centre the form vertically in the page", async function () {
        const offCentre = await page.evaluate(function () {
            const centreOf = function (selector) {
                const rect = document.querySelector(selector).getBoundingClientRect();
                return rect.top + (rect.height / 2);
            };

            // A couple of pixels of rounding either way is fine.
            return Math.abs(centreOf('.loginLayout__content') - centreOf('.loginLayout'));
        });

        expect(offCentre).to.be.below(3);
    });

    it("should keep the form visible and the page free of overflow on tablet and mobile", async function () {
        const measure = function () {
            const de = document.documentElement;

            return {
                noOverflow: de.scrollWidth <= de.clientWidth,
                formVisible: document.querySelector('#login_form_login').offsetParent !== null,
            };
        };

        await page.webpage.setViewport({ width: 720, height: 820 });
        await page.goto(loginUrl);
        await page.waitForSelector('#login_form_login');
        const tablet = await page.evaluate(measure);

        await page.webpage.setViewport({ width: 380, height: 820 });
        await page.goto(loginUrl);
        await page.waitForSelector('#login_form_login');
        const mobile = await page.evaluate(measure);

        [tablet, mobile].forEach(function (result) {
            expect(result.noOverflow).to.be.true;
            expect(result.formVisible).to.be.true;
        });
    });
});
