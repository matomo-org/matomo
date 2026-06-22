/*!
 * Matomo - free/libre analytics platform
 *
 * evolution graph screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EvolutionGraph", function () {
    const url = "?module=Widgetize&action=iframe&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30"
              + "&moduleToWidgetize=UserCountry&actionToWidgetize=getCountry&viewDataTable=graphEvolution"
              + "&isFooterExpandedInDashboard=1";
    const plotLinesTweaksColumns = "nb_visits,nb_actions,avg_time_on_site,bounce_rate";
    const plotLinesTweaksUrl = url + "&columns=" + plotLinesTweaksColumns + "&filter_add_columns_when_show_all_columns=0";
    const setThemeMode = async function (themeMode) {
        await page.evaluate((mode) => {
            window.piwik.setThemeMode(mode);
        }, themeMode);
        await page.waitForFunction((mode) => window.piwik.getThemeMode() === mode, {}, themeMode);
    };
    const getResolvedBackgroundColor = async function (selector) {
        return page.evaluate(function (targetSelector) {
            var target = document.querySelector(targetSelector);
            var dataTable = target && target.closest('.dataTable');
            var uiControlObject = dataTable ? $(dataTable).data('uiControlObject') : null;
            var configuredColor = (uiControlObject
                && uiControlObject.jqplotParams
                && uiControlObject.jqplotParams.grid
                && uiControlObject.jqplotParams.grid.background)
                || '#ffffff';
            var colorProbe = document.createElement('div');

            colorProbe.style.display = 'none';
            colorProbe.style.color = configuredColor;
            document.body.appendChild(colorProbe);

            var resolvedColor = window.getComputedStyle(colorProbe).color;
            colorProbe.remove();

            return resolvedColor || 'rgb(255, 255, 255)';
        }, selector);
    };
    const getImagePixelColor = async function (selector, offset) {
        return page.evaluate(async function (targetSelector, pixelOffset) {
            const exportImage = document.querySelector(targetSelector);
            const image = new Image();
            image.src = exportImage.src;

            await new Promise((resolve, reject) => {
                image.onload = resolve;
                image.onerror = reject;
            });

            const canvas = document.createElement('canvas');
            canvas.width = image.width;
            canvas.height = image.height;

            const context = canvas.getContext('2d');
            context.drawImage(image, 0, 0);

            const pixel = context.getImageData(
                image.width - pixelOffset,
                image.height - pixelOffset,
                1,
                1
            ).data;

            return 'rgb(' + pixel[0] + ', ' + pixel[1] + ', ' + pixel[2] + ')';
        }, selector, offset);
    };
    const getFooterLegendState = async function () {
        return page.evaluate(function () {
            const footer = document.querySelector('.jqplot-legend-footer.has-legend');
            const items = footer ? footer.querySelectorAll('.jqplot-legend-item') : [];
            const hiddenItems = footer ? footer.querySelectorAll('.jqplot-legend-item-hidden') : [];
            const overflowItem = footer ? footer.querySelector('.jqplot-legend-item-overflow .jqplot-legend-label') : null;

            return {
                hasLegend: !!footer,
                itemCount: items.length,
                hiddenItemCount: hiddenItems.length,
                visibleItemCount: footer ? footer.querySelectorAll('.jqplot-legend-item:not(.jqplot-legend-item-hidden)').length : 0,
                overflowLabel: overflowItem ? overflowItem.textContent.trim() : null,
            };
        });
    };

    before(function () {
        return testEnvironment.callApi("Annotations.deleteAll", {idSite: 3});
    });

    afterEach(async function () {
        await setThemeMode('light');
    });

    it("should load correctly", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });

    it("should show percent metrics like bounce rate correctly", async function () {
        await page.goto(url + "&columns=nb_visits,bounce_rate,avg_time_on_site&filter_add_columns_when_show_all_columns=0");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('bounce_rate');
    });

    it("should show only one series when a label is specified", async function () {
        await page.goto(url + "&label=Canada");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('one_series');
    });

    it("should display the metric picker on hover of metric picker icon", async function () {
        await page.hover('.jqplot-seriespicker');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
    });

    it("should show multiple metrics when another metric picked", async function () {
        await page.waitForSelector('.jqplot-seriespicker-popover input');
        const element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):first');
        await element.click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('two_metrics');
    });

    it("should show graph as image when export as image icon clicked", async function () {
        await page.click('#dataTableFooterExportAsImageIcon');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('export_image');
    });

    it("should display more periods when limit selection changed", async function () {
        const element = await page.jQuery('.ui-dialog .ui-widget-header button:visible');
        await element.click();

        await page.click('.limitSelection input');
        await page.evaluate(function () {
            $('.limitSelection ul li:contains(60) span').click();
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('limit_changed');
    });

    // annotations tests
    it("should show annotations when annotation icon on x-axis clicked", async function () {
        await page.click('.limitSelection input');
        await page.evaluate(function () {
            $('.limitSelection ul li:contains(30) span').click(); // change limit back
        });
        await page.waitForNetworkIdle();

        const element = await page.jQuery('.evolution-annotations>span[data-count!=0]');
        await element.click();
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_single_period');
    });

    it("should show all annotations when annotations footer link clicked", async function () { // TODO: fails
        await page.click('.annotationView');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_all');
    });

    it("should show no annotations message when no annotations for site", async function () {
        await page.goto(page.url().replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");
        await page.click('.annotationView');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_none');
    });

    it("should show add annotation form when create annotation clicked", async function () {
        await page.click('.add-annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.datepicker').datepicker("setDate", new Date(2012,0,2) );
            $(".ui-datepicker-current-day").trigger("click"); // this triggers onSelect event which sets .annotation-period-edit>a
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('new_annotation_form');
    });

    it("should add new annotation when create annotation submitted", async function () {
        await page.focus('.new-annotation-edit');
        await page.keyboard.type('new annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.ui-datepicker-calendar td a:contains(15)').click();
        });
        await page.waitForNetworkIdle();
        await page.click('.annotation-list-range');
        await page.click('input.new-annotation-save');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('new_annotation_submit');
    });

    it("should star annotation when star image clicked", async function () {
        await page.click('.annotation-star');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_starred');
    });

    it("should show edit annotation form", async function () {
        await page.click('.edit-annotation');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_edit_form');
    });

    it("should edit annotation when edit form submitted", async function () {
        await page.focus('.annotation-edit');
        await page.keyboard.type('edited annotation');
        await page.click('.annotation-period-edit>a');
        await page.evaluate(function () {
            $('.annotation-meta .ui-datepicker-calendar td a:contains(16)').click();
        });
        await page.waitForNetworkIdle();
        await page.click('.annotation-list-range');
        await page.click('input.annotation-save');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotation_edit_submit');
    });

    it("should delete annotation when delete link clicked", async function () {
        await page.click('.edit-annotation');
        await page.waitForFunction("$('.delete-annotation:visible').length > 0");
        await page.evaluate(function () {
            $('.delete-annotation').click();
        });
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_none');
    });

    it("should cutout two labels so all can fit on screen", async function () {
        await page.webpage.setViewport({ width: 320, height: 320 });
        await page.goto(url.replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('label_ticks_cutout');
    });

    it("should show available periods", async function () {
        await page.webpage.setViewport({
            width: 1350,
            height: 768,
        });
        await page.reload();
        await page.waitForNetworkIdle();
        await (await page.jQuery('.activatePeriodsSelection:last')).click();

        await page.mouse.move(-10, -10);
        await page.waitForTimeout(500); // wait for animation

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_list');
    });

    it("should be possible to change period", async function () {
        await (await page.jQuery('[data-period=month]:last')).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_selected');
    });

    it("should not show add annotation form for user with view access", async function () {
        testEnvironment.idSitesViewAccess = [1];
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();

        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.click('.annotationView');
        await page.waitForNetworkIdle();

        // check that add annotation link is not shown
        const element = await page.$('.add-annotation');
        expect(element).to.be.not.ok;
    });

    describe("with_PlotLinesTweaks_enabled", function () {
        before(function () {
            delete testEnvironment.idSitesViewAccess;
            testEnvironment.testUseMockAuth = 1;
            testEnvironment.overrideConfig('FeatureFlags', 'PlotLinesTweaks_feature', 'enabled');
            testEnvironment.save();
        });

        after(function () {
            if (testEnvironment.configOverride.FeatureFlags) {
                delete testEnvironment.configOverride.FeatureFlags.PlotLinesTweaks_feature;
            }
            testEnvironment.save();
        });

        it("should render the evolution graph footer legend correctly", async function () {
            await page.webpage.setViewport({ width: 1350, height: 768 });
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('plot_lines_tweaks_initial');
        });

        it("should show graph as image with footer legend when export as image icon clicked", async function () {
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();
            await page.click('#dataTableFooterExportAsImageIcon');
            await page.waitForNetworkIdle();

            const dialog = await page.$('.ui-dialog');
            expect(await dialog.screenshot()).to.matchImage('plot_lines_tweaks_export_image');
        });

        it("should export the graph image using the active dark theme background", async function () {
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();
            await setThemeMode('dark');
            await page.waitForTimeout(250);
            await page.click('#dataTableFooterExportAsImageIcon');
            await page.waitForSelector('.ui-dialog img');

            expect(await getImagePixelColor('.ui-dialog img', 5))
                .to.equal(await getResolvedBackgroundColor('.jqplot-target'));
        });

        it("should overflow footer legend labels cleanly in a narrow viewport", async function () {
            await page.webpage.setViewport({ width: 320, height: 480 });
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();

            const legendState = await getFooterLegendState();
            expect(legendState.hasLegend).to.equal(true);
            expect(legendState.itemCount).to.be.above(legendState.visibleItemCount);
            expect(legendState.visibleItemCount).to.be.at.least(1);
            expect(legendState.hiddenItemCount).to.be.above(0);
            expect(legendState.overflowLabel).to.equal('…');

            expect(await page.screenshot({ fullPage: true })).to.matchImage('plot_lines_tweaks_narrow_overflow');
        });

        it("should show annotations above the footer legend", async function () {
            await page.webpage.setViewport({ width: 1350, height: 768 });
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();

            const element = await page.jQuery('.evolution-annotations>span[data-count!=0]');
            await element.click();
            await page.waitForNetworkIdle();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('plot_lines_tweaks_annotations');
        });

        it("should use the active dark theme background for the graph loading overlay", async function () {
            await page.goto(plotLinesTweaksUrl);
            await page.waitForNetworkIdle();
            await setThemeMode('dark');
            await page.waitForTimeout(250);

            await page.evaluate(function () {
                var dataTable = $('.dataTable').data('uiControlObject');
                var originalReloadAjaxDataTable = dataTable.reloadAjaxDataTable.bind(dataTable);

                dataTable.reloadAjaxDataTable = function () {
                    return null;
                };

                dataTable.__restoreReloadAjaxDataTable = function () {
                    dataTable.reloadAjaxDataTable = originalReloadAjaxDataTable;
                };
            });

            await page.hover('.jqplot-seriespicker');
            await page.waitForSelector('.jqplot-seriespicker-popover input');
            const element = await page.jQuery('.jqplot-seriespicker-popover input:not(:checked):first');
            await element.click();
            await page.waitForSelector('.jqplot-loading');

            expect(await page.evaluate(function () {
                return window.getComputedStyle(document.querySelector('.jqplot-loading')).backgroundColor;
            })).to.equal(await getResolvedBackgroundColor('.jqplot-loading'));
            expect(await page.evaluate(function () {
                return !!document.querySelector('.jqplot-loading .matomo-loader');
            })).to.equal(true);
            expect(await page.evaluate(function () {
                return window.getComputedStyle(document.querySelector('.jqplot-loading')).opacity;
            })).to.equal('0.7');

            await page.evaluate(function () {
                var dataTable = $('.dataTable').data('uiControlObject');
                if (dataTable && dataTable.__restoreReloadAjaxDataTable) {
                    dataTable.__restoreReloadAjaxDataTable();
                    delete dataTable.__restoreReloadAjaxDataTable;
                }
                $('.jqplot-loading').remove();
            });
        });
    });
});
