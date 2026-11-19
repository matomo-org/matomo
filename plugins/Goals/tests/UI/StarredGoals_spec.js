/*!
 * Matomo - free/libre analytics platform
 *
 * Starred goals UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("StarredGoals", function () {
    this.fixture = 'Piwik\\Plugins\\Goals\\tests\\Fixtures\\StarredGoals';

    // goal IDs of the fixture: starred goals first ("Demo request", "Goal 10"), then by name
    const starredFirstOrder = ['5', '3', '7', '2', '6', '4', '1'];
    const starredIds = ['5', '3'];

    const generalParams = 'idSite=1&period=day&date=2015-03-04';
    const reportingUrl = `?module=CoreHome&action=index&${generalParams}#?${generalParams}`;
    const manageGoalsUrl = `${reportingUrl}&category=Goals_Goals&subcategory=Goals_ManageGoals`;
    const overviewUrl = `${reportingUrl}&category=Goals_Goals&subcategory=General_Overview`;
    const dashboardUrl = `${reportingUrl}&category=Dashboard_Dashboard&subcategory=1`;
    const goalsTableUrl = `?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&${generalParams}`
        + '&actionToWidgetize=getReferrerType&viewDataTable=tableGoals&idGoal=0&isFooterExpandedInDashboard=1';

    const rowSelector = '.manageGoals .managegoals table tbody tr';

    // A hash-only goto does not reload under Chrome 149; navigate away first to isolate tests.
    async function goTo(url, waitForSelector) {
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForSelector(waitForSelector);
        await page.waitForNetworkIdle();
    }

    async function getGoalRows() {
        return page.$$eval(rowSelector, (rows) => rows.map((row) => {
            const star = row.querySelector('.starToggle');
            return {
                id: row.getAttribute('id'),
                starred: star.classList.contains('starToggle--starred'),
                tag: star.tagName.toLowerCase(),
                pressed: star.getAttribute('aria-pressed'),
            };
        }));
    }

    async function getStarTooltip(idGoal) {
        return page.evaluate((selector) => {
            const $star = $(selector);
            return $star.data('ui-tooltip-title') || $star.attr('title') || '';
        }, `${rowSelector}[id="${idGoal}"] .starToggle`);
    }

    async function clickStar(idGoal) {
        await page.click(`${rowSelector}[id="${idGoal}"] button.starToggle`);
        await page.waitForNetworkIdle();
    }

    async function getStarredFromApi() {
        return page.evaluate(async () => {
            const goals = await window.CoreHome.AjaxHelper.fetch({ method: 'Goals.getGoals', filter_limit: '-1' });
            return Object.values(goals).filter((goal) => goal.starred === true).map((goal) => `${goal.idgoal}`).sort();
        });
    }

    after(function () {
        delete testEnvironment.idSitesViewAccess;
        testEnvironment.save();
    });

    it("should show the star column first and list starred goals first", async function () {
        await goTo(manageGoalsUrl, rowSelector);

        const firstColumnClasses = await page.$$eval(
            '.manageGoals .managegoals table tr',
            (rows) => rows.map((row) => row.children[0].className),
        );
        firstColumnClasses.forEach((className) => expect(className).to.contain('entityTable_StarCell'));

        const rows = await getGoalRows();
        expect(rows.map((row) => row.id)).to.deep.equal(starredFirstOrder);
        expect(rows.filter((row) => row.starred).map((row) => row.id)).to.deep.equal(starredIds);

        // a user who can edit goals gets buttons that say whether they are pressed
        rows.forEach((row) => {
            expect(row.tag).to.equal('button');
            expect(row.pressed).to.equal(row.starred ? 'true' : 'false');
        });
    });

    it("should show a tooltip for both states of the star", async function () {
        expect(await getStarTooltip(5)).to.equal('Remove from Starred goals for this website.');
        expect(await getStarTooltip(1)).to.equal('Add to Starred goals for this website.');

        // the first goal that is not starred, so the tooltip below its star is inside the table
        await page.hover(`${rowSelector}[id="7"] .starToggle`);
        await page.waitForSelector('.ui-tooltip', { visible: true });

        expect(await page.screenshotSelector('.manageGoals .managegoals,.ui-tooltip')).to.matchImage('manage_goals_star_tooltip');
    });

    it("should star a goal right away, move it to the starred goals and keep it after a reload", async function () {
        await page.mouse.move(-10, -10);
        await clickStar(1);

        // "Newsletter" is sorted after the other starred goals
        let rows = await getGoalRows();
        expect(rows.map((row) => row.id)).to.deep.equal(['5', '3', '1', '7', '2', '6', '4']);
        expect(rows.find((row) => row.id === '1').pressed).to.equal('true');
        expect(await getStarTooltip(1)).to.equal('Remove from Starred goals for this website.');
        expect(await getStarredFromApi()).to.deep.equal(['1', '3', '5']);

        await goTo(manageGoalsUrl, rowSelector);

        rows = await getGoalRows();
        expect(rows.map((row) => row.id)).to.deep.equal(['5', '3', '1', '7', '2', '6', '4']);
    });

    it("should unstar a goal right away and move it back", async function () {
        await clickStar(1);

        const rows = await getGoalRows();
        expect(rows.map((row) => row.id)).to.deep.equal(starredFirstOrder);
        expect(rows.find((row) => row.id === '1').pressed).to.equal('false');
        expect(await getStarTooltip(1)).to.equal('Add to Starred goals for this website.');
        expect(await getStarredFromApi()).to.deep.equal(['3', '5']);
    });

    it("should be possible to toggle the star with the keyboard", async function () {
        await page.focus(`${rowSelector}[id="1"] button.starToggle`);
        await page.keyboard.press('Enter');
        await page.waitForNetworkIdle();
        expect(await getStarredFromApi()).to.deep.equal(['1', '3', '5']);

        await page.focus(`${rowSelector}[id="1"] button.starToggle`);
        await page.keyboard.press('Space');
        await page.waitForNetworkIdle();
        expect(await getStarredFromApi()).to.deep.equal(['3', '5']);
    });

    it("should list starred goals first in the reporting menu and mark them", async function () {
        // the menu is reloaded shortly after the last star was saved
        await page.waitForFunction(() => {
            const items = [...document.querySelectorAll('li.menuTab.active .menuDropdown .items a.item')];
            const starred = items.filter((item) => item.querySelector('.starToggle--starred'));
            return starred.length === 2 && items.indexOf(starred[1]) === 1;
        });

        const menuGoals = await page.$$eval('li.menuTab.active .menuDropdown .items a.item', (links) => links.map((link) => ({
            name: link.textContent.trim(),
            starred: !!link.querySelector('.starToggle--starred'),
            tooltip: link.querySelector('.starToggle') ? link.querySelector('.starToggle').getAttribute('title') : '',
        })));

        expect(menuGoals.map((goal) => goal.name)).to.deep.equal([
            'Demo request', 'Goal 10', 'apple pay', 'contact form', 'Download', 'Goal 2', 'Newsletter',
        ]);
        expect(menuGoals.filter((goal) => goal.starred).map((goal) => goal.name)).to.deep.equal(['Demo request', 'Goal 10']);
        expect(menuGoals[0].tooltip).to.equal('Starred goal');
    });

    it("should list starred goals first on the goals overview and mark their titles", async function () {
        await goTo(overviewUrl, 'h2[goal-page-link]');
        await page.waitForSelector('h2[goal-page-link] .starToggle');

        const titles = await page.$$eval('h2[goal-page-link]', (elements) => elements.map((element) => ({
            id: element.getAttribute('goal-page-link'),
            starred: !!element.querySelector('.starToggle--starred'),
            tooltip: element.querySelector('.starToggle') ? element.querySelector('.starToggle').getAttribute('title') : '',
            isButton: !!element.querySelector('button.starToggle'),
        })));

        expect(titles.map((title) => title.id)).to.deep.equal(starredFirstOrder);
        expect(titles.filter((title) => title.starred).map((title) => title.id)).to.deep.equal(starredIds);
        expect(titles[0].tooltip).to.equal('Starred goal');
        // the star can only be changed on Manage Goals
        titles.forEach((title) => expect(title.isButton).to.equal(false));
    });

    it("should show the columns of starred goals first in the goals table and mark their headers", async function () {
        await page.goto('about:blank');
        await page.goto(goalsTableUrl);
        await page.waitForSelector('table.dataTable th[data-starred-goal] .starToggle');
        await page.waitForNetworkIdle();

        const columns = await page.$$eval('table.dataTable thead th', (headers) => headers.map((header) => ({
            id: header.getAttribute('id'),
            starred: !!header.querySelector('.starToggle--starred'),
        })));

        const conversionColumns = columns.filter((column) => /^goal_\d+_nb_conversions$/.test(column.id));
        expect(conversionColumns.map((column) => column.id.split('_')[1])).to.deep.equal(starredFirstOrder);
        expect(conversionColumns.filter((column) => column.starred).map((column) => column.id.split('_')[1]))
            .to.deep.equal(starredIds);

        // every column of a starred goal is marked, no other column is
        columns.forEach((column) => {
            const match = /^goal_(\d+)_/.exec(column.id);
            expect(column.starred).to.equal(!!match && starredIds.includes(match[1]), column.id);
        });

        // The table with the columns of all goals is wider than the viewport and scrolls horizontally,
        // so widen the viewport to capture every column header.
        const fullWidth = await page.evaluate(() => {
            const scroller = document.querySelector('.dataTableScroller');
            const table = document.querySelector('table.dataTable');
            return Math.ceil(Math.max(scroller ? scroller.scrollWidth : 0, table ? table.scrollWidth : 0));
        });
        await page.webpage.setViewport({ width: fullWidth + 40, height: 768 });
        await page.waitForNetworkIdle();

        const header = await page.$('table.dataTable thead');
        const screenshot = await header.screenshot();
        await page.webpage.setViewport({ width: 1350, height: 768 });
        expect(screenshot).to.matchImage('goals_table_header');
    });

    it("should list the widgets of starred goals first in the widget selector and mark them", async function () {
        await goTo(dashboardUrl, '.dashboard-manager .title');

        const modalSelector = '.modal.open.add-widget-modal';
        await page.click('.dashboard-manager .title');
        await page.click('button[data-action="addWidget"]');
        await page.waitForSelector(modalSelector + ' .widgetpreview-categorylist>li button');

        const goalsCategory = await page.jQuery(modalSelector + ' .widgetpreview-categorylist>li button:contains(Goals):first');
        await goalsCategory.click();
        await page.waitForSelector(modalSelector + ' .widgetpreview-widgetlist>li .widgetpreview-widgetname .starToggle');

        const widgets = await page.$$eval('.widgetpreview-widgetlist li', (items) => items.map((item) => ({
            uniqueId: item.getAttribute('uniqueid'),
            starred: !!item.querySelector('.starToggle--starred'),
        })));

        const goalWidgets = widgets.filter((widget) => /^widgetGoal_\d+$/.test(widget.uniqueId));
        expect(goalWidgets.map((widget) => widget.uniqueId.replace('widgetGoal_', ''))).to.deep.equal(starredFirstOrder);
        expect(goalWidgets.filter((widget) => widget.starred).map((widget) => widget.uniqueId.replace('widgetGoal_', '')))
            .to.deep.equal(starredIds);
        expect(widgets.filter((widget) => widget.starred).length).to.equal(starredIds.length);
    });

    it("should show the stars to a user with view access without letting them change a star", async function () {
        testEnvironment.idSitesViewAccess = [1];
        testEnvironment.save();

        await goTo(manageGoalsUrl, rowSelector);

        const rows = await getGoalRows();
        expect(rows.map((row) => row.id)).to.deep.equal(starredFirstOrder);
        expect(rows.filter((row) => row.starred).map((row) => row.id)).to.deep.equal(starredIds);
        rows.forEach((row) => {
            expect(row.tag).to.equal('span');
            expect(row.pressed).to.equal(null);
        });

        expect(await getStarTooltip(5)).to.equal('Starred goal');
        expect(await getStarTooltip(1)).to.equal('');

        const cursor = await page.$eval(`${rowSelector}[id="1"] .starToggle`, (star) => window.getComputedStyle(star).cursor);
        expect(cursor).to.equal('default');

        await page.click(`${rowSelector}[id="1"] .starToggle`);
        await page.click(`${rowSelector}[id="5"] .starToggle`);
        await page.waitForNetworkIdle();

        expect((await getGoalRows()).filter((row) => row.starred).map((row) => row.id)).to.deep.equal(starredIds);

        await page.mouse.move(-10, -10);
        expect(await page.screenshotSelector('.manageGoals .managegoals')).to.matchImage('manage_goals_view_user');

        delete testEnvironment.idSitesViewAccess;
        testEnvironment.save();

        // nothing was saved
        await goTo(manageGoalsUrl, rowSelector);
        expect(await getStarredFromApi()).to.deep.equal(['3', '5']);
    });
});
