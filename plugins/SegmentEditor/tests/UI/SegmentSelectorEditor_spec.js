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

    function selectFieldValue(page, fieldName, textToSelect)
    {
        page.execCallback(function () {
            page.webpage.evaluate(function(fieldName) {
                $(fieldName + ' input.select-dropdown').click();
            }, fieldName);
        });
        page.execCallback(function () {
            page.webpage.evaluate(function(fieldName, textToSelect) {
                $(fieldName + ' .dropdown-content.active li:contains("' + textToSelect + '"):first').click();
            }, fieldName, textToSelect);
        });
    }

    function selectDimension(page, prefixSelector, category, name)
    {
        page.click(prefixSelector + ' .metricListBlock .select-wrapper');
        page.click(prefixSelector + ' .metricListBlock .expandableList h4:contains(' + category + ')');
        page.click(prefixSelector + ' .metricListBlock .expandableList .secondLevel li:contains(' + name + ')');
    }

    it("should load correctly", async function() {
        expect.screenshot("0_initial").to.be.captureSelector(selectorsToCapture, function (page) {
            page.load(url);
        }, done);
    });

    it("should open selector when control clicked", async function() {
        expect.screenshot("1_selector_open").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should open segment editor when edit link clicked for existing segment", async function() {
        expect.screenshot("2_segment_editor_update").to.be.captureSelector(selectorsToCapture, function (page) {
            page.evaluate(function() {
                $('.segmentList .editSegment:first').click()
            }, 200);
        }, done);
    });

    it("should start editing segment name when segment name edit link clicked", async function() {
        expect.screenshot("3_segment_editor_edit_name").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .editSegmentName');
        }, done);
    });

    it("should show the egment editor's available segments dropdown", async function() {
        expect.screenshot("6_segment_editor_droplist").to.be.captureSelector(selectorsToCapture, function (page) {
            page.mouseMove('.available_segments a.dropList');
            page.click('.available_segments a.dropList');
        }, done);
    });

    it("should change segment when another available segment clicked in segment editor's available segments dropdown", async function() {
        expect.screenshot("6_segment_editor_different").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.ui-menu-item a:contains(Add new segment)');
        }, done);
    });

    it("should close the segment editor when the close link is clicked", async function() {
        expect.screenshot("7_segment_editor_closed").to.be.captureSelector(selectorsToCapture, function (page) {
            page.evaluate(function () {
                $('.segmentEditorPanel .segment-footer .close').click();
            });
        }, done);
    });

    it("should open blank segment editor when create new segment link is clicked", async function() {
        expect.screenshot("8_segment_editor_create").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentationContainer .title');
            page.click('.add_new_segment');
        }, done);
    });

    it("should update segment expression when selecting different segment", async function() {
        expect.screenshot("dimension_drag_drop").to.be.captureSelector(selectorsToCapture, function (page) {
            selectDimension(page, '.segmentRow0', 'Actions', 'Action URL');
        }, done);
    });

    // phantomjs won't take screenshots of dropdown windows, so skip this test
    it.skip("should show suggested segment values when a segment value input is focused", async function() {
        expect.screenshot("suggested_values").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .ui-autocomplete-input');
        }, done);
    });

    it("should add an OR condition when clicking on add OR", async function() {
        expect.screenshot("add_new_or_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .segment-add-or');
        }, done);
    });

    it("should add an OR condition when a segment dimension is selected in the OR placeholder section", async function() {
        expect.screenshot("drag_or_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            selectDimension(page, '.segmentRow0 .segment-row:last', 'Actions', 'Clicked URL');
        }, done);
    });

    it("should add an AND condition when clicking on add AND", async function() {
        expect.screenshot("add_new_and_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .segment-add-row');
        }, done);
    });

    it("should add an AND condition when a segment dimension is dragged to the AND placeholder section", async function() {
        expect.screenshot("drag_and_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            selectDimension(page, '.segmentRow1', 'Actions', 'Clicked URL');
        }, done);
    });

    it("should save a new segment and add it to the segment list when the form is filled out and the save button is clicked", async function() {
        expect.screenshot("saved").to.be.captureSelector(selectorsToCapture, function (page) {
            page.evaluate(function () {
                $('.metricValueBlock input').each(function (index) {
                    $(this).val('value ' + index).change();
                });
            });

            page.sendKeys('input.edit_segment_name', 'new segment');
            page.click('.segmentRow0 .segment-or'); // click somewhere else to save new name

            page.evaluate(function () {
                $('button.saveAndApply').click();
            });

            page.click('.segmentationContainer');
        }, done);
    });

    it("should show the new segment after page reload", async function() {
        expect.screenshot("saved").to.be.captureSelector("saved_reload", selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should correctly load the new segment's details when the new segment is edited", async function() {
        expect.screenshot("saved_details").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentList li[data-idsegment=4] .editSegment');
        }, done);
    });

    it("should correctly should show a confirmation when changing segment definition", async function() {
        expect.screenshot("update_confirmation").to.be.captureSelector('.modal.open', function (page) {
            page.click('.segmentEditorPanel .editSegmentName');
            page.evaluate(function () {
                $('input.edit_segment_name').val('').change();
            });
            page.sendKeys('input.edit_segment_name', 'edited segment');
            page.click('.segmentRow0 .segment-or:first'); // click somewhere else to save new name

            selectFieldValue(page, '.segmentRow0 .segment-row:first .metricMatchBlock', 'Is not');
            selectFieldValue(page, '.segmentRow0 .segment-row:last .metricMatchBlock', 'Is not');
            selectFieldValue(page, '.segmentRow1 .segment-row .metricMatchBlock', 'Is not');

            page.evaluate(function () {
                $('.metricValueBlock input').each(function (index) {
                    $(this).val('new value ' + index).change();
                });
            });

            page.evaluate(function () {
                $('button.saveAndApply').click();
            });
        }, done);
    });

    it("should correctly update the segment when saving confirmed", async function() {
        expect.screenshot("updated").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.modal.open .modal-footer a:contains(Yes):visible');
            page.click('.segmentationContainer');
        }, done);
    });

    it("should show the updated segment after page reload", async function() {
        expect.screenshot("updated_reload").to.be.captureSelector("updated_reload", selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should correctly load the updated segment's details when the updated segment is edited", async function() {
        expect.screenshot("updated_details").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentList li[data-idsegment=4] .editSegment');
        }, done);
    });

    it("should correctly show delete dialog when the delete link is clicked", async function() {
        expect.screenshot('deleted_dialog').to.be.captureSelector('.modal.open', function (page) {
            page.evaluate(function () {
                $('.segmentEditorPanel a.delete').click();
            });
        }, done);
    });

    it("should correctly remove the segment when the delete dialog is confirmed", async function() {
        expect.screenshot('deleted').to.be.captureSelector(selectorsToCapture + ',.modal.open', function (page) {
            page.click('.modal.open .modal-footer a:contains(Yes):visible');

            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should not show the deleted segment after page reload", async function() {
        expect.screenshot('deleted').to.be.captureSelector('deleted_reload', selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });
});
