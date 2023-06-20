/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentSelectorEditorTest", function () {
    var selectorsToCapture = ".segmentEditorPanel,.segmentEditorPanel .dropdown-body,.segment-element";

    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

    async function selectFieldValue(fieldName, textToSelect)
    {
        await (await page.jQuery(fieldName + ' input.select-dropdown', { waitFor: true })).click();
        await (await page.jQuery(fieldName + ' .dropdown-content li:contains("' + textToSelect + '"):first', { waitFor: true })).click();
        await page.mouse.move(-10, -10);
    }

    async function selectDimension(prefixSelector, category, name)
    {
        await (await page.jQuery(prefixSelector + ' .metricListBlock .select-wrapper', { waitFor: true })).click();
        await (await page.jQuery(prefixSelector + ' .metricListBlock .expandableList h4:contains(' + category + ')', { waitFor: true })).click();
        await (await page.jQuery(prefixSelector + ' .metricListBlock .expandableList .secondLevel li:contains(' + name + ')', { waitFor: true })).click();
    }

    it("should load correctly", async function() {
        await page.goto(url);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('0_initial');
    });

    it("should open selector when control clicked", async function() {
        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('1_selector_open');
    });

    it("should open segment editor when edit link clicked for existing segment", async function() {
        await page.evaluate(function() {
            $('.segmentList .editSegment:first').click()
        });
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('2_segment_editor_update');
    });

    it("should start editing segment name when segment name edit link clicked", async function() {
        await page.click('.segmentEditorPanel .editSegmentName');
        await page.waitForTimeout(250); // animation
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('3_segment_editor_edit_name');
    });

    it("should close the segment editor when the close link is clicked", async function() {
        await page.evaluate(function () {
            $('.segmentEditorPanel .segment-footer .close').click();
        });
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('7_segment_editor_closed');
    });

    it("should open blank segment editor when create new segment link is clicked", async function() {
        await page.click('.segmentationContainer .title');
        await page.click('.add_new_segment');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.segmentRow0');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('8_segment_editor_create');
    });

    it("should update segment expression when selecting different segment", async function() {
        await selectDimension('.segmentRow0', 'Behaviour', 'Action URL');
        await selectFieldValue('.segmentRow0 .segment-row:first .metricMatchBlock', 'Is not');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('dimension_drag_drop');
    });

    it("should show suggested segment values when a segment value input is focused", async function() {
        await page.click('.segmentEditorPanel .segmentRow0 .ui-autocomplete-input');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('suggested_values');
    });

    it("should add an OR condition when clicking on add OR", async function() {
        await page.$eval('.segmentEditorPanel .segmentRow0 .ui-autocomplete-input', e => e.blur());
        await page.click('.segmentEditorPanel .segment-add-or');
        await page.waitForFunction(() => !! $('.segmentRow0 .segment-rows>div:eq(1)').length);
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('add_new_or_condition');
    });

    it("should add an OR condition when a segment dimension is selected in the OR placeholder section", async function() {
        await selectDimension('.segmentRow0 .segment-row:last', 'Behaviour', 'Clicked Outlink');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('drag_or_condition');
    });

    it("should add an AND condition when clicking on add AND", async function() {
        await page.click('.segmentEditorPanel .segment-add-row');
        await page.waitForSelector('.segmentRow1');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('add_new_and_condition');
    });

    it("should add an AND condition when a segment dimension is dragged to the AND placeholder section", async function() {
        await selectDimension('.segmentRow1', 'Behaviour', 'Clicked Outlink');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('drag_and_condition');
    });

    it("should save a new segment and add it to the segment list when the form is filled out and the save button is clicked", async function() {
        for (let i = 0; i < 3; i += 1) {
          await page.evaluate(function (i) {
            $(`.metricValueBlock input:eq(${i})`).val('value ' + i).change();
          }, i);
          await page.waitForTimeout(250);
        }

        await page.type('input.edit_segment_name', 'new segment');
        await page.click('.segmentRow0 .segment-or'); // click somewhere else to save new name

        await page.waitForTimeout(200);

        await page.evaluate(function () {
            $('button.saveAndApply').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('.segmentationContainer');

        await page.click('.segmentationContainer');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('saved');
    });

    it("should show the new segment after page reload", async function() {
        await page.reload();
        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('saved');
    });

    it("should correctly load the new segment's details when the new segment is edited", async function() {
        await page.click('.segmentList li[data-idsegment="4"] .editSegment');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('saved_details');
    });

    it("should correctly should show a confirmation when changing segment definition", async function() {
        await page.click('.segmentEditorPanel .editSegmentName');

        await page.$eval('.segmentEditorPanel .segmentRow0 .ui-autocomplete-input', e => e.blur());
        await page.evaluate(function () {
            $('input.edit_segment_name').val('').change();
        });
        await page.type('input.edit_segment_name', 'edited segment');
        await (await page.jQuery('.segmentRow0 .segment-or:first')).click(); // click somewhere else to save new name

        await selectFieldValue('.segmentRow0 .segment-row:first .metricMatchBlock', 'Is not');
        await selectFieldValue('.segmentRow0 .segment-row:last .metricMatchBlock', 'Is not');
        await selectFieldValue('.segmentRow1 .segment-row .metricMatchBlock', 'Is not');

        for (let i = 0; i < 3; i += 1) {
          await page.waitForTimeout(200);
          await page.evaluate(function (i) {
            $(`.metricValueBlock input:eq(${i})`).val('new value ' + i).change();
          }, i);
        }

        await page.waitForTimeout(200);

        await page.evaluate(function () {
           $('button.saveAndApply').click();
        });
        await page.waitForSelector('.modal.open');
        await page.waitForTimeout(500); // animation to show confirm

        const modal = await page.$('.modal.open');
        expect(await modal.screenshot()).to.matchImage('update_confirmation');
    });

    it("should correctly update the segment when saving confirmed", async function() {
        var elem = await page.jQuery('.modal.open .modal-footer a:contains(Yes):visible');
        await elem.click();
        await page.waitForNetworkIdle();
        await (await page.waitForSelector('.segmentationContainer')).click();
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('updated');
    });

    it("should show the updated segment after page reload", async function() {
        await page.reload();
        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('updated');
    });

    it("should correctly load the updated segment's details when the updated segment is edited", async function() {
        await page.click('.segmentList li[data-idsegment="4"] .editSegment');
        await page.waitForNetworkIdle();

        await page.waitForSelector('.segmentListContainer .metricValueBlock');

        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('updated_details');
    });

    it('should display autocomplete dropdown options correctly with lower case', async function() {
        await page.click('.expandableSelector .select-wrapper');
        await page.waitForSelector('.expandableList');
        await page.click('.expandableSelector');
        await page.type('.expandableSelector', 'event');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('autocomplete_lowercase');
    });

    it('should display autocomplete dropdown options correctly with upper case', async function() {
        const input = await page.$('.expandableSelector');
        await input.click({ clickCount: 3 })
        await page.type('.expandableSelector', 'EVENT');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('autocomplete_uppercase');
    });

    it('should display autocomplete dropdown options correctly with capitalized', async function() {
        const input = await page.$('.expandableSelector');
        await input.click({ clickCount: 3 })
        await page.type('.expandableSelector', 'Event');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('autocomplete_capitalized');
    });


    it("should correctly show delete dialog when the delete link is clicked", async function() {
        await page.click('.segmentEditorPanel a.delete');
        await page.waitForTimeout(500); // animation

        const modal = await page.$('.modal.open');
        expect(await modal.screenshot()).to.matchImage('deleted_dialog');
    });

    it("should correctly remove the segment when the delete dialog is confirmed", async function() {
        var elem = await page.jQuery('.modal.open .modal-footer a:contains(Yes):visible');
        await elem.click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('.segmentationContainer .title');

        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture + ',.modal.open')).to.matchImage('deleted');
    });

    it("should not show the deleted segment after page reload", async function() {
        await page.reload();
        await page.waitForSelector('.segmentationContainer .title');

        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('deleted');
    });

    it('should correctly handle complex segments with encoded characters and whitespace', async function () {
        await page.goto(url);

        await page.click('.segmentationContainer .title');
        await page.click('a.add_new_segment');
        await page.type('input.edit_segment_name', 'complex segment');

        await page.waitForSelector('.segmentRow0');
        await selectDimension('.segmentRow0', 'Visitors', 'Browser');
        await selectFieldValue('.segmentRow0 .segment-row:eq(0) .metricMatchBlock', 'Is not');

        var complexValue = 's#2&#--_*+?#  #5"\'&<>.22,3';
        await (await page.jQuery('.segmentRow0 .segment-row:first .metricValueBlock input')).type(complexValue);
        await page.waitForTimeout(200);

        await page.evaluate(() => $('.segment-add-or > div').click());
        await page.waitForFunction(() => !! $('.segmentRow0 .segment-row:eq(1)').length);

        // configure or condition
        await selectDimension('.segmentRow0 .segment-row:eq(1)', 'Visitors', 'Browser');
        await selectFieldValue('.segmentRow0 .segment-row:eq(1) .metricMatchBlock', 'Is');

        await (await page.jQuery('.segmentRow0 .segment-row:eq(1) .metricValueBlock input')).type(complexValue);
        await page.waitForTimeout(200);

        await page.evaluate(() => $('.segment-add-row > div').click());
        await page.waitForSelector('.segmentRow1 .segment-row');

        // configure and condition
        await selectDimension('.segmentRow1', 'Visitors', 'Browser');
        await selectFieldValue('.segmentRow1 .segment-row:first .metricMatchBlock', 'Is not');

        await (await page.jQuery('.segmentRow1 .metricValueBlock input')).type(complexValue);
        await page.waitForTimeout(200);

        await page.evaluate(function () {
            $('button.saveAndApply').click();
        });

        await page.waitForNetworkIdle();
        await page.waitForSelector('.dataTable');
        await page.waitForNetworkIdle();

        expect(await page.screenshot()).to.matchImage('complex_segment');
    });

    it('should not show "AND segmented reports are pre-processed (faster, requires cron)" when enable_create_realtime_segments = 0', async () => {
        testEnvironment.overrideConfig('General', 'enable_create_realtime_segments', 0);
        testEnvironment.save();
        await page.goto(url);
        await page.click('.segmentationContainer .title');
        await page.click('.add_new_segment');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('enabled_create_realtime_segments');
    });

    it("should save a new segment when enable_create_realtime_segments = 0", async function() {
        // ensure segment won't be archived after saving it.
        testEnvironment.overrideConfig('General', 'enable_create_realtime_segments', 0);
        testEnvironment.overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        testEnvironment.overrideConfig('General', 'browser_archiving_disabled_enforce', 1);
        testEnvironment.optionsOverride = {
          enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();
        await page.evaluate(function () {
          $('.segmentRow0 .segment-row:first .metricValueBlock input').val('3').change();
        });

        await page.type('input.edit_segment_name', 'auto archive segment');
        await page.click('.segmentRow0 .segment-or'); // click somewhere else to save new name

        // this is for debug purpose. If segment can't be saved, and alert might be shown, causing the UI test to hang
        page.on('dialog', (dialog)=> {
            console.log(dialog.message());
        });

        await page.waitForTimeout(200);

        await page.evaluate(function () {
            $('button.saveAndApply').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('.segmentationContainer');

        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('enabled_create_realtime_segments_saved');
    });
});
