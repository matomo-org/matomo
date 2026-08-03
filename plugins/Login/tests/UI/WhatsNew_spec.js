/*!
 * Matomo - free/libre analytics platform
 *
 * "What's New" panel on the refreshed login layout.
 *
 * DOM/security invariants only (no screenshot baselines): these hold regardless of the exact
 * production "changes" data, so they don't churn when changes.json is updated. Layout screenshots
 * for this page are covered by the shared login baselines, refreshed from CI artifacts.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("LoginWhatsNew", function () {
    const loginUrl = "?module=Login&action=login";

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    after(function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    it("should render the What's New panel beside the login form and centre both columns on desktop", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('.loginLayout__primary');

        const layout = await page.evaluate(function () {
            const centreOf = function (selector) {
                const rect = document.querySelector(selector).getBoundingClientRect();
                return rect.top + (rect.height / 2);
            };

            const form = document.querySelector('#login_form_login').getBoundingClientRect();
            const panel = document.querySelector('.loginWhatsNew').getBoundingClientRect();

            return {
                panelIsRightOfForm: panel.left >= form.right,
                // Both columns share the vertical centre of the layout (a couple of pixels of
                // rounding either way is fine).
                formOffCentre: Math.abs(centreOf('.loginLayout__content') - centreOf('.loginLayout')),
                panelOffCentre: Math.abs(centreOf('.loginLayout__secondary') - centreOf('.loginLayout')),
            };
        });

        expect(layout.panelIsRightOfForm).to.be.true;
        expect(layout.formOffCentre).to.be.below(3);
        expect(layout.panelOffCentre).to.be.below(3);
    });

    it("should render the sign in form without card chrome and flush with the logo", async function () {
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

    it("should show at most the three most recent entries", async function () {
        const count = await page.$$eval('.loginWhatsNew__entry', els => els.length);

        expect(count).to.be.above(0);
        expect(count).to.be.at.most(3);
    });

    it("should never expose internal/instance links as a call to action", async function () {
        const hrefs = await page.$$eval('.loginWhatsNew__link', els => els.map(el => el.getAttribute('href')));

        hrefs.forEach(function (href) {
            // Only absolute external http(s) links are allowed to render a CTA.
            expect(href).to.match(/^https?:(&#x2F;|\/){2}/);
            expect(href.toLowerCase()).to.not.contain('index.php');
        });
    });

    it("should open external call-to-action links safely in a new tab", async function () {
        const links = await page.$$eval('.loginWhatsNew__link', els => els.map(el => ({
            target: el.getAttribute('target'),
            rel: el.getAttribute('rel'),
        })));

        links.forEach(function (link) {
            expect(link.target).to.equal('_blank');
            expect(link.rel).to.contain('noreferrer');
            expect(link.rel).to.contain('noopener');
        });
    });

    it("should hide the panel and leave the form alone on tablet and mobile", async function () {
        const measure = function () {
            const de = document.documentElement;

            return {
                noOverflow: de.scrollWidth <= de.clientWidth,
                panelHidden: document.querySelector('.loginWhatsNew').offsetParent === null,
                formVisible: document.querySelector('#login_form_login').offsetParent !== null,
            };
        };

        await page.webpage.setViewport({ width: 992, height: 820 });
        await page.goto(loginUrl);
        await page.waitForSelector('#login_form_login');
        const tablet = await page.evaluate(measure);

        await page.webpage.setViewport({ width: 380, height: 820 });
        await page.goto(loginUrl);
        await page.waitForSelector('#login_form_login');
        const mobile = await page.evaluate(measure);

        [tablet, mobile].forEach(function (result) {
            expect(result.noOverflow).to.be.true;
            expect(result.panelHidden).to.be.true;
            expect(result.formVisible).to.be.true;
        });
    });
});
