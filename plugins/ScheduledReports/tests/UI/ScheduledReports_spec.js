/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ScheduledReports", function () {
    this.fixture = "Piwik\\Plugins\\ScheduledReports\\tests\\Fixtures\\ReportSubscription";

    it("should show an error if no token was provided", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('no_token');
    });

    it("should show an error if token is invalid", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=invalidtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_token');
    });

    it("should ask for confirmation before unsubscribing", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('unsubscribe_form');
    });

    it("should show success message on submit", async function () {
        await page.click(".submit");
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('unsubscribe_success');
    });

    it("token should be invalid on second try", async function () {
        await page.goto("?module=ScheduledReports&action=unsubscribe&token=mycustomtoken");

        expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_token');
    });

    describe('ManageScheduledReports', function () {
        const manageReportsUrl = "?module=ScheduledReports&action=index&idSite=1&period=day&date=2013-01-23";
        const createdReportName = "for testing";

        // Helper function to open the report we will use for testing
        async function openReportForTesting() {
            await page.evaluate((description) => {
                const rows = Array.from(document.querySelectorAll('#entityEditContainer tbody tr'));

                for (const row of rows) {
                    if (!row.textContent || row.textContent.indexOf(description) === -1) {
                        continue;
                    }
                    const editButton = row.querySelector('button[title="Edit"]');
                    editButton.click();
                }
            }, createdReportName);

            await page.waitForSelector('#addEditReport', { visible: true });
            await page.waitForSelector('.selectedReportsList li', { visible: true });
        }
        async function getReportsWrapper() {
          return await page.$('.selectedReportsWrapper');
        }

        async function reorderSelectedReports(sourceId, targetId, position = 'before') {
            await page.evaluate(async (dragSourceId, dropTargetId, dropPosition) => {
                const getItem = (itemId) => document.querySelector(
                    `.selectedReportsList li[data-item-id="${itemId}"]`,
                );
                const source = getItem(dragSourceId);
                const target = getItem(dropTargetId);

                if (!source || !target) {
                    throw new Error(`Could not find draggable items ${dragSourceId} -> ${dropTargetId}`);
                }

                const rect = target.getBoundingClientRect();
                const clientY = dropPosition === 'after' ? rect.bottom - 1 : rect.top + 1;
                const dataTransfer = new DataTransfer();

                source.dispatchEvent(new DragEvent('dragstart', {
                    bubbles: true,
                    cancelable: true,
                    dataTransfer,
                }));
                target.dispatchEvent(new DragEvent('dragover', {
                    bubbles: true,
                    cancelable: true,
                    clientY,
                    dataTransfer,
                }));
                target.dispatchEvent(new DragEvent('drop', {
                    bubbles: true,
                    cancelable: true,
                    clientY,
                    dataTransfer,
                }));
                source.dispatchEvent(new DragEvent('dragend', {
                    bubbles: true,
                    cancelable: true,
                    dataTransfer,
                }));

                await new Promise((resolve) => {
                    requestAnimationFrame(() => requestAnimationFrame(resolve));
                });
            }, sourceId, targetId, position);
        }

        it("should show selected reports when creating a new report", async function () {
            await page.goto(manageReportsUrl);
            await page.waitForNetworkIdle();

            await page.waitForSelector('#add-report');
            await page.click('#add-report');
            await page.waitForSelector('#addEditReport', { visible: true });

            const reportCheckboxes = await page.$$(
                'div[name="reportsList"]:not([style*="display: none"]) .listReports input[type="checkbox"]',
            );

            const selectedReportIds = [];
            // Click the first 4 checkboxes
            for (const checkbox of reportCheckboxes.slice(0, 4)) {
                await checkbox.click();
                const uniqueId = await checkbox.evaluate((input) => input.id );
                if (uniqueId) {
                    selectedReportIds.push(uniqueId);
                }
            }
            const selectedReportsWrapper = await getReportsWrapper();
            expect(await selectedReportsWrapper.screenshot()).to.matchImage('selected_reports');
        });

        it("should persist manually reordered selected reports when saving a report", async function () {
            await openReportForTesting();
            const initialOrder = await page.$$eval(
              '.selectedReportsList li',
              (items) => items.map((item) => item.getAttribute('data-item-id')),
            );
            const expectedOrder = initialOrder.slice().reverse();

            await reorderSelectedReports(initialOrder[3], initialOrder[0], 'before');
            await reorderSelectedReports(initialOrder[2], initialOrder[0], 'before');
            await reorderSelectedReports(initialOrder[1], initialOrder[0], 'before');

            await page.waitForFunction((newOrder) => {
                const order = Array.from(document.querySelectorAll('.selectedReportsList li'))
                    .map((item) => item.getAttribute('data-item-id'));
                return JSON.stringify(order) === JSON.stringify(newOrder);
            }, {}, expectedOrder);

            const descriptionSelector = 'textarea[name="report_description"], input[name="report_description"]';
            await page.waitForSelector(descriptionSelector, { visible: true });
            await page.click(descriptionSelector, { clickCount: 3 });
            await page.keyboard.press('Backspace');
            await page.type(descriptionSelector, createdReportName);
            await page.keyboard.press('Tab');
            await page.$eval(descriptionSelector, (descriptionField) => {
                descriptionField.dispatchEvent(new Event('change', { bubbles: true }));
            });
            await page.click('.matomo-save-button .btn');
            await page.waitForNetworkIdle();

            await openReportForTesting();
            const persistedOrder = await page.$$eval(
              '.selectedReportsList li',
              (items) => items.map((item) => item.getAttribute('data-item-id')),
            );

            expect(persistedOrder).to.deep.equal(expectedOrder);
            const selectedReportsWrapper = await getReportsWrapper();
            expect(await selectedReportsWrapper.screenshot()).to.matchImage('reorder_persisted');
        });
    });
});
