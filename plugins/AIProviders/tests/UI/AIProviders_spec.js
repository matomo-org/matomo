/*!
 * Matomo - free/libre analytics platform
 *
 * AIProviders UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("AIProviders", function () {
    this.fixture = 'Piwik\\Plugins\\AIProviders\\tests\\Fixtures\\ConfiguredProviders';

    const providersUrl = "?module=AIProviders&action=index&idSite=1&period=day&date=yesterday";
    const processingUrl = "?module=AIProviders&action=aiProcessing&idSite=1&period=day&date=yesterday";
    const cards = '.ai-providers-card';
    const saveButton = '.ai-providers-footer .matomo-save-button input';

    async function open(url, selector) {
        // a plain goto to the same url only changes the hash, so leave the page to really reload it
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForSelector(selector);
        await page.waitForNetworkIdle();
    }

    async function countAll(selector) {
        return (await page.$$(selector)).length;
    }

    // the card of a provider, found by its name
    function card(name) {
        return `xpath///*[contains(@class, "ai-providers-card") and @role="radio"][.//*[contains(@class, "ai-providers-card-name") and normalize-space()="${name}"]]`;
    }

    async function cardState(name) {
        const el = await page.$(card(name));
        return {
            selected: await el.evaluate((e) => e.getAttribute('aria-checked') === 'true'),
            connected: !!(await el.$('.ai-providers-card-status.is-connected')),
        };
    }

    describe("Settings", function () {
        it("should show every provider with its connection state and the default", async function () {
            await open(providersUrl, cards);

            expect(await countAll(cards)).to.be.above(2);
            expect(await cardState('OpenAI')).to.eql({ selected: false, connected: true });
            expect(await cardState('Anthropic')).to.eql({ selected: true, connected: true });
            expect(await countAll('.ai-providers-card-default')).to.equal(1);
            expect(await countAll('.ai-providers-default-warning')).to.equal(0);
        });

        it("should render the providers page", async function () {
            expect(await page.screenshotSelector('.ai-providers-page')).to.matchImage('page');
        });

        it("should flag unsaved changes and discard them on cancel", async function () {
            await (await page.$(card('OpenAI'))).click();
            await page.waitForSelector('.ai-providers-unsaved-changes.is-visible');

            expect(await cardState('OpenAI')).to.eql({ selected: true, connected: true });
            expect(await cardState('Anthropic')).to.eql({ selected: false, connected: true });

            await page.click('.ai-providers-footer button');
            await page.waitForSelector('.ai-providers-unsaved-changes:not(.is-visible)');
            expect(await cardState('Anthropic')).to.eql({ selected: true, connected: true });
        });

        it("should save a new default provider and capability level", async function () {
            await (await page.$(card('OpenAI'))).click();
            await page.click('input[name=defaultCapabilityLevel][value=thinking]');
            await page.click(saveButton);
            await page.waitForNetworkIdle();

            await open(providersUrl, cards);
            expect(await cardState('OpenAI')).to.eql({ selected: true, connected: true });
            expect(await page.$eval('input[name=defaultCapabilityLevel]:checked', (el) => el.value)).to.equal('thinking');
        });

        it("should disconnect a provider", async function () {
            await (await (await page.$(card('OpenAI'))).$('.ai-providers-card-actions button:last-child')).click();
            await page.waitForSelector('.notification-success');

            // the default falls back to the remaining connected provider
            expect(await cardState('OpenAI')).to.eql({ selected: false, connected: false });
            expect(await cardState('Anthropic')).to.eql({ selected: true, connected: true });
        });
    });

    describe("Processing", function () {
        const switches = '.ai-processing-category input[type=checkbox]';
        const processingSave = '.ai-processing-footer .matomo-save-button input';

        async function checked() {
            return page.$$eval(switches, (els) => els.map((el) => el.checked));
        }

        it("should show the data categories and which one is enabled", async function () {
            await open(processingUrl, '.ai-processing-category');

            expect(await checked()).to.eql([false, true]);
            expect(await page.$eval(processingSave, (el) => el.disabled)).to.equal(true);
        });

        it("should render the AI processing page", async function () {
            expect(await page.screenshotSelector('.ai-processing')).to.matchImage('page');
        });

        it("should save a changed category", async function () {
            await page.click('.ai-processing-category .switch label');
            await page.click(processingSave);
            await page.waitForNetworkIdle();

            await open(processingUrl, '.ai-processing-category');
            expect(await checked()).to.eql([true, true]);
        });

        it("should link back only to a relative Matomo url", async function () {
            await open(`${processingUrl}&returnTo=index.php%3Fmodule%3DCoreHome`, '.ai-processing-back');
            expect(await page.$eval('.ai-processing-back', (el) => el.getAttribute('href'))).to.equal('index.php?module=CoreHome');

            await open(`${processingUrl}&returnTo=https%3A%2F%2Fexample.com`, '.ai-processing-category');
            expect(await countAll('.ai-processing-back')).to.equal(0);
        });
    });
});
