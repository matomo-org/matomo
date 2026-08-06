/*!
 * Matomo - free/libre analytics platform
 *
 * "What's New" panel on the refreshed login layout: mostly DOM/security invariants, plus one
 * baseline of the panel itself. Entries come from the WhatsNewChanges fixture, so they are stable.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("LoginWhatsNew", function () {
    this.fixture = 'Piwik\\Plugins\\Login\\tests\\Fixtures\\WhatsNewChanges';

    const loginUrl = "?module=Login&action=login";

    // Pages that only need to show the layout holds assert geometry instead of taking a baseline.
    const panelBesideForm = { entries: 3, panelRightOfForm: true, panelVisible: true, noOverflow: true };

    function measurePanelBesideForm() {
        const de = document.documentElement;
        const primary = document.querySelector('.loginLayout__primary').getBoundingClientRect();
        const panel = document.querySelector('.loginWhatsNew').getBoundingClientRect();

        return {
            entries: document.querySelectorAll('.loginWhatsNew__entry').length,
            panelRightOfForm: panel.left >= primary.right,
            panelVisible: panel.width > 0 && panel.height > 0,
            noOverflow: de.scrollWidth <= de.clientWidth,
        };
    }

    before(function () {
        testEnvironment.testUseMockAuth = 0;
        // Swaps FakeChangesModel, which reads back nothing, for the real one.
        testEnvironment.loadChanges = 1;
        testEnvironment.save();
    });

    after(function () {
        testEnvironment.testUseMockAuth = 1;
        delete testEnvironment.loadChanges;
        testEnvironment.save();
    });

    it("should render the login page with the What's New panel", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('.loginWhatsNew__entry');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('panel');
    });

    it("should show the three most recent entries and drop the older one", async function () {
        const titles = await page.$$eval('.loginWhatsNew__title', els => els.map(el => el.innerText.trim()));

        expect(titles).to.deep.equal([
            'An entry with an external call to action',
            'An entry linking back to this instance',
            'An entry without a call to action',
        ]);
    });

    it("should keep the external call to action and strip the internal one", async function () {
        const entries = await page.$$eval('.loginWhatsNew__entry', els => els.map(el => {
            const link = el.querySelector('.loginWhatsNew__link');
            return link === null ? null : link.getAttribute('href');
        }));

        expect(entries[0]).to.match(/^https:(&#x2F;|\/){2}matomo\.org(&#x2F;|\/)changelog/);
        expect(entries[1]).to.equal(null, 'the internal link must not render a call to action');
        expect(entries[2]).to.equal(null);
    });

    it("should render the What's New panel beside the login form on desktop", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('.loginLayout__primary');

        const panelIsRightOfForm = await page.evaluate(function () {
            const form = document.querySelector('#login_form_login').getBoundingClientRect();
            const panel = document.querySelector('.loginWhatsNew').getBoundingClientRect();

            return panel.left >= form.right;
        });

        expect(panelIsRightOfForm).to.be.true;
    });

    // Same invariants LoginLayout_spec asserts without the panel, re-checked against the narrower
    // primary column the secondary one leaves behind.
    it("should keep the sign in form flush with the logo with the panel beside it", async function () {
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

    it("should only render call-to-action links on an allowed host", async function () {
        const hrefs = await page.$$eval('.loginWhatsNew__link', els => els.map(el => el.getAttribute('href')));

        hrefs.forEach(function (href) {
            // Only absolute http(s) links on matomo.org (or a subdomain of it) may render a CTA.
            expect(href).to.match(/^https?:(&#x2F;|\/){2}([a-z0-9-]+\.)*matomo\.org([:/]|$)/);
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

    // The other shape the primary column takes for a logged out visitor. Kept ahead of the
    // responsive test below, which shrinks the viewport.
    it("should render the forgot password form beside the panel", async function () {
        await page.webpage.setViewport({ width: 1440, height: 900 });
        await page.goto(loginUrl);
        await page.waitForSelector('a#login_form_nav');
        await page.click('a#login_form_nav');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.loginWhatsNew__entry');

        expect(await page.evaluate(measurePanelBesideForm)).to.deep.equal(panelBesideForm);
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

        // The panel is dropped at `max-width: 760px` (login.less), so 720 is inside that range.
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
            expect(result.panelHidden).to.be.true;
            expect(result.formVisible).to.be.true;
        });
    });
});
