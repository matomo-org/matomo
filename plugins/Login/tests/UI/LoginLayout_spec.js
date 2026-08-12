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

    it("should reach the bottom of the viewport and centre the column's content vertically", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('.loginLayout__primary');

        const layout = await page.evaluate(function () {
            const primary = document.querySelector('.loginLayout__primary');
            const styles = getComputedStyle(primary);
            const box = primary.getBoundingClientRect();

            // The logo, the form and the footer links are centred as one stack, so the free
            // space above the first of them has to match the space left below the last.
            const visible = Array.prototype.filter.call(primary.children, function (el) {
                return el.offsetParent !== null;
            });
            const first = visible[0].getBoundingClientRect();
            const last = visible[visible.length - 1].getBoundingClientRect();

            return {
                layoutBottom: document.querySelector('.loginLayout').getBoundingClientRect().bottom,
                viewportHeight: document.documentElement.clientHeight,
                spaceAbove: first.top - (box.top + parseFloat(styles.paddingTop)),
                spaceBelow: (box.bottom - parseFloat(styles.paddingBottom)) - last.bottom,
            };
        });

        // Reaching the bottom edge is the real requirement. Comparing heights instead would
        // break the moment anything else is added to the page above the layout, without the
        // layout itself being wrong.
        expect(layout.viewportHeight - layout.layoutBottom).to.be.below(4);
        // Guards against the assertion below passing when there is no free space to share.
        expect(layout.spaceAbove).to.be.above(0);
        expect(Math.abs(layout.spaceAbove - layout.spaceBelow)).to.be.below(2);
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
