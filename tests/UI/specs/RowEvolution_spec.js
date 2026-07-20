/*!
 * Matomo - free/libre analytics platform
 *
 * row evolution screenshot tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RowEvolution", function () {
    const viewDataTableUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=week&date=2012-02-09&"
                         + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5";

    const ecommerceItemReportWidgetized = "?module=Widgetize&action=iframe&moduleToWidgetize=Goals&actionToWidgetize=getItemsSku&idGoal=ecommerceAbandonedCart"
                                      + "&idSite=1&period=year&date=2012-02-09&viewDataTable=ecommerceAbandonedCart&filter_limit=-1";

    const waitForRowEvolutionAnnotations = async () => {
        await page.waitForFunction("$('.ui-dialog .evolution-annotations > span').length > 0");
    };

    const setThemeMode = async (themeMode) => {
        await page.evaluate((mode) => {
            window.piwik.setThemeMode(mode);
        }, themeMode);
        await page.waitForFunction((mode) => window.piwik.getThemeMode() === mode, {}, themeMode);
    };

    it('should load when icon clicked in ViewDataTable', async function() {
        await page.goto(viewDataTableUrl);
        await page.waitForSelector('tbody tr:first-child')
        const row = await page.jQuery('tbody tr:contains("corruption")');
        await row.hover();

        const icon = await page.jQuery('tbody tr:contains("corruption") a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();
        await waitForRowEvolutionAnnotations();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution');
    });

    it('should change the metric shown when a metric sparkline row is clicked', async function() {
        await page.click('table.metrics tr[data-i="1"]');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_other_metric');
    });

    it('should show two serieses when a metric sparkline row is shift+clicked', async function() {
        await page.keyboard.down('Shift');
        await page.click('table.metrics tr[data-i="2"]', ['shift']);
        await page.keyboard.up('Shift');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_multiple_series');
    });

    it('should load multi-row evolution correctly', async function() {
        await page.evaluate(function() {
            $('.rowevolution-startmulti').click();
        });
        await page.waitForFunction("$('.ui-dialog').length === 0");

        const row = await page.waitForSelector('tbody tr:nth-child(2)');
        await row.hover();

        const icon = await page.waitForSelector('tbody tr:nth-child(2) a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        await page.waitForNetworkIdle();
        expect(await dialog.screenshot()).to.matchImage('multirow_evolution');
    });

    it('should display a different row evolution metric when the metric selection is changed', async function() {
        await page.evaluate(function () {
            $('select.multirowevoltion-metric').val($('select.multirowevoltion-metric option:nth-child(3)').val()).change();
        });

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('multirow_evolution_other_metric');
    });

    it('should load row evolution for goals view', async function() {
        await page.goto(viewDataTableUrl + '&forceView=1&viewDataTable=tableGoals');

        await page.waitForSelector('tbody tr:first-child')
        const row = await page.jQuery('tbody tr:contains("corruption")');
        await row.hover();

        const icon = await page.jQuery('tbody tr:contains("corruption") a.actionRowEvolution');
        await icon.click();

        const dialog = await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();
        expect(await dialog.screenshot()).to.matchImage('row_evolution_goal_view');
    });

    it('should load row evolution with goal metrics again when reloading the page url', async function() {
        // page.reload() won't work with url hashes
        const url = await page.evaluate('location.href');
        await page.goto('about:blank');
        await page.goto(url);

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_goal_view_reload');
    });

    it('should display row evolution for an ecommerce item report correctly', async function() {
        await page.goto(ecommerceItemReportWidgetized);
        const row = await page.waitForSelector('tbody tr:first-child');
        await row.hover();

        const icon = await page.waitForSelector('tbody tr:first-child a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog', { visible: true });
        await page.waitForNetworkIdle();
        await waitForRowEvolutionAnnotations();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution_ecommerce_item');
    });

    it('should keep row evolution open when the theme changes live', async function() {
        await page.goto(viewDataTableUrl);
        await setThemeMode('light');

        const row = await page.jQuery('tbody tr:contains("corruption")');
        await row.hover();
        const icon = await page.jQuery('tbody tr:contains("corruption") a.actionRowEvolution');
        await icon.click();

        await setThemeMode('dark');
        await waitForRowEvolutionAnnotations();

        const dialogCount = await page.evaluate(() => $('.ui-dialog').length);
        const hasRowEvolution = await page.evaluate(() => $('.ui-dialog .rowevolution').length);
        expect(dialogCount).to.be.equal(1);
        expect(hasRowEvolution).to.be.equal(1);

        await setThemeMode('light');
    });

    it('refuses to steer the popover request to a different module/action', async function() {
        const attackerJson = JSON.stringify({
            module: 'CoreAdminHome',
            action: 'setMailSettings',
            mailHost: 'attacker.example',
            force_api_session: 0,
            format: 'json'
        });
        const popoverPayload = 'RowAction:RowEvolution:Actions.getPageUrls:'
            + encodeURIComponent(attackerJson) + ':attacker-label';
        const hashValue = encodeURIComponent(popoverPayload).replace(/%/g, '$');
        const attackUrl = '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday'
            + '#?popover=' + hashValue;

        const popoverRequests = [];
        let listening = true;
        const onRequest = (req) => {
            if (!listening) {
                return;
            }
            const url = req.url();
            if (url.indexOf('apiMethod=Actions.getPageUrls') !== -1) {
                popoverRequests.push(url);
            }
        };
        page.on('request', onRequest);

        try {
            await page.goto('about:blank');
            await page.goto(attackUrl);
            await page.waitForNetworkIdle();
        } finally {
            listening = false;
        }

        expect(popoverRequests.length).to.be.above(0);
        popoverRequests.forEach((url) => {
            expect(url).to.contain('module=CoreHome');
            expect(url).to.contain('action=getRowEvolutionPopover');
            expect(url).to.not.contain('module=CoreAdminHome');
            expect(url).to.not.contain('action=setMailSettings');
            expect(url).to.not.contain('force_api_session=0');
            expect(url).to.not.match(/[?&]format=json(&|$)/);
            expect(url).to.not.contain('mailHost=');
        });
    });
});
