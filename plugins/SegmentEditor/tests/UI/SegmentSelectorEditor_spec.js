/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentSelectorEditorTest", function () {
    const getSegmentQuery = n => '.segmentList li:nth-of-type(' + (n+1) + ')';
    const getSegmentStarQuery = n => getSegmentQuery(n) + ' .starSegment';
    var selectorsToCapture = ".segmentEditorPanel,.segmentEditorPanel .dropdown-body,.segment-element";
    var generalParams = 'idSite=1&period=year&date=2012-08-09';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Actions&subcategory=General_Pages';

    async function selectFieldValue(fieldName, textToSelect)
    {
        await page.waitForFunction((fieldNameSelector, optionText) => {
            const $field = window.$(fieldNameSelector).first();
            const select = $field.find('select').get(0);

            if (!select) {
                return false;
            }

            const option = Array.from(select.options).find((entry) => {
                return (entry.textContent || '').trim() === optionText;
            });

            if (!option) {
                return false;
            }

            window.$(select).val(option.value).trigger('change');
            return true;
        }, {}, fieldName, textToSelect);

        await page.waitForTimeout(200);
        await page.mouse.move(-10, -10);
    }

    async function selectDimension(prefixSelector, category, name)
    {
        await (await page.jQuery(prefixSelector + ' .metricListBlock .select-wrapper', { waitFor: true })).click();
        await (await page.jQuery(prefixSelector + ' .metricListBlock .expandableList h4:contains(' + category + ')', { waitFor: true })).click();
        await (await page.jQuery(prefixSelector + ' .metricListBlock .expandableList .secondLevel li:contains(' + name + ')', { waitFor: true })).click();
    }

    async function moveMouseAwayFromCapturedArea()
    {
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(100);
    }

    async function searchForSegment(searchTerm)
    {
        const selector = '.segmentationContainer .searchInputField';

        await page.waitForSelector(selector);
        await page.evaluate((inputSelector) => {
            const input = document.querySelector(inputSelector);
            if (!input) {
                throw new Error(`Search input not found for selector: ${inputSelector}`);
            }

            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }, selector);

        if (searchTerm) {
            await page.focus(selector);
            await page.type(selector, searchTerm);
        }

        // debounce in segment filter is 500ms
        await page.waitForTimeout(600);
    }

    async function getVisibleSegmentTitles()
    {
        return await page.evaluate(() => {
            return $('.segmentList li:visible .segname').toArray()
                .map((element) => $(element).prop('title') || $(element).text());
        });
    }

    async function expectSearchToShowOnly(searchTerm, expectedTitlePart)
    {
        await searchForSegment(searchTerm);
        const visibleSegmentTitles = await getVisibleSegmentTitles();
        expect(visibleSegmentTitles.length).to.equal(1);
        const expectedTitlePartLower = expectedTitlePart.toLowerCase();
        const visibleTitle = (visibleSegmentTitles[0] || '').toLowerCase();
        expect(visibleTitle.indexOf(expectedTitlePartLower) !== -1).to.equal(true);
        await searchForSegment('');
    }

    async function expectSearchToHaveNoResults(searchTerm)
    {
        await searchForSegment(searchTerm);
        const visibleSegmentTitles = await getVisibleSegmentTitles();
        expect(visibleSegmentTitles.length).to.equal(0);
        const hasNoResultsMessage = await page.evaluate(() => {
            return !!$('.segmentList .filterNoResults:visible').length;
        });
        expect(hasNoResultsMessage).to.equal(true);
        await searchForSegment('');
    }

    async function switchToAnonymousUser() {
        await testEnvironment.callApi('UsersManager.setUserAccess', {
            userLogin: 'anonymous',
            access: 'view',
            idSites: [1],
        });
        testEnvironment.testUseMockAuth = 0;
        await testEnvironment.save();
    }

    async function switchToConnectedUser() {
        testEnvironment.testUseMockAuth = 1;
        await testEnvironment.save();
        await testEnvironment.callApi('UsersManager.setUserAccess', {
            userLogin: 'anonymous',
            access: 'noaccess',
            idSites: [1],
        });
    }

    it("should load correctly", async function() {
        await page.goto(url);
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('0_initial');
    });

    it("should open selector when control clicked", async function() {
        await page.click('.segmentationContainer .title');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('1_selector_open');
    });

    it("should star all segments", async function() {
        await page.click(getSegmentStarQuery(1));
        await page.click(getSegmentStarQuery(2));
        await page.click(getSegmentStarQuery(3));
        const firstSegmentClassName = await page.evaluate(() => $('.segmentList li:nth-of-type(2)').attr('class'));
        expect(firstSegmentClassName).to.match(/segmentStarred/);
        const firstSegmentStarState = await page.evaluate(() => $('.segmentList li:nth-of-type(2) .starSegment').attr('data-state') || '');
        expect(firstSegmentStarState).to.equal('');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('1_selector_starred');
    });

    it("should unstar first segment", async function() {
        await page.click(getSegmentStarQuery(1));
        const firstSegmentClassName = await page.evaluate(() => $('.segmentList li:nth-of-type(2)').attr('class'));
        expect(firstSegmentClassName).to.not.match(/segmentStarred/);
        const firstSegmentStarState = await page.evaluate(() => $('.segmentList li:nth-of-type(2) .starSegment').attr('data-state') || '');
        expect(firstSegmentStarState).to.equal('');
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('1b_selector_unstarred');
    });

    it("should hide star for anonymous users", async function() {
        await switchToAnonymousUser();
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.click('.segmentationContainer .title');
        const firstSegmentStarCount = await page.evaluate(() => $('.segmentList li:nth-of-type(2) .starSegment').length);
        expect(firstSegmentStarCount).to.equal(0);
    });

    it("should open segment editor when edit link clicked for existing segment", async function() {
        await switchToConnectedUser();
        await page.goto('about:blank');
        await page.goto(url);
        await page.click('.segmentationContainer .title');
        await page.evaluate(function() {
            $('.segmentList button.editSegment:first').click();
        });
        await page.waitForNetworkIdle();
        const isPanelExpanded = await page.evaluate(() => $('.segmentEditorPanel').hasClass('expanded'));
        expect(isPanelExpanded).to.equal(false);
        await moveMouseAwayFromCapturedArea();
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
        await selectFieldValue('.segmentRow0 .segment-row:visible:first .metricMatchBlock', 'Is not');
        await page.$eval('.segmentEditorPanel .segmentRow0 .metricValueBlock input', e => e.blur());
        await page.waitForNetworkIdle();
        await moveMouseAwayFromCapturedArea();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('dimension_drag_drop');
    });

    it("should show suggested segment values when a segment value input is focused", async function() {
        await page.click('.segmentEditorPanel .segmentRow0 .ui-autocomplete-input');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await moveMouseAwayFromCapturedArea();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('suggested_values');
    });

    it("should add an OR condition when clicking on add OR", async function() {
        await page.$eval('.segmentEditorPanel .segmentRow0 .ui-autocomplete-input', e => e.blur());
        await page.click('.segmentEditorPanel .segment-add-or');
        await page.waitForFunction(() => !! $('.segmentRow0 .segment-rows>div:eq(1)').length);
        await page.waitForNetworkIdle();
        await moveMouseAwayFromCapturedArea();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('add_new_or_condition');
    });

    it("should add an OR condition when a segment dimension is selected in the OR placeholder section", async function() {
        await selectDimension('.segmentRow0 .segment-row:last', 'Behaviour', 'Clicked Outlink');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('drag_or_condition');
    });

    it("should add an AND condition when clicking on add AND", async function() {
        await page.click('.segmentEditorPanel .segment-add-row');
        await moveMouseAwayFromCapturedArea();
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

        await page.type('input.edit_segment_name', 'new șégmênt');
        await page.click('.segmentRow0 .segment-or'); // click somewhere else to save new name

        await page.waitForTimeout(200);

        // open and close test feature to ensure it doesn't break saving the new segment
        await page.click('.testSegment');
        await page.waitForSelector('#Piwik_Popover');
        await page.waitForNetworkIdle();
        await page.click('.ui-dialog-titlebar-close');

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

    it("should find diacritic segment names with ASCII query", async function() {
        await expectSearchToShowOnly('segment', 'șégmênt');
    });

    it("should match ASCII segment names case-insensitively", async function() {
        await expectSearchToShowOnly('SEGMENT', 'șégmênt');
    });

    it("should correctly load the new segment's details when the new segment is edited", async function() {
        await page.click('.segmentList li[data-idsegment="4"] .editSegment');
        await page.waitForNetworkIdle();
        await moveMouseAwayFromCapturedArea();
        expect(await page.screenshotSelector(selectorsToCapture)).to.matchImage('saved_details');
    });

    it("should show a confirmation modal when changing segment definition", async function() {
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
        await page.waitForFunction(() => $('.modal.open .modal-footer a:contains(Yes):visible').length > 0);
    });

    it("should update the segment URL when saving is confirmed", async function() {
        var elem = await page.jQuery('.modal.open .modal-footer a:contains(Yes):visible');
        await elem.click();
        await page.waitForSelector('.modal.open', { hidden: true });
        await page.waitForFunction(() => {
            const hash = (window.location.hash || '').replace(/^#\?/, '');
            const params = new URLSearchParams(hash);
            let segment = params.get('segment') || '';

            for (let i = 0; i < 3; i += 1) {
                try {
                    const decoded = decodeURIComponent(segment);
                    if (decoded === segment) {
                        break;
                    }
                    segment = decoded;
                } catch (e) {
                    break;
                }
            }

            return segment.indexOf('new value 0') !== -1
                && segment.indexOf('new value 1') !== -1
                && segment.indexOf('new value 2') !== -1;
        });
    });

    it("should keep the updated segment name after page reload", async function() {
        await page.reload();
        await page.waitForSelector('.segmentationContainer .title');
        await page.waitForFunction(() => {
            return $('.segmentationContainer .segmentationTitle').text().indexOf('edited segment') !== -1;
        });
        await page.click('.segmentationContainer .title');
        await page.waitForSelector('.segmentList li[data-idsegment="4"] .editSegment');
    });

    it("should load the updated segment values in editor", async function() {
        await page.waitForSelector('.segmentList li[data-idsegment="4"] .editSegment');
        await page.click('.segmentList li[data-idsegment="4"] .editSegment');
        await page.waitForNetworkIdle();

        await page.waitForFunction(() => {
            const values = $('.segmentEditorPanel .metricValueBlock input').map(function () {
                return ($(this).val() || '').toString();
            }).get();
            const segmentName = (
                $('input.edit_segment_name').val()
                || $('.segmentEditorPanel .segment-content > h3 .segmentName').text()
                || ''
            ).toString();

            return segmentName === 'edited segment'
                && values.indexOf('new value 0') !== -1
                && values.indexOf('new value 1') !== -1
                && values.indexOf('new value 2') !== -1;
        });
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
        await page.click('.add_new_segment');
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
        await selectFieldValue('.segmentRow1 .segment-row:visible:first .metricMatchBlock', 'Is not');

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

    it("should match Cyrillic and Chinese segment names without transliteration", async function() {
        await testEnvironment.callApi('SegmentEditor.add', {
            name: 'unicode журнал 中文',
            definition: 'browserCode==ff',
            idSite: 1,
            autoArchive: 1,
            enableAllUsers: 1,
        });

        await page.goto(url);
        await page.click('.segmentationContainer .title');

        await expectSearchToShowOnly('ЖУРНАЛ', 'журнал');
        await expectSearchToShowOnly('中文', '中文');
        await expectSearchToHaveNoResults('zhongwen');
    });

    it("should initialize only the first segment selector control during bootstrap", async function() {
        await page.goto(url);

        const initState = await page.evaluate(() => {
            const SegmentSelectorControl = window.require('piwik/UI').SegmentSelectorControl;
            const $firstPanel = $('.segmentEditorPanel').first();
            const $secondPanel = $firstPanel.clone(false, false);

            $secondPanel.removeAttr('data-inited');
            $secondPanel.removeData('uiControlObject');
            $secondPanel.find('[data-inited]').removeAttr('data-inited');
            $secondPanel.find('[data-ui-control-object]').removeAttr('data-ui-control-object');
            $firstPanel.after($secondPanel);

            SegmentSelectorControl.initElements();

            return {
                panelCount: $('.segmentEditorPanel').length,
                firstPanelHasUiControl: !!$firstPanel.data('uiControlObject'),
                firstPanelDataInited: $firstPanel.attr('data-inited') || '',
                secondPanelDataInited: $secondPanel.attr('data-inited') || '',
            };
        });

        expect(initState.panelCount).to.equal(2);
        expect(initState.firstPanelHasUiControl).to.equal(true);
        expect(initState.firstPanelDataInited).to.equal('1');
        expect(initState.secondPanelDataInited).to.equal('');
    });

    it("should throw when a second Segmentation instance is created", async function() {
        await page.goto(url);

        const result = await page.evaluate(() => {
            const $firstPanel = $('.segmentEditorPanel').first();
            const $secondPanel = $firstPanel.clone(false, false);

            $secondPanel.removeAttr('data-inited');
            $secondPanel.removeData('uiControlObject');
            $secondPanel.find('[data-inited]').removeAttr('data-inited');
            $secondPanel.find('[data-ui-control-object]').removeAttr('data-ui-control-object');
            $firstPanel.after($secondPanel);

            try {
                new window.Segmentation({
                    target: $secondPanel.find('.segmentListContainer'),
                    editorTemplate: $('.SegmentEditor', $secondPanel),
                    translations: {},
                });

                return { didThrow: false, message: '' };
            } catch (error) {
                return { didThrow: true, message: error.message };
            }
        });

        expect(result.didThrow).to.equal(true);
        expect(result.message).to.contain('Segmentation is initialized more than once on this page.');
    });
});
