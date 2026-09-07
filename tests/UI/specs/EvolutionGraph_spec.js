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
    const multiMetricColumns = "nb_visits,nb_actions,avg_time_on_site,bounce_rate";
    const multiMetricUrl = url + "&columns=" + multiMetricColumns + "&filter_add_columns_when_show_all_columns=0";

    // Export leaves the 3-dots menu when the header line has room, so open whichever holds it.
    const openExport = async function () {
        if (await page.$('[data-report-action="export"]')) {
            await page.click('[data-report-action="export"] .mtm-selector__trigger');
        } else {
            await page.click('.reportHeader__actionsTrigger');
        }
    };

    // The promoted toggle is an icon, so its wording is in the title rather than in a label.
    const annotationsLabel = () => page.evaluate(() => {
        const toggle = document.querySelector('.annotationView');
        const label = toggle.querySelector('.mtm-dropdownPanel__menuLabel');

        return (label ? label.textContent : toggle.getAttribute('title')).trim();
    });

    // Annotations leave the 3-dots menu when the header line has room, so reach the toggle wherever
    // it is.
    const toggleAnnotations = async function () {
        if (!await page.$('[data-report-action="annotations"]')) {
            await page.click('.reportHeader__actionsTrigger');
        }

        await page.click('.annotationView');
    };

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
            const labels = footer
                ? Array.prototype.map.call(
                    footer.querySelectorAll('.jqplot-legend-item .jqplot-legend-label'),
                    function (label) { return label.textContent.trim(); }
                )
                : [];

            return {
                hasLegend: !!footer,
                itemCount: items.length,
                hiddenItemCount: hiddenItems.length,
                visibleItemCount: footer ? footer.querySelectorAll('.jqplot-legend-item:not(.jqplot-legend-item-hidden)').length : 0,
                overflowLabel: overflowItem ? overflowItem.textContent.trim() : null,
                labels: labels,
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

    it("should display the metric picker when the metric picker button is clicked", async function () {
        await page.click('.metrics-picker__toggle');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('metric_picker_shown');
        await page.keyboard.press('Escape');
    });

    it("should show multiple metrics when another metric picked", async function () {
        await page.click('.metrics-picker__toggle');
        // click the label, not the input: the options sit in the DOM whether the dropdown is open
        // or not, and the input itself is the hidden half of a Materialize checkbox
        await page.waitForSelector('.metrics-picker__options label');
        const element = await page.jQuery('.metrics-picker__options .metrics-picker__label:has(input:not(:checked)):first');
        await element.click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('two_metrics');
    });

    it("should show graph as image when export as image icon clicked", async function () {
        await openExport();
        await page.click('#dataTableExportAsImageIcon-header');
        await page.waitForNetworkIdle();

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('export_image');
    });

    it("should display more periods when limit selection changed", async function () {
        const element = await page.jQuery('.ui-dialog .ui-widget-header button:visible');
        await element.click();

        await page.click('.limitSelection .mtm-selector__trigger');
        await page.click('.limitSelection [data-limit="60"]');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('limit_changed');
    });

    // annotations tests
    it("should show annotations when annotation icon on x-axis clicked", async function () {
        await page.click('.limitSelection .mtm-selector__trigger');
        await page.click('.limitSelection [data-limit="30"]'); // change limit back
        await page.waitForNetworkIdle();

        const element = await page.jQuery('.evolution-annotations>span[data-count!=0]');
        await element.click();
        await page.waitForNetworkIdle();

        expect(await annotationsLabel()).to.equal('Hide annotations');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_single_period');

        // The callback that moves the label runs on the way out as well as the way in.
        await element.click();
        await page.waitForTimeout(1000);
        expect(await annotationsLabel()).to.equal('Show annotations');

        await element.click(); // leave the notes as the next test expects to find them
        await page.waitForTimeout(1000);
    });

    it("should hide the notes a marker opened when the menu entry is clicked", async function () {
        // The entry says "hide", so from notes opened for a single day it closes them rather than
        // reloading the whole range - those notes carry no `data-is-range`.
        const notesShown = () => page.evaluate(() => $('.annotation-manager').is(':visible'));
        expect(await notesShown(), 'the marker left its notes open').to.equal(true);

        await page.evaluate(() => document.querySelector('.annotationView').click());
        await page.waitForTimeout(1000); // slideUp is 'slow'

        expect(await notesShown(), 'the entry closed them').to.equal(false);
        expect(await annotationsLabel(), 'and the label followed').to.equal('Show annotations');
    });

    it("should show all annotations when the menu entry is clicked", async function () {
        await toggleAnnotations();
        await page.waitForNetworkIdle();

        // Opens from the closed state the test above leaves behind, so the whole range loads.
        expect(await page.screenshot({ fullPage: true })).to.matchImage('annotations_all');
    });

    it("should put the label back when the notes are hidden again", async function () {
        // Clicks through the element, not the pointer: the panel may or may not be open here.
        const notesShown = () => page.evaluate(
          () => $('.annotation-manager').is(':visible'),
        );
        const toggle = async () => {
            await page.evaluate(() => document.querySelector('.annotationView').click());
            await page.waitForTimeout(1000); // slideUp/slideDown are 'slow'
        };

        expect(await annotationsLabel()).to.equal('Hide annotations');
        expect(await notesShown()).to.equal(true);

        await toggle();
        expect(await notesShown()).to.equal(false);
        expect(await annotationsLabel()).to.equal('Show annotations');

        await toggle();
        expect(await notesShown()).to.equal(true);
        expect(await annotationsLabel()).to.equal('Hide annotations');
    });

    it("should show no annotations message when no annotations for site", async function () {
        await page.goto(page.url().replace(/idSite=[^&]*/, "idSite=3") + "&columns=nb_visits");
        await toggleAnnotations();
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
        // this asserts the same image as the test above, so it has to leave the pointer where that
        // one does: the delete click leaves it inside the widget, and a hovered widget darkens the
        // header's actions trigger
        await page.mouse.move(-10, -10);

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
        // wide enough for the header to lift the period selector out of its menu
        await page.click('[data-report-action="periods"] .mtm-selector__trigger');

        await page.mouse.move(-10, -10);
        await page.waitForTimeout(500); // wait for animation

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_list');
    });

    it("should be possible to change period", async function () {
        // the previous test left the menu and its submenu open
        await (await page.jQuery('[data-period=month]:last')).click();
        await page.waitForNetworkIdle();

        const stillOpen = await page.evaluate(
          () => document.querySelectorAll('.mtm-dropdownPanel__submenu--open').length,
        );
        expect(stillOpen, 'picking a period folds the submenu').to.equal(0);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('periods_selected');
    });

    it("should not show add annotation form for user with view access", async function () {
        testEnvironment.idSitesViewAccess = [1];
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();

        await page.goto(url);
        await page.waitForNetworkIdle();
        await toggleAnnotations();
        await page.waitForNetworkIdle();

        // check that add annotation link is not shown
        const element = await page.$('.add-annotation');
        expect(element).to.be.not.ok;
    });

    describe("footer legend", function () {
        before(function () {
            delete testEnvironment.idSitesViewAccess;
            testEnvironment.testUseMockAuth = 1;
            testEnvironment.save();
        });

        it("should render the evolution graph footer legend with all selected metrics", async function () {
            await page.webpage.setViewport({ width: 1350, height: 768 });
            await page.goto(multiMetricUrl);
            await page.waitForNetworkIdle();

            const legendState = await getFooterLegendState();
            expect(legendState.hasLegend).to.equal(true);

            // getCountry plots every row (country) for each selected metric, so the legend
            // labels look like "United States (Visits)". Assert that every selected metric is
            // represented, regardless of how many rows the fixture happens to contain.
            const selectedMetricLabels = ['Visits', 'Actions', 'Avg. Time on Website', 'Bounce Rate'];
            selectedMetricLabels.forEach(function (metricLabel) {
                const isPresent = legendState.labels.some(function (label) {
                    return label.indexOf('(' + metricLabel + ')') !== -1;
                });
                expect(isPresent, 'legend should include a "' + metricLabel + '" series').to.equal(true);
            });
        });

        it("should export the graph image using the active dark theme background", async function () {
            await page.goto(multiMetricUrl);
            await page.waitForNetworkIdle();
            await setThemeMode('dark');
            await page.waitForTimeout(250);
            await openExport();
            await page.click('#dataTableExportAsImageIcon-header');
            await page.waitForSelector('.ui-dialog img');

            expect(await getImagePixelColor('.ui-dialog img', 5))
                .to.equal(await getResolvedBackgroundColor('.jqplot-target'));
        });

        it("should overflow footer legend labels cleanly in a narrow viewport", async function () {
            await page.webpage.setViewport({ width: 320, height: 480 });
            await page.goto(multiMetricUrl);
            await page.waitForNetworkIdle();

            const legendState = await getFooterLegendState();
            expect(legendState.hasLegend).to.equal(true);
            expect(legendState.itemCount).to.be.above(legendState.visibleItemCount);
            expect(legendState.visibleItemCount).to.be.at.least(1);
            expect(legendState.hiddenItemCount).to.be.above(0);
            expect(legendState.overflowLabel).to.equal('…');
        });

        it("should use the active dark theme background for the graph loading overlay", async function () {
            await page.webpage.setViewport({ width: 1350, height: 768 });
            await page.goto(multiMetricUrl);
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

            await page.click('.metrics-picker__toggle');
            await page.waitForSelector('.metrics-picker__options label');
            const element = await page.jQuery('.metrics-picker__options .metrics-picker__label:has(input:not(:checked)):first');
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

    it("should close the period selector on a second click", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        const trigger = '[data-report-action="periods"] .mtm-selector__trigger';
        await page.click(trigger);
        await page.waitForSelector('.mtm-selector.expanded', { visible: true });

        await page.click(trigger);
        const stillOpen = await page.evaluate(
          () => !!document.querySelector('.mtm-selector.expanded'),
        );
        expect(stillOpen, 'a second click closes it').to.equal(false);
    });

    it("should close the period selector once a period is picked", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        await page.click('[data-report-action="periods"] .mtm-selector__trigger');
        await page.waitForSelector('.mtm-selector.expanded', { visible: true });

        await page.evaluate(
          () => document.querySelector('.dataTablePeriods [data-period="week"]').click(),
        );
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        const stillOpen = await page.evaluate(
          () => !!document.querySelector('.mtm-selector.expanded'),
        );
        expect(stillOpen, 'picking a period closes the selector').to.equal(false);
    });

    it("should let the keyboard reach and activate a period", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        await page.evaluate(
          () => document.querySelector('[data-report-action="periods"] .mtm-selector__trigger').click(),
        );
        await page.waitForSelector('.mtm-selector.expanded', { visible: true });

        const focused = await page.evaluate(() => {
            const item = document.querySelector('.dataTablePeriods [role^="menuitem"]');
            if (!item) {
                return 'no item';
            }
            item.focus();
            return document.activeElement === item ? 'focused' : 'not focusable';
        });
        expect(focused, 'a period is focusable once the submenu is open').to.equal('focused');

        // Enter has to act, since the item is not a link the browser would follow.
        await page.evaluate(() => {
            document.querySelector('.dataTablePeriods [data-period="week"]')
                .dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        const period = await page.evaluate(
          () => $('.dataTable').first().data('uiControlObject').param.period,
        );
        expect(period, 'Enter changed the period').to.equal('week');
    });

    it("should render a report header inside a widgetized container", async function () {
        // Nothing above a widgetized container's children renders a header, so each renders its own.
        await page.goto("?module=Widgetize&action=iframe&containerId=VisitOverviewWithGraph"
            + "&moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer"
            + "&disableLink=1&widget=1&idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30");
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000);

        const state = await page.evaluate(() => ({
            headers: document.querySelectorAll('.reportHeader').length,
            triggers: document.querySelectorAll('.reportHeader__actionsTrigger').length,
            entries: document.querySelectorAll('.annotationView').length,
            markers: document.querySelectorAll('.evolution-annotations span[data-date]').length,
            periods: document.querySelectorAll('[data-report-action="periods"]').length,
        }));
        expect(state.headers, 'exactly one, not one per child').to.equal(1);
        // Everything this report offers is promoted, so the menu holds nothing and is not offered.
        expect(state.triggers, 'no trigger for an empty menu').to.equal(0);
        expect(state.markers, 'markers render').to.be.above(0);
        expect(state.entries, 'the annotations toggle is in it').to.equal(1);
        expect(state.periods, 'the period selector is lifted out of it').to.equal(1);

        await page.evaluate(() => document.querySelector('.annotationView').click());
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1500);
        const shown = await page.evaluate(() => $('.annotation-manager').is(':visible'));
        expect(shown, 'clicking it opens the notes').to.equal(true);
    });
});
