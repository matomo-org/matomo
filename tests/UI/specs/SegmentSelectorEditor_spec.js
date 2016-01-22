/*!
 * Piwik - free/libre analytics platform
 *
 * ViewDataTable screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentSelectorEditorTest", function () {
    var selectorsToCapture = ".segmentEditorPanel,.segmentEditorPanel .dropdown-body,.segment-element";
    
    this.timeout(0);

    var url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09";

    it("should load correctly", function (done) {
        expect.screenshot("0_initial").to.be.captureSelector(selectorsToCapture, function (page) {
            page.load(url);
        }, done);
    });

    it("should open selector when control clicked", function (done) {
        expect.screenshot("1_selector_open").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should open segment editor when edit link clicked for existing segment", function (done) {
        expect.screenshot("2_segment_editor_update").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentList .editSegment:first');
        }, done);
    });

    it("should start editing segment name when segment name edit link clicked", function (done) {
        expect.screenshot("3_segment_editor_edit_name").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .editSegmentName');
        }, done);
    });

    it("should expand segment dimension category when category name clicked in segment editor", function (done) {
        expect.screenshot("4_segment_editor_expanded_dimensions").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .metric_category:contains(Actions)');
        }, done);
    });

    it("should search segment dimensions when text entered in dimension search input", function (done) {
        expect.screenshot("5_segment_editor_search_dimensions").to.be.captureSelector(selectorsToCapture, function (page) {
            page.sendKeys('.segmentEditorPanel .segmentSearch', 'page title');
        }, done);
    });

    it("should change segment when another available segment clicked in segment editor's available segments dropdown", function (done) {
        expect.screenshot("6_segment_editor_different").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.available_segments a.dropList');
            page.click('li.ui-menu-item a:contains(Add new segment)');
        }, done);
    });

    it("should close the segment editor when the close link is clicked", function (done) {
        expect.screenshot("7_segment_editor_closed").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .segment-footer .close');
        }, done);
    });

    it("should open blank segment editor when create new segment link is clicked", function (done) {
        expect.screenshot("8_segment_editor_create").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentationContainer .title');
            page.click('.add_new_segment');
        }, done);
    });

    it("should add new segment expression when segment dimension drag dropped", function (done) {
        expect.screenshot("dimension_drag_drop").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .metric_category:contains(Actions)');
            page.dragDrop('.segmentEditorPanel li[data-metric=actionUrl]', '.segmentEditorPanel .ui-droppable');
        }, done);
    });

    // phantomjs won't take screenshots of dropdown windows, so skip this test
    it.skip("should show suggested segment values when a segment value input is focused", function (done) {
        expect.screenshot("suggested_values").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .ui-autocomplete-input');
        }, done);
    });

    it("should add an OR condition when a segment dimension is dragged to the OR placeholder section", function (done) {
        expect.screenshot("drag_or_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            page.dragDrop('.segmentEditorPanel li[data-metric=outlinkUrl]', '.segmentEditorPanel .segment-add-or .ui-droppable');
        }, done);
    });

    it("should add an AND condition when a segment dimension is dragged to the AND placeholder section", function (done) {
        expect.screenshot("drag_and_condition").to.be.captureSelector(selectorsToCapture, function (page) {
            page.dragDrop('.segmentEditorPanel li[data-metric=outlinkUrl]', '.segmentEditorPanel .segment-add-row .ui-droppable');
        }, done);
    });

    it("should save a new segment and add it to the segment list when the form is filled out and the save button is clicked", function (done) {
        expect.screenshot("saved").to.be.captureSelector(selectorsToCapture, function (page) {
            page.evaluate(function () {
                $('.metricMatchBlock>select').each(function () {
                    $(this).val('==');
                });

                $('.metricValueBlock>input').each(function (index) {
                    $(this).val('value ' + index);
                });
            });

            page.sendKeys('input.edit_segment_name', 'new segment');
            page.click('.segmentEditorPanel .metric_category:contains(Actions)'); // click somewhere else to save new name

            page.click('button.saveAndApply');

            page.click('.segmentationContainer');
        }, done);
    });

    it("should show the new segment after page reload", function (done) {
        expect.screenshot("saved").to.be.captureSelector("saved_reload", selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should correctly load the new segment's details when the new segment is edited", function (done) {
        expect.screenshot("saved_details").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentList li[data-idsegment=4] .editSegment');
        }, done);
    });

    it("should correctly update the segment when its details are changed and saved", function (done) {
        expect.screenshot("updated").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentEditorPanel .editSegmentName');
            page.evaluate(function () {
                $('input.edit_segment_name').val('');
            });
            page.sendKeys('input.edit_segment_name', 'edited segment');
            page.click('.segmentEditorPanel .metric_category:contains(Actions)'); // click somewhere else to save new name

            page.evaluate(function () {
                $('.metricMatchBlock>select').each(function () {
                    $(this).val('!=');
                });

                $('.metricValueBlock>input').each(function (index) {
                    $(this).val('new value ' + index);
                });
            });

            page.click('button.saveAndApply');

            page.click('.segmentationContainer');
        }, done);
    });

    it("should show the updated segment after page reload", function (done) {
        expect.screenshot("updated").to.be.captureSelector("updated_reload", selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should correctly load the updated segment's details when the updated segment is edited", function (done) {
        expect.screenshot("updated_details").to.be.captureSelector(selectorsToCapture, function (page) {
            page.click('.segmentList li[data-idsegment=4] .editSegment');
        }, done);
    });

    it("should correctly show delete dialog when the delete link is clicked", function (done) {
        expect.screenshot('deleted_dialog').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('.segmentEditorPanel a.delete');
        }, done);
    });

    it("should correctly remove the segment when the delete dialog is confirmed", function (done) {
        expect.screenshot('deleted').to.be.captureSelector(selectorsToCapture + ',.ui-dialog', function (page) {
            page.click('.ui-dialog button>span:contains(Yes):visible');

            page.click('.segmentationContainer .title');
        }, done);
    });

    it("should not show the deleted segment after page reload", function (done) {
        expect.screenshot('deleted').to.be.captureSelector('deleted_reload', selectorsToCapture, function (page) {
            page.reload();
            page.click('.segmentationContainer .title');
        }, done);
    });
});