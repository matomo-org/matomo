/*!
 * Matomo - free/libre analytics platform
 *
 * Recommended goals UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RecommendGoals", function () {
    this.fixture = 'Piwik\\Plugins\\Goals\\tests\\Fixtures\\GoalRecommendationsSaved';

    const manageGoalsUrl = "?module=CoreHome&action=index&idSite=1&period=year&date=2009-01-01#?idSite=1&period=year&date=2009-01-01&category=Goals_Goals&subcategory=Goals_ManageGoals";
    const cards = '.recommendGoals .recommendGoals-card';

    async function reloadManageGoals() {
        // a plain goto to the same url only changes the hash, so leave the page to really reload it
        await page.goto('about:blank');
        await page.goto(manageGoalsUrl);
        await page.waitForNetworkIdle();
    }

    async function openManageGoals() {
        await reloadManageGoals();
        await page.waitForSelector(cards);
    }

    async function countCards(selector = cards) {
        return (await page.$$(selector)).length;
    }

    before(function () {
        testEnvironment.overrideConfig('FeatureFlags', {
            GoalRecommendations_feature: 'enabled',
        });
        testEnvironment.save();
    });

    it("should show the saved recommendations as cards", async function () {
        await openManageGoals();

        // 7 saved suggestions, only the 5 strongest are shown at first
        expect(await countCards()).to.equal(5);
        expect(await page.$eval('.recommendGoals-showMore', (el) => el.textContent.trim())).to.contain('2');
        expect(await page.$('.recommendGoals-manual')).to.be.ok;
        expect(await page.$('.recommendGoals-meta')).to.be.ok;
    });

    it("should render the scan results", async function () {
        // the relative "last scanned" time changes between runs
        await page.$eval('.recommendGoals-meta', (el) => el.style.visibility = 'hidden');

        expect(await page.screenshotSelector('.recommendGoals')).to.matchImage('scan_results');
    });

    it("should reveal the remaining suggestions and the needs-setup chip", async function () {
        await page.click('.recommendGoals-showMore');
        await page.waitForFunction(() => document.querySelectorAll('.recommendGoals .recommendGoals-card').length === 7);

        expect(await page.$('.recommendGoals-showMore')).to.equal(null);
        expect(await countCards('.recommendGoals-card .recommendGoals-chip--setup')).to.equal(1);
    });

    it("should show why a suggestion was made", async function () {
        expect(await page.$eval(`${cards}:first-child .recommendGoals-cardReason`, (el) => el.textContent)).to.contain('ready to convert');

        // 6.x shows the reason inline and keeps only the setup note in a drawer
        const details = await page.$('.recommendGoals-card .recommendGoals-evidence');
        expect(await details.evaluate((el) => el.open)).to.equal(false);

        await page.click('.recommendGoals-card .recommendGoals-evidence summary');
        expect(await details.evaluate((el) => el.textContent)).to.contain('newsletter_submit');
    });

    it("should create a single recommended goal", async function () {
        const goalsBefore = await countCards('div.manageGoals table.entityTable tbody tr');

        await page.click(`${cards}:first-child .recommendGoals-cardActions .btn`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`${cards}--added`);

        expect(await countCards(`${cards}--added`)).to.equal(1);
        expect(await countCards('div.manageGoals table.entityTable tbody tr')).to.equal(goalsBefore + 1);
        expect(await page.$('.notification.notification-success')).to.be.ok;
    });

    it("should dismiss a single suggestion", async function () {
        const before = await countCards();

        await page.click(`${cards}:not(.recommendGoals-card--added) .recommendGoals-dismissBtn`);
        await page.waitForNetworkIdle();
        await page.waitForFunction((count) => document.querySelectorAll('.recommendGoals .recommendGoals-card').length === count, {}, before - 1);

        // the dismissal is persisted, so it survives a reload (6 left, 5 shown, 1 behind "show more")
        await openManageGoals();
        expect(await countCards()).to.equal(5);
        expect(await page.$eval('.recommendGoals-showMore', (el) => el.textContent.trim())).to.contain('1');
    });

    it("should prefill the goal form from a manual suggestion", async function () {
        await page.click('.recommendGoals-manual summary');
        await page.click('.recommendGoals-manualList li button');
        await page.waitForSelector('.addEditGoal', { visible: true });

        const name = await page.$eval('#goal_name', (el) => el.value);
        expect(name).to.contain('Video watched');
    });

    it("should dismiss all suggestions and offer a fresh scan", async function () {
        await openManageGoals();

        await page.click('.recommendGoals-actions .btn-flat');
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !document.querySelector('.recommendGoals .recommendGoals-card'));

        expect(await page.$('.recommendGoals-run')).to.be.ok;
        expect(await page.$('.recommendGoals-meta')).to.equal(null);

        // the dismissal is persisted, so no suggestions come back after a reload
        await reloadManageGoals();
        await page.waitForSelector('.recommendGoals-run');
        expect(await countCards()).to.equal(0);
    });
});
